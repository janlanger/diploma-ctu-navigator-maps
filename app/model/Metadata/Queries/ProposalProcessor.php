<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 19.3.13
 * Time: 22:10
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\User\User;
use Maps\Tools\Mixed;

class ProposalProcessor {

    /** @var Revision */
    private $actualRevision = null;

    private $dbNodes = [];
    private $dbPaths = [];

    private $nodesAdd = [];
    private $nodesChange = [];
    private $nodesDelete = [];

    private $pathAdd = [];
    private $pathChange = [];
    private $pathDelete = [];

    private $nodePropertiesRepository;
    /** @var \Maps\Model\Dao */
    private $pathPropertiesRpository;
    /** @var Dao */
    private $changesetRepository;
    /** @var Dao */
    private $nodeChangeRepository;

    private $user;


    function __construct($actualRevision, User $user,
                         Dao $nodeProperties, Dao $pathProperties,
                         Dao $changeset, Dao $nodeChange, Dao $pathChange)
    {
        $this->actualRevision = $actualRevision;
        $this->nodePropertiesRepository = $nodeProperties;
        $this->pathPropertiesRpository = $pathProperties;
        $this->changesetRepository = $changeset;
        $this->nodeChangeRepository = $nodeChange;
        $this->pathChangeRepository = $pathChange;
        $this->user = $user;
    }


