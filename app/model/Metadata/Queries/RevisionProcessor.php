<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.3.13
 * Time: 17:43
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Dao;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\Revision;
use Maps\Model\User\User;
use Maps\Tools\Mixed;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;
use Nette\Object;
use Nette\Utils\Strings;

class RevisionProcessor extends Object {

    private $nodePropertiesRepository;
    /** @var \Maps\Model\Dao */
    private $pathPropertiesRepository;
    /** @var Dao */
    private $changesetRepository;
    /** @var Dao */
    private $nodeChangeRepository;

    private $pathChangeRepository;
    private $nodeRepository;
    private $pathRepository;
    private $revisionRepository;

    private $user;
    /** @var Revision */
    private $actualRevision = null;
    /** @var Changeset */
    private $directChangeset = null;

    function __construct($actualRevision, User $user, Dao $revision,
                         Dao $nodeProperties, Dao $pathProperties,
                         Dao $changeset, Dao $nodeChange, Dao $pathChange,
    Dao $node, Dao $path)
    {
        $this->actualRevision = $actualRevision;
        $this->nodePropertiesRepository = $nodeProperties;
        $this->pathPropertiesRepository = $pathProperties;
        $this->changesetRepository = $changeset;
        $this->nodeChangeRepository = $nodeChange;
        $this->pathChangeRepository = $pathChange;
        $this->nodeRepository = $node;
        $this->pathRepository = $path;
        $this->revisionRepository = $revision;
        $this->user = $user;
    }


    public function handle(Form $form) {

        $values = $form->getValues();
        $changes = json_decode($values['custom_changes'], true);

        $this->processNewChanges($changes);


        /** @var $changesets Changeset[] */
        $changesets = $this->changesetRepository->fetchAssoc(new ActiveProposals(null, $this->actualRevision),'id');

        $changesetsToApply = [];

        if($this->directChangeset != null) {
            $this->directChangeset->setState(Changeset::STATE_APPROVED);
            $changesetsToApply[$this->directChangeset->id] = $this->directChangeset;
        }

        foreach($values as $item => $action) {
            if(Strings::startsWith($item, "proposal")) {
                $id = str_replace("proposal", "", $item);
                if($action == "approve") {
                    $changesetsToApply[$id] = $changesets[$id];
                    $changesets[$id]->setState(Changeset::STATE_APPROVED);
                }
                if($action == "reject") {
                    $changesets[$id]->setState(Changeset::STATE_REJECTED);
                }
            }
        }

        if(!empty($changesetsToApply)) {

            $newRevision = $this->cloneRevision($this->actualRevision);

            $this->applyChanges($newRevision, $changesetsToApply);

            $this->nodeRepository->add($newRevision->nodes);
            $this->pathRepository->add($newRevision->paths);
            $this->revisionRepository->save($newRevision);
        } else {
            //saves rejections
            $this->changesetRepository->save();
        }
    }

    private function processNewChanges($changes) {

        //remove null shitty things...

        foreach ($changes as $part => $p) {
            foreach ($p as $key => $section) {
                foreach ($section as $id => $node) {
                    if ($node == null) {
                        unset($changes[$part][$key][$id]);
                    }
                }
            }
        }

        if($this->hasChanges($changes)) {
            /** @var $changeset Changeset */
            $this->directChangeset = $changeset = $this->changesetRepository->createNew(null, [
                'state' => Changeset::STATE_NEW,
                'againstRevision' => $this->actualRevision,
                'comment' => "",
                'submittedBy' => $this->user,
            ]);

            //load needed nodes properties for relations

            $nodePropertiesIds = [];

            foreach ($changes['nodes']['changed'] as $node) {
                $nodePropertiesIds[] = $node['propertyId'];
            }
            $nodePropertiesIds = array_merge($nodePropertiesIds, $changes['nodes']['deleted']);


            foreach ($changes['paths']['added'] as $path) {
                if (isset($path['start']['propertyId'])) {
                    $nodePropertiesIds[] = $path['start']['propertyId'];
                }
                if (isset($path['end']['propertyId'])) {
                    $nodePropertiesIds[] = $path['end']['propertyId'];
                }
            }

            $pathNodeIds = [];

            foreach ($changes['paths']['deleted'] as $path) {
                $nodePropertiesIds[] = $path['start'];
                $nodePropertiesIds[] = $path['end'];

                $pathNodeIds[] = [$path['start'], $path['end']];
            }

            $nodePropertiesIds = array_unique($nodePropertiesIds, SORT_NUMERIC);

            $dbNodeProperties = $this->nodePropertiesRepository->fetchAssoc(new NodePropertiesQuery($nodePropertiesIds), 'id');

            $dbPathProperties = $this->pathPropertiesRepository->fetchAssoc(new PathPropertiesByNodes($pathNodeIds), 'id');

            //load end



            $nodesAdd = [];

            $nodes = [];

            foreach($changes['nodes']['added'] as $id => $node) {
                $nodesAdd[$id] = $this->nodePropertiesRepository->createNew(null, [
                    'gpsCoordinates' => $node['position'],
                    'type' => $node['type'],
                    'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : null),
                    'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : null),
                    'fromFloor' => isset($node['fromFloor']) && trim($node['fromFloor']) != "" ? $node['fromFloor'] : null,
                    'toFloor' => isset($node['toFloor']) && trim($node['toFloor']) != "" ? $node['toFloor'] : null,
                    'toBuilding' => isset($node['toBuilding']) && trim($node['toBuilding']) != "" ? $node['toBuilding'] : null,
                ]);

                $nodes[] = $this->nodeChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => $nodesAdd[$id],
                    'original' => null,
                    'wasDeleted' => false
                ]);
            }


