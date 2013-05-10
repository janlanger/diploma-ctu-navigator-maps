<?php

namespace Maps\Model\Floor\Service;

use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\Floor\Floor;
use Maps\Model\Metadata\Node;
use Maps\Model\Metadata\Path;
use Nette\Diagnostics\Debugger;

/**
 * Process metadata form submit
 *
 * @package Maps\Model\Floor\Service
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class MetadataFormProcessor {

    /** @var \Maps\Model\Dao  */
    private $nodeRepository;
    /** @var \Maps\Model\Dao  */
    private $pathRepository;
    /** @var Floor */
    private $floor;
    /** @var Node[] */
    private $dbNodes;
    /** @var Path[] */
    private $dbPaths;
    /** @var array  */
    private $nodesAdd = [];
    /** @var array  */
    private $nodesDelete = [];
    /** @var array  */
    private $pathsAdd = [];
    /** @var array  */
    private $pathsDelete = [];

    /**
     * @param Dao $nodeRepository
     * @param Dao $pathRepository
     * @param Floor $floor
     */
    function __construct(Dao $nodeRepository, Dao $pathRepository, $floor) {
        $this->nodeRepository = $nodeRepository;
        $this->pathRepository = $pathRepository;
        $this->floor = $floor;
    }

    /**
     * Main handler
     * @param Form $form
     */
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
            if($this->dbNodes == NULL) {
                $this->dbNodes = [];
            }
            if($this->dbPaths == NULL) {
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
            Debugger::log($e);
        }
    }

    /**
     * @param array $nodes
     */
    private function processNodes($nodes) {
        $unprocessedNodes = array_flip(array_keys($this->dbNodes));
        foreach($nodes as $node) {
            if(!isset($node->id) || !isset($this->dbNodes[$node->id])) {
                if($node == NULL) continue;
                $this->nodesAdd[] = $this->nodeRepository->createNew(NULL, array(
                    'floor' => $this->floor,
                    'gpsCoordinates' => $node->position,
                    'type' => $node->type,
                    'name' => (isset($node->name) ? $node->name : NULL),
                    'room' => (isset($node->room) ? $node->room : NULL),
                    'fromFloor' => isset($node->fromFloor) ? $node->fromFloor : NULL,
                    'toFloor' => isset($node->toFloor) ? $node->toFloor : NULL,
                    'toBuilding' => isset($node->toBuilding) ? $node->toBuilding : NULL,
                ));
                $node->addedId = count($this->nodesAdd) -1;
            } else {
                $entity = $this->dbNodes[$node->id];

                foreach($node as $key=>$value) {
                    if(in_array($key, ['id','state'])) continue;
                    $method = "set".ucfirst($key);
                    if($value == "") $value = NULL;
                    $entity->$method($value);
                }
                unset($unprocessedNodes[$node->id]);
            }
        }

        foreach($unprocessedNodes as $id =>$foo) {
            $this->nodesDelete[] = $this->dbNodes[$id];
        }
    }

    /**
     * @param array $paths
     * @param array $nodes
     */
    private function processPaths($paths, $nodes) {
        $unprocessedPaths = array_flip(array_keys($this->dbPaths));
        foreach($paths as $path) {
            $updated = FALSE;
            if(isset($nodes[$path->startNode]->id) && isset($nodes[$path->endNode]->id)) {
                //oba maji id - jsou z db - zkus najit zaklade id bodu
                $entity = $this->findPathWithNodes($nodes[$path->startNode]->id, $nodes[$path->endNode]->id);
                if($entity != NULL) {
                    $entity->length = $path->length;
                    $updated = TRUE;
                    if($entity->id != NULL)
                        unset($unprocessedPaths[$entity->id]);
                }
            }
            if(!$updated) {
                $begin = $this->getNodeWithPathId($path->startNode, $nodes);
                $end = $this->getNodeWithPathId($path->endNode, $nodes);
                if($begin == NULL || $end == NULL) {
                    continue; // not ended with markers on both sides
                }
                $this->pathsAdd[] = $this->pathRepository->createNew(NULL, [
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

    /**
     * @param int $f
     * @param int $s
     * @return Path|null
     */
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
        return NULL;
    }

    /**
     * @param int $nodeId
     * @param array $nodes
     * @return Node
     */
    private function getNodeWithPathId($nodeId, $nodes)
    {
        return (isset($nodes[$nodeId]->addedId)? $this->nodesAdd[$nodes[$nodeId]->addedId]:$this->dbNodes[$nodes[$nodeId]->id]);
    }

}

?>