    public function handle(Form $form) {
        if(!$form->isValid()) {
            return;
        }



        $values = $form->getValues();
        $definition = json_decode($values['definition']);



        $sentPaths = $definition->paths;
        $sentNodes = $definition->nodes;

        if($this->actualRevision != null) {
            $this->dbNodes = Mixed::mapAssoc($this->actualRevision->getNodes(), 'id');
            $this->dbPaths = Mixed::mapAssoc($this->actualRevision->getPaths(), 'id');
        }

        /** @var $changeset Changeset */
        $changeset = $this->changesetRepository->createNew(null, [
            'state' => Changeset::STATE_NEW,
            'againstRevision' => $this->actualRevision,
            'comment' => $values['comment'],
            'submittedBy' => $this->user,
        ]);

        $this->processNodes($sentNodes);

        $nodesDB = [];

        foreach($this->nodesAdd as $i=>$node) {
            $nodesDB[] = $this->nodeChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'properties' => $node,
            ]);
        }

        foreach($this->nodesChange as $id=>$node) {
            $nodesDB[] = $this->nodeChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'properties' => $node,
                'original' => $this->dbNodes[$id]
            ]);
        }

        foreach($this->nodesDelete as $item) {
            $nodesDB[] = $this->nodeChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'wasDeleted' => true,
                'original' => $item,
            ]);
        }

        $this->processPaths($sentPaths, $sentNodes);

        $pathsDB = [];

        foreach($this->pathAdd as $item) {
            $pathsDB[] = $this->pathChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'properties' => $item,
            ]);
        }

        foreach($this->pathChange as $id=>$item) {
            $pathsDB[] = $this->pathChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'properties' => $item,
                'original' => $this->dbPaths[$id],
            ]);
        }

        foreach($this->pathDelete as $item) {
            $pathsDB[] = $this->pathChangeRepository->createNew(null, [
                'changeset' => $changeset,
                'wasDeleted' => true,
                'original' => $item,
            ]);
        }

        $this->nodeChangeRepository->add($nodesDB);
        $this->pathChangeRepository->add($pathsDB);
        $this->nodeChangeRepository->getEntityManager()->flush();
    }

    private function processNodes($nodes) {
        $unprocessedNodes = array_flip(array_keys($this->dbNodes));
        foreach($nodes as $node) {
            if(!isset($node->id) || !isset($this->dbNodes[$node->id])) {
                //add
                if($node == null) continue;
                $this->nodesAdd[] = $this->nodePropertiesRepository->createNew(null, array(
                    'gpsCoordinates' => $node->position,
                    'type' => $node->type,
                    'name' => (isset($node->name) ? $node->name : null),
                    'room' => (isset($node->room) ? $node->room : null),
                    'fromFloor' => isset($node->fromFloor) ? $node->fromFloor : null,
                    'toFloor' => isset($node->toFloor) ? $node->toFloor : null,
                    'toBuilding' => isset($node->toBuilding) ? $node->toBuilding : null,
                ));
                $node->addedId = count($this->nodesAdd) -1;
            } else {
                //change
                $entityChanged = false;
                $entity = $this->dbNodes[$node->id]->properties;

                foreach($node as $key=>$value) {
                    if(in_array($key, ['id','state','propertyId'])) continue;
                    $getMethod = "get".ucfirst($key);
                    if($value == "") $value = null;
                    if($value != $entity->$getMethod()) {
                        $entityChanged = true;
                    }
                }

                if($entityChanged) {
                    $this->nodesChange[$node->id] = $this->nodePropertiesRepository->createNew(null, [
                        'gpsCoordinates' => $node->position,
                        'type' => $node->type,
                        'name' => (isset($node->name) ? $node->name : null),
                        'room' => (isset($node->room) ? $node->room : null),
                        'fromFloor' => isset($node->fromFloor) ? $node->fromFloor : null,
                        'toFloor' => isset($node->toFloor) ? $node->toFloor : null,
                        'toBuilding' => isset($node->toBuilding) ? $node->toBuilding : null,
                    ]);
                }
                unset($unprocessedNodes[$node->id]);
            }
        }

        foreach($unprocessedNodes as $id =>$foo) {
            $this->nodesDelete[] = $this->dbNodes[$id];
        }
    }

    private function processPaths($paths, $nodes) {
        $unprocessedPaths = array_flip(array_keys($this->dbPaths));
        foreach($paths as $path) {
            $updated = false;
            if(isset($nodes[$path->startNode]->id) && isset($nodes[$path->endNode]->id)) {
                //oba maji id - jsou z db - zkus najit zaklade id bodu
                $entity = $this->findPathWithNodes($nodes[$path->startNode]->propertyId, $nodes[$path->endNode]->propertyId);
                if($entity != null) {
                    if($entity instanceof Path) {
                        unset($unprocessedPaths[$entity->id]);
                        $entity = $entity->getProperties();
                    }
                    if(round($entity->length,5) != round($path->length,5)) {
                        $this->pathChange[$entity->id] = $this->pathPropertiesRpository->createNew(null, [
                            'startNode' => $entity->startNode,
                            'endNode' => $entity->endNode,
                            'length' => $path->length
                        ]);
                    }
                    $updated = true;

                }
            }
            if(!$updated) {
                $begin = $this->getNodeWithPathId($path->startNode, $nodes);
                $end = $this->getNodeWithPathId($path->endNode, $nodes);
                if($begin == null || $end == null) {
                    continue; // not ended with markers on both sides
                }
                $this->pathAdd[] = $this->pathPropertiesRpository->createNew(null, [
                    "startNode" => $begin,
                    "endNode" => $end,
                    "length" => $path->length,
                ]);
            }
        }
        foreach($unprocessedPaths as $id => $foo) {
            $this->pathDelete[] = $this->dbPaths[$id];
        }
    }

    private function findPathWithNodes($f, $s) {
        foreach($this->dbPaths as $id => $path) {
            if(($f == $path->properties->startNode->id && $s == $path->properties->endNode->id) ||
                ($s == $path->properties->startNode->id && $f == $path->properties->endNode->id)) {
                return $path;
            }
        }

        foreach($this->pathAdd as $path) {
            if(($f == $path->startNode->id && $s == $path->endNode->id) ||
                ($s == $path->startNode->id && $f == $path->endNode->id)) {
                return $path;
            }
        }
        return null;
    }

    private function getNodeWithPathId($nodeId, $nodes)
    {
        return (isset($nodes[$nodeId]->addedId)? $this->nodesAdd[$nodes[$nodeId]->addedId]:$this->dbNodes[$nodes[$nodeId]->id]->properties);
    }

}