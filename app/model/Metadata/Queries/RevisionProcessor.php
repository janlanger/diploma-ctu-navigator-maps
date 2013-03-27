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

    private $user;
    /** @var Revision */
    private $actualRevision = null;

    private $directChangeset = null;

    function __construct($actualRevision, User $user,
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
        $this->user = $user;
    }


    public function handle(Form $form) {
        Debugger::$maxDepth = 4;
        $values = $form->getValues();
        $changes = json_decode($values['custom_changes'], true);

        $this->processNewChanges($changes);

        $changesets = $this->changesetRepository->fetchAssoc(new ActiveProposals(null, $this->actualRevision),'id');

        dump($changesets);

        exit;
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
            $dbNodes = $this->nodeRepository->fetchAssoc(new NodesByPropertiesId($nodePropertiesIds, $this->actualRevision), 'id');
            $dbPathProperties = $this->pathPropertiesRepository->fetchAssoc(new PathPropertiesByNodes($pathNodeIds), 'id');
            $dbPath = $this->pathRepository->fetchAssoc(new PathsByPropertiesId(array_keys($dbPathProperties), $this->actualRevision), 'id');

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
                    'original' => $this->getNodeByPropertiesId($node['propertyId'], $dbNodes),
                    'wasDeleted' => false
                ]);
            }


            foreach ($changes['nodes']['deleted'] as $nodeId) {
                $nodes[] = $this->nodeChangeRepository->createNew(null, [
                    'changeset' => $changeset,
                    'properties' => null,
                    'original' => $this->getNodeByPropertiesId($nodeId, $dbNodes),
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
                foreach($dbPath as $p) {
                    if($p->properties->startNode->id == $path['start'] && $p->properties->endNode->id == $path['end']) {
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

    private function  getNodeByPropertiesId($id, $nodes) {
        foreach($nodes as $node) {
            if($node->properties->id == $id) {
                return $node;
            }
        }
    }

    private function getPathByNodesIds($x1, $x2, $paths) {
        foreach($paths as $path) {

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

}