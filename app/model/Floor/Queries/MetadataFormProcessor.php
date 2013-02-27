<?php

namespace Maps\Model\Floor;

use Maps\Components\Forms\Form;
use Maps\Model\Dao;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MetadataFormProcessor
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class MetadataFormProcessor {

    private $nodeRepository;
    private $pathRepository;
    private $floor;
    private $dbNodes;
    private $dbPaths;
    private $allNodes = [];
    private $allPaths = [];

    private $sentNodes = [];

    function __construct(Dao $nodeRepository, Dao $pathRepository, $floor) {
        $this->nodeRepository = $nodeRepository;
        $this->pathRepository = $pathRepository;
        $this->floor = $floor;
    }

    public function handle(Form $form) {
        if (!$form->isValid()) {
            return;
        }
        try {
            $values = $form->getValues();

            $definition = json_decode($values['definition']);
            $paths = $definition->paths;

            $this->dbNodes = $this->allNodes = $this->nodeRepository->fetchAssoc(new \Maps\Model\BasicFetchByQuery(["floor_plan" => $this->floor]), 'id');
            $this->dbPaths = $this->allPaths = $this->pathRepository->fetchAssoc(new \Maps\Model\BasicFetchByQuery(["floor" => $this->floor]), 'id');
            if($this->dbNodes == null) {
                $this->allNodes = $this->dbNodes = [];
            }
            if($this->dbPaths == null) {
                $this->allPaths = $this->dbPaths = [];
            }
            

            $addNodes = $this->walkNodes($definition);
            $addPath = $this->walkPaths($definition);

            $deleteNodes = $this->findToDelete($definition);
            

            if(!empty($addNodes)) {
                $this->nodeRepository->add($addNodes);
            }
            if(!empty($addPath)) {
                $this->pathRepository->add($addPath);
            }

            $this->nodeRepository->delete($deleteNodes, Dao::NO_FLUSH);

            $this->nodeRepository->getEntityManager()->flush();
            
            
        } catch (\InvalidArgumentException $e) {
            
        }
    }

    private function walkNodes($definition) {
        $toAdd = [];
        foreach ($definition->paths as $id => $path) {
            if(!isset($path->startNode) || !isset($path->endNode)) {
                continue;
            }
            foreach ([$path->startNode, $path->endNode] as $node) {
                if (!$this->nodeOnPositionExists($node->position)) {
                    $x = $this->nodeRepository->createNew(null, [
                        'floorPlan' => $this->floor,
                        'gpsCoordinates' => $node->position,
                        'type' => $node->type,
                        'name' => (isset($node->name) ? $node->name : null),
                        'room' => (isset($node->room) ? $node->room : null),
                        'fromFloor' => isset($node->fromFloor) ? $node->fromFloor : null,
                        'toFloor' => isset($node->toFloor) ? $node->toFloor : null,
                        'toBuilding' => isset($node->toBuilding) ? $node->toBuilding : null,
                    ]);
                    $this->sentNodes[] = $x;
                    $toAdd[$node->id] = $x;
                    $this->allNodes[] = $x;
                } else {
                    $entity = $this->getNodeInPosition($node->position);

                    foreach($node as $key=>$value) {
                        if(in_array($key, ['id','state'])) continue;
                        $method = "set".ucfirst($key);
                        $entity->$method($value);
                    }
                    $this->sentNodes[] = $entity;
                }
            }
        }
        return $toAdd;
    }
    
    private function walkPaths($definition) {
        $toAdd = [];
        foreach ($definition->paths as $id => $path) {
            if(!isset($path->startNode) || !isset($path->endNode)) {
                continue;
            }
            if(!$this->pathExistsBetween($path->startNode->position, $path->endNode->position)) {
                $this->allPaths[] = $toAdd[] = $this->pathRepository->createNew(null, [
                    'startNode' => $this->getNodeInPosition($path->startNode->position),
                    'endNode' => $this->getNodeInPosition($path->endNode->position),
                    'floor' => $this->floor,
                ]);
            }
        }
        return $toAdd;
    }

    public function findToDelete() {
        $toDelete = [];
        foreach($this->dbNodes as $dbNode) {
            foreach($this->sentNodes as $sent) {
                if($dbNode->position == $sent->position) {
                    continue 2;
                }
            }
            $toDelete[] = $dbNode;
        }
        return $toDelete;
    }

    private function getNodeInPosition($position) {
        foreach ($this->allNodes as $node) {
            if ($node->getGpsCoordinates() == $position) {
                return $node;
            }
        }
        return null;
    }

    private function nodeOnPositionExists($position) {
        return $this->getNodeInPosition($position) !== null;
    }
    
    private function getPathBetween($one, $two) {
        foreach($this->allPaths as $path) {
            if(($path->startNode->getGpsCoordinates() == $one && $path->endNode->getGpsCoordinates() == $two) || 
                    ($path->startNode->getGpsCoordinates() == $two && $path->endNode->getGpsCoordinates() == $one)) {
                return $path;
            }
        }
        return null;
    }
    
    private function pathExistsBetween($one, $two) {
        return $this->getPathBetween($one, $two) !== null;
    }

}

?>
