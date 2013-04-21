<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.3.13
 * Time: 17:43
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Maps\Model\Dao;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\FloorConnection;
use Maps\Model\Metadata\Path;
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
    private $actualRevision = NULL;
    /** @var Changeset */
    private $directChangeset = NULL;


    private $floorConnectionRepository;

    private $changedKeys = [];

    private $connections = [];

    private $nodeChangedIds = [];

    function __construct(Revision $activeRevision, User $user, Dao $revision,
                         Dao $nodeProperties, Dao $pathProperties,
                         Dao $changeset, Dao $nodeChange, Dao $pathChange,
    Dao $node, Dao $path, Dao $floorConnection)
    {
        $this->actualRevision = $activeRevision;
        $this->nodePropertiesRepository = $nodeProperties;
        $this->pathPropertiesRepository = $pathProperties;
        $this->changesetRepository = $changeset;
        $this->nodeChangeRepository = $nodeChange;
        $this->pathChangeRepository = $pathChange;
        $this->nodeRepository = $node;
        $this->pathRepository = $path;
        $this->revisionRepository = $revision;
        $this->floorConnectionRepository = $floorConnection;
        $this->user = $user;
    }


    public function handle(Form $form) {

        $values = $form->getValues();
        $changes = json_decode($values['custom_changes'], TRUE);

        $this->processNewChanges($changes);


        /** @var $changesets Changeset[] */
        $changesets = $this->changesetRepository->fetchAssoc(new ActiveProposals(NULL, $this->actualRevision),'id');

        $changesetsToApply = [];

        if($this->directChangeset != NULL) {
            $this->directChangeset->setState(Changeset::STATE_APPROVED);
            $changesetsToApply[$this->directChangeset->id] = $this->directChangeset;
        }

        foreach($values as $item => $action) {
            if(Strings::startsWith($item, "proposal")) {
                $id = str_replace("proposal", "", $item);
                if($action == "approve") {
                    $changesetsToApply[$id] = $changesets[$id];
                    $changesets[$id]->setState(Changeset::STATE_APPROVED);
                    $changesets[$id]->setProcessedBy($this->user);
                    $changesets[$id]->setProcessedDate(new \DateTime());
                }
                if($action == "reject") {
                    $changesets[$id]->setState(Changeset::STATE_REJECTED);
                    $changesets[$id]->setProcessedBy($this->user);
                    $changesets[$id]->setProcessedDate(new \DateTime());
                }
                if(isset($values['proposaltext'.$id]) && $values['proposaltext'.$id] != "") {
                    $changesets[$id]->setAdminComment($values['proposaltext'.$id]);
                }

            }
        }

        if(!empty($changesetsToApply) || (!empty($this->connections) && (!empty($this->connections['add']) || !empty($this->connections['change']) || !empty($this->connections['delete'])))) {

            $newRevision = $this->cloneRevision($this->actualRevision);

            $this->applyChanges($newRevision, $changesetsToApply);

            $this->cloneAndUpdateFloorConnections($newRevision);

            $this->autoCloseChangesets($changesets, $this->changedKeys);
            $this->countPathsLength($newRevision);

            $this->nodeRepository->add($newRevision->nodes);
            $this->pathRepository->add($newRevision->paths);
            $this->revisionRepository->save($newRevision);
        } else {
            //saves rejections
            $this->changesetRepository->save();
        }
        return TRUE;
    }

    private function processNewChanges($changes) {

        //remove null shitty things...

        if(!is_array($changes)) return;

        foreach ($changes as $part => $p) {
            foreach ($p as $key => $section) {
                foreach ($section as $id => $node) {
                    if ($node == NULL) {
                        unset($changes[$part][$key][$id]);
                    }
                }
            }
        }


        if($this->hasChanges($changes)) {
            /** @var $changeset Changeset */
            $this->directChangeset = $changeset = $this->changesetRepository->createNew(NULL, [
                'state' => Changeset::STATE_NEW,
                'againstRevision' => $this->actualRevision,
                'comment' => "Automaticky vytvořený návrh při tvorbě revize.",
                'submittedBy' => $this->user,
                'processedBy' => $this->user,
                'processedDate' => new \DateTime(),
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

            if(count($nodePropertiesIds) > 0) {
                $dbNodeProperties = $this->nodePropertiesRepository->fetchAssoc(new NodePropertiesQuery($nodePropertiesIds), 'id');
            } else {
                $dbNodeProperties = [];
            }

            $dbPathProperties = $this->pathPropertiesRepository->fetchAssoc(new PathPropertiesByNodes($pathNodeIds), 'id');

            //load end



            $nodesAdd = [];

            $nodes = [];

            $floorIntersections = [];


            $paths = [];

            foreach($changes['nodes']['added'] as $id => $node) {
                $nodesAdd[$id] = $this->nodePropertiesRepository->createNew(NULL, [
                    'gpsCoordinates' => $node['position'],
                    'type' => $node['type'],
                    'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : NULL),
                    'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : NULL),
                ]);

                $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                    'changeset' => $changeset,
                    'properties' => $nodesAdd[$id],
                    'original' => NULL,
                    'wasDeleted' => FALSE
                ]);
            }


            foreach ($changes['nodes']['changed'] as $node) {
                $item = $this->nodePropertiesRepository->createNew(NULL, [
                    'gpsCoordinates' => $node['position'],
                    'type' => $node['type'],
                    'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : NULL),
                    'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : NULL),
                ]);

                $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                    'changeset' => $changeset,
                    'properties' => $item,
                    'original' => $dbNodeProperties[$node['propertyId']],
                    'wasDeleted' => FALSE
                ]);
            }


            foreach ($changes['nodes']['deleted'] as $nodeId) {
                $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                    'changeset' => $changeset,
                    'properties' => NULL,
                    'original' => $dbNodeProperties[$nodeId],
                    'wasDeleted' => TRUE
                ]);
            }


            foreach($changes['paths']['added'] as $path) {
                $item = $this->pathPropertiesRepository->createNew(NULL, [
                    "startNode" => (isset($path['start']['propertyId'])?$dbNodeProperties[$path['start']['propertyId']] : $nodesAdd[$path['start']['id']]),
                    "endNode" => (isset($path['end']['propertyId']) ? $dbNodeProperties[$path['end']['propertyId']] : $nodesAdd[$path['end']['id']]),
                ]);

                $paths[] = $this->pathChangeRepository->createNew(NULL, [
                    'changeset' => $changeset,
                    'properties' => $item,
                    'original' => NULL,
                    'wasDeleted' => FALSE,
                ]);
            }

            $deletedPaths = [];

            foreach($changes['paths']['deleted'] as $path) {
                foreach($dbPathProperties as $p) {
                    if($p->startNode->id == $path['start'] && $p->endNode->id == $path['end']) {
                        break;
                    }
                }

                $paths[] = $this->pathChangeRepository->createNew(NULL, [
                    'changeset' => $changeset,
                    'properties' => NULL,
                    'original' => $p,
                    'wasDeleted' => TRUE,
                ]);
            }

            $this->nodeChangeRepository->add($nodes);
            $this->pathChangeRepository->add($paths);

            $this->nodeChangeRepository->getEntityManager()->flush();

        }

        if(!empty($changes['exchange']) && (!empty($changes['exchange']['added']) || !empty($changes['exchange']['changed']) || !empty($changes['exchange']['deleted']))) {
           // dump($changes['exchange']);
            $connectionsAdded = [];
            $connectionsChanged = [];

            $selectIds = [];
            foreach([$changes['exchange']['added'], $changes['exchange']['changed'], $changes['exchange']['deleted']] as $s) {
                foreach($s as $id=>$x) {
                    if(isset($x['destinationNode'])) {
                        $selectIds[] = $x['destinationNode'];
                    }
                    if(!isset($nodesAdd[$id])) {
                        $selectIds[] = $id;
                    }
                }
            }
            //$selectIds = array_merge($selectIds, array_keys($changes['exchange']['changed']));

            $nodes = Mixed::mapAssoc($this->nodePropertiesRepository->findBy(["id"=>$selectIds]), 'id');

            foreach($changes['exchange']['added'] as $id => $added) {
                if(isset($nodesAdd[$id])) {
                    $connectionsAdded[] = [$nodesAdd[$id], $nodes[$added['destinationNode']]];
                } else {
                    $connectionsAdded[] = [$nodes[$id], $nodes[$added['destinationNode']]];
                }

            }

            foreach ($changes['exchange']['changed'] as $id => $changed) {
                if (isset($nodes[$id])) {
                    $connectionsChanged[$changed['pathId']] = [$nodes[$id], $nodes[$changed['destinationNode']]];
                }
            }

            $connectionsDelete = $changes['exchange']['deleted'];
            $this->connections = [
                'add' => $connectionsAdded,
                'change' => $connectionsChanged,
                'delete' => $connectionsDelete
            ];
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
        $newRevision = $this->revisionRepository->createNew(NULL, array(
            'floor' => $revision->getFloor(),
            'user' => $this->user,
        ));

        $nodes = $newRevision->getNodes();


        foreach($revision->nodes as $node) {
            $nodes[] = $this->nodeRepository->createNew(NULL, array(
                'revision' => $newRevision,
                'properties' => $node->properties
            ));
        }

        $paths = $newRevision->getPaths();

        foreach($revision->paths as $path) {
            $paths[] = $this->pathRepository->createNew(NULL, array(
                'revision' => $newRevision,
                'properties' => $path->properties
            ));
        }

        //$newRevision->setNodes($nodes);
        //$newRevision->setPaths($paths);

        return $newRevision;
    }

    private function applyChanges(Revision $revision, array $changes) {


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
            $change->setInRevision($revision);
            foreach($change->nodes as $node) {
                if($node->original == NULL) {
                    $revision->nodes[] = $this->nodeRepository->createNew(NULL, ["revision" => $revision, "properties"=>$node->properties]);
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
                $revision->nodes->set($key, $this->nodeRepository->createNew(NULL, ["revision" => $revision, "properties" => $node->properties]));
                $changesMap[$node->original->id] = $key;
            }
            $this->changedKeys['nodes'][$node->original->id] = TRUE;
        }
        foreach ($changes as $change) {
            foreach ($change->paths as $path) {
                if ($path->original == NULL) {
                    $revision->paths[] = $this->pathRepository->createNew(NULL, ["revision" => $revision, "properties" => $path->properties]);
                } else {
                    $revision->paths->remove($this->findKeyByPropertiesId($revision->paths, $path->original));
                    $this->changedKeys['paths'][$path->original->id] = TRUE;
                }
            }
        }
        $this->nodeChangedIds = $changesMap;


        //change paths reference to new properties ids
        foreach ($revision->paths as $key => $path) {
            $c = FALSE;
            foreach([$path->properties->startNode, $path->properties->endNode] as $node) {
                if(isset($changesMap[$node->id])) {
                    $c = TRUE;
                }
            }
            if($c) {
                $path->properties = $this->pathPropertiesRepository->createNew(NULL, array(
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

        return $this->nodeChangeRepository->createNew(NULL, array(
            'properties' => $this->nodePropertiesRepository->createNew(NULL, $merged),
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

    private function autoCloseChangesets($changesets, $keys) {
        /** @var $changeset Changeset */
        foreach($changesets as $changeset) {
            if($changeset->getState() != Changeset::STATE_NEW) {
                //we are dealing with this only if the changeset should stay open after this
                continue;
            }

            $close = FALSE;
            foreach($changeset->nodes as $node) {
                if($node->original != NULL && isset($keys['nodes'][$node->original->id])) {
                    $close = TRUE;
                    break;
                }
            }
            if(!$close) {
                foreach ($changeset->paths as $path) {
                    if ($path->original != NULL && isset($keys['paths'][$path->original->id])) {
                        $close = TRUE;
                        break;
                    }
                }
            }

            if($close) {
                $changeset->setState(Changeset::STATE_REJECTED);
                $changeset->setAdminComment("Návrh byl automaticky uzavřen kvůli kolizi s nově uloženou verzí dat.");
                $changeset->setProcessedBy($this->user);
                $changeset->setProcessedDate(new \DateTime());
            }
        }
    }

    private function countPathsLength(Revision $revision) {
        foreach($revision->getPaths() as $path) {
            /** @var $path Path */
                $path->setLength(($this->computeDistance(
                    $path->getProperties()->getStartNode()->getGpsCoordinates(),
                    $path->getProperties()->getEndNode()->getGpsCoordinates()
                )));

        }
    }

    /**
     * @param $one string GPS
     * @param $two string GPS
     * @return float distance between points in meters
     */
    private function computeDistance($one, $two) {
        $one = explode(",", $one);
        $two = explode(",", $two);

        $lat1 = (float) $one[0];
        $lng1 = (float) $one[1];

        $lat2 = (float) $two[0];
        $lng2 = (float) $two[1];

        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
                sin($dLng / 2) * sin($dLng / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $R * $c;

        return $d*1000;
    }


    private function cloneAndUpdateFloorConnections(Revision $revision) {
        $floorConnections = $this->floorConnectionRepository->findBy(["revision_one"=> $this->actualRevision]);

//clone
        $newFloorConnections = [];
        if(is_array($floorConnections)) {
            /** @var $conn FloorConnection */
            foreach($floorConnections as $conn) {
                $newFloorConnections[$conn->id] = $this->floorConnectionRepository->createNew(NULL, [
                    'revisionOne' => $revision,
                    'nodeOne' => $conn->getNodeOne(),
                    'revisionTwo' => $conn->getRevisionTwo(),
                    'nodeTwo' => $conn->getNodeTwo(),
                    'type' => $conn->getType(),
                ]);
            }
        }



        if(!empty($this->connections)) {
            $otherNodes = [];
            foreach([$this->connections['add'],$this->connections['change']] as $item) {
                foreach($item as $conn) {
                    if(is_array($conn)) {
                        $otherNodes[] = $conn[1]->id;
                    }
                }
            }
            if(!empty($otherNodes)) {
                $otherRevision = $this->revisionRepository->createQueryBuilder("r")
                        ->select("r AS rev, p.id as prop")
                        ->innerJoin("Maps\\Model\\Metadata\\Node", "n", Join::WITH, "n.revision = r")
                        ->innerJoin("n.properties", "p")
                        ->where("r.published = true")
                        ->andWhere("n.properties IN (?1)")
                        ->setParameter(1, $otherNodes)->getQuery()->getResult();
                $otherMap = [];
                foreach($otherRevision as $item) {
                    $otherMap[$item['prop']] = $item['rev'];
                }



                foreach($this->connections['add'] as $added) {
                    if(isset($this->nodeChangedIds[$added[0]->id])) {
                        $added[0] = $revision->nodes->get($this->nodeChangedIds[$added[0]->id])->properties;
                    }
                    $newFloorConnections[] = $this->floorConnectionRepository->createNew(NULL, [
                        'revisionOne' => $revision,
                        'nodeOne' => $added[0],
                        'revisionTwo' => $otherMap[$added[1]->id],
                        'nodeTwo' => $added[1],
                        'type' => $added[0]->getType(),
                    ]);
                }

                foreach($this->connections['change'] as $id => $change) {
                    if (isset($this->nodeChangedIds[$change[0]->id])) {
                        $change[0] = $revision->nodes->get($this->nodeChangedIds[$change[0]->id])->properties;
                    }
                    $newFloorConnections[$id] = $this->floorConnectionRepository->createNew(NULL, [
                        'revisionOne' => $revision,
                        'nodeOne' => $change[0],
                        'revisionTwo' => $otherMap[$change[1]->id],
                        'nodeTwo' => $change[1],
                        'type' => $change[0]->getType(),
                    ]);
                }
            }
            foreach($this->connections['delete'] as $item) {
                unset($newFloorConnections[$item]);
            }
        }

        //find all connections ending here in actual revision

        $floorConnections = $this->floorConnectionRepository->findBy(["revision_two" => $this->actualRevision]);
        //clone and change to new nodes

        /** @var $floorConnection FloorConnection */
        foreach($floorConnections as $floorConnection) {
            $node = $floorConnection->getNodeTwo();
            if(isset($this->nodeChangedIds[$node->id])) {
                $node = $revision->nodes->get(($this->nodeChangedIds[$node->id]))->properties;
            } else if(is_array($this->changedKeys['nodes']) && isset($this->changedKeys['nodes'][$node->id])){
                continue;
            }
            $newFloorConnections[] = $this->floorConnectionRepository->createNew(NULL, [
                'revisionOne' => $floorConnection->getRevisionOne(),
                'nodeOne' => $floorConnection->getNodeOne(),
                'revisionTwo' => $revision,
                'nodeTwo' => $node,
                'type' => $floorConnection->getType(),
            ]);
        }


        $this->floorConnectionRepository->add($newFloorConnections);
    }

}