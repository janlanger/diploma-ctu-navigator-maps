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
    private $nodesAdd = [];
    private $nodesDelete = [];
    private $pathsAdd = [];
    private $pathsDelete = [];

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

            $sentPaths = $definition->paths;
            $sentNodes = $definition->nodes;

            $this->dbNodes = $this->nodeRepository->fetchAssoc(new \Maps\Model\BasicFetchByQuery(["floor" => $this->floor]), 'id');
            $this->dbPaths = $this->pathRepository->fetchAssoc(new \Maps\Model\BasicFetchByQuery(["floor" => $this->floor]), 'id');
            if($this->dbNodes == null) {
                $this->dbNodes = [];
            }
            if($this->dbPaths == null) {
                $this->dbPaths = [];
            }

            $this->processNodes($sentNodes);
            $this->processPaths($sentPaths, $sentNodes);

            

            if(!empty($this->nodesAdd)) {
                $this->nodeRepository->add($this->nodesAdd);
            }
            if(!empty($this->pathsAdd)) {
                $this->pathRepository->add($this->pathsAdd);
            }

            if(!empty($this->nodesDelete)) {
                $this->nodeRepository->delete($this->nodesDelete, Dao::NO_FLUSH);
            }

            if(!empty($this->pathsDelete)) {
                $this->pathRepository->delete($this->pathsDelete, Dao::NO_FLUSH);
            }

            $this->nodeRepository->getEntityManager()->flush();
            
            
        } catch (\InvalidArgumentException $e) {
            
        }
    }

    private function processNodes($nodes) {
        $unprocessedNodes = array_flip(array_keys($this->dbNodes));
        foreach($nodes as $node) {
            if(!isset($node->id) || !isset($this->dbNodes[$node->id])) {
                if($node == null) continue;
                $this->nodesAdd[] = $this->nodeRepository->createNew(null, array(
                    'floor' => $this->floor,
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
                $entity = $this->dbNodes[$node->id];

                foreach($node as $key=>$value) {
                    if(in_array($key, ['id','state'])) continue;
                    $method = "set".ucfirst($key);
                    if($value == "") $value = null;
                    $entity->$method($value);
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
                $entity = $this->findPathWithNodes($nodes[$path->startNode]->id, $nodes[$path->endNode]->id);
                if($entity != null) {
                    $entity->length = $path->length;
                    $updated = true;
                    if($entity->id != null)
                        unset($unprocessedPaths[$entity->id]);
                }
            }
            if(!$updated) {
                $begin = $this->getNodeWithPathId($path->startNode, $nodes);
                $end = $this->getNodeWithPathId($path->endNode, $nodes);
                if($begin == null || $end == null) {
                    continue; // not ended with markers on both sides
                }
                $this->pathsAdd[] = $this->pathRepository->createNew(null, [
                    "startNode" => $begin,
                    "endNode" => $end,
                    "length" => $path->length,
                    "floor" => $this->floor,
                ]);
            }
        }
        foreach($unprocessedPaths as $id => $foo) {
            $this->pathsDelete[] = $this->dbPaths[$id];
        }
    }


    private function findPathWithNodes($f, $s) {
        foreach($this->dbPaths as $id => $path) {
            if(($f == $path->startNode->id && $s == $path->endNode->id) ||
                ($s == $path->startNode->id && $f == $path->endNode->id)) {
                return $path;
            }
        }

        foreach($this->pathsAdd as $path) {
            if(($f == $path->startNode->id && $s == $path->endNode->id) ||
                ($s == $path->startNode->id && $f == $path->endNode->id)) {
                return $path;
            }
        }
        return null;
    }

    private function getNodeWithPathId($nodeId, $nodes)
    {
        return (isset($nodes[$nodeId]->addedId)? $this->nodesAdd[$nodes[$nodeId]->addedId]:$this->dbNodes[$nodes[$nodeId]->id]);
    }

}

?>