            foreach ($changes['nodes']['changed'] as $node) {
                $item = $this->nodePropertiesRepository->createNew(null, [
                    'gpsCoordinates' => $node['position'],
                    'type' => $node['type'],
                    'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : null),
                    'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : null),
                    'fromFloor' => isset($node['fromFloor']) && trim($node['fromFloor']) != "" ? $node['fromFloor'] : null,
                    'toFloor' => isset($node['toFloor']) && trim($node['toFloor']) != "" ? $node['toFloor'] : null,
                    'toBuilding' => isset($node['toBuilding']) && trim($node['toBuilding']) != "" ? $node['toBuilding'] : null,
                ]);

                $nodes[] = $this->nodeChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => $item,
                    'original' => $dbNodeProperties[$node['propertyId']],
                    'wasDeleted' => false
                ]);
            }


            foreach ($changes['nodes']['deleted'] as $nodeId) {
                $nodes[] = $this->nodeChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => null,
                    'original' => $dbNodeProperties[$nodeId],
                    'wasDeleted' => true
                ]);
            }

            $paths = [];

            foreach($changes['paths']['added'] as $path) {
                $item = $this->pathPropertiesRepository->createNew(null, [
                    "startNode" => (isset($path['start']['propertyId'])?$dbNodeProperties[$path['start']['propertyId']] : $nodesAdd[$path['start']['id']]),
                    "endNode" => (isset($path['end']['propertyId']) ? $dbNodeProperties[$path['end']['propertyId']] : $nodesAdd[$path['end']['id']]),
                ]);

                $paths[] = $this->pathChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => $item,
                    'original' => null,
                    'wasDeleted' => false,
                ]);
            }

            $deletedPaths = [];

            foreach($changes['paths']['deleted'] as $path) {
                foreach($dbPathProperties as $p) {
                    if($p->startNode->id == $path['start'] && $p->endNode->id == $path['end']) {
                        break;
                    }
                }

                $paths[] = $this->pathChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => null,
                    'original' => $p,
                    'wasDeleted' => true,
                ]);
            }

            $this->nodeChangeRepository->add($nodes);
            $this->pathChangeRepository->add($paths);

            $this->nodeChangeRepository->getEntityManager()->flush();

        }
    }


    private function hasChanges($changes) {
        $nodes = $changes['nodes'];
        $paths = $changes['paths'];

        return ((isset($nodes['added']) && !empty($nodes['added'])) ||
                (isset($nodes['changed']) && !empty($nodes['changed'])) ||
                (isset($nodes['deleted']) && !empty($nodes['deleted'])) ||
                (isset($paths['added']) && !empty($paths['added'])) ||
                (isset($paths['deleted']) && !empty($paths['deleted']))
        );

    }

    private function cloneRevision(Revision $revision) {
        /** @var $newRevision Revision */
        $newRevision = $this->revisionRepository->createNew(null, array(
            'floor' => $revision->getFloor(),
            'user' => $this->user,
        ));

        $nodes = $newRevision->getNodes();


        foreach($revision->nodes as $node) {
            $nodes[] = $this->nodeRepository->createNew(null, array(
                'revision' => $newRevision,
                'properties' => $node->properties
            ));
        }

        $paths = $newRevision->getPaths();

        foreach($revision->paths as $path) {
            $paths[] = $this->pathRepository->createNew(null, array(
                'revision' => $newRevision,
                'properties' => $path->properties
            ));
        }

        //$newRevision->setNodes($nodes);
        //$newRevision->setPaths($paths);

        return $newRevision;
    }

    private function applyChanges(Revision $revision, array $changes) {

        dump(count($revision->paths));

        usort($changes, function(Changeset $a, Changeset $b) {
            //sorts the array by submitted date (we need to replace changes made by previous proposal)
            if($a->getSubmittedDate() < $b->getSubmittedDate()) {
                return -1;
            }
            if ($a->getSubmittedDate() > $b->getSubmittedDate()) {
                return 1;
            }
            return 0;
        });

        $toChangeNodes = [];

        foreach($changes as $change) {
            foreach($change->nodes as $node) {
                if($node->original == null) {
                    $revision->nodes[] = $this->nodeRepository->createNew(null, ["revision" => $revision, "properties"=>$node->properties]);
                }
                else {
                    if(isset($toChangeNodes[$node->original->id])) {
                        $toChangeNodes[$node->original->id] = $this->mergeNodeChanges($node->original, $toChangeNodes[$node->original->id], $node);
                    } else {
                        $toChangeNodes[$node->original->id] = $node;
                    }
                }
            }
        }

        $changesMap = [];

        foreach($toChangeNodes as $node) {
            $key = $this->findKeyByPropertiesId($revision->nodes, $node->original);
            if($node->wasDeleted) {
                $revision->nodes->remove($key);
            } else {
                $revision->nodes->set($key, $this->nodeRepository->createNew(null, ["revision" => $revision, "properties" => $node->properties]));
                $changesMap[$node->original->id] = $key;
            }
        }
        foreach ($changes as $change) {
            foreach ($change->paths as $path) {
                if ($path->original == null) {
                    $revision->paths[] = $this->pathRepository->createNew(null, ["revision" => $revision, "properties" => $path->properties]);
                } else {
                    $revision->paths->remove($this->findKeyByPropertiesId($revision->paths, $path->original));
                }
            }
        }

        dump($changesMap);

        //change paths reference to new properties ids
        foreach ($revision->paths as $key => $path) {
            $c = false;
            foreach([$path->properties->startNode, $path->properties->endNode] as $node) {
                if(isset($changesMap[$node->id])) {
                    $c = true;
                }
            }
            if($c) {
                $path->properties = $this->pathPropertiesRepository->createNew(null, array(
                    "startNode" => isset($changesMap[$path->properties->startNode->id])?
                            $revision->nodes->get($changesMap[$path->properties->startNode->id])->properties :
                            $path->properties->startNode,
                    "endNode" => isset($changesMap[$path->properties->endNode->id]) ?
                            $revision->nodes->get($changesMap[$path->properties->endNode->id])->properties :
                            $path->properties->endNode,
                ));
            }
        }
    }

    public function mergeNodeChanges($original, $previous, $replacement) {
        if($replacement->wasDeleted) {
            return $replacement;
        }
        if($previous->wasDeleted) {
            return $previous;
        }

        $merged = $original->toArray();
        unset($merged['id']);
        foreach($merged as $key => $value) {
            if($key == 'id') continue;
            $getMethod = 'get'.ucfirst($key);
            if(!method_exists($original, $getMethod)) continue;

            if($value != $replacement->properties->$getMethod()) {
                $merged[$key] = $replacement->properties->$getMethod();
                continue;
            }

            if($value == $replacement->properties->$getMethod() &&
                    $value != $previous->properties->$getMethod()) {
                $merged[$key] = $previous->properties->$getMethod();
            }
        }

        return $this->nodeChangeRepository->createNew(null, array(
            'properties' => $this->nodePropertiesRepository->createNew(null, $merged),
            'original' => $original
        ));
    }

    private function findKeyByPropertiesId($collection, $nodeP) {
        foreach ($collection as $key => $value) {
            if($value->properties->id == $nodeP->id) {
                return $key;
            }
        }
    }

}