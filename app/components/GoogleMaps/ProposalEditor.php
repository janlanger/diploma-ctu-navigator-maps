<?php


namespace Maps\Components\GoogleMaps;


use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\NodeProperties;
use Maps\Model\Metadata\Queries\ActiveProposals;
use Maps\Model\Metadata\Queries\FloorExchangePaths;
use Maps\Model\Metadata\Revision;
use Nette\Application\UI\Control;

/**
 * Proposal editor component main class
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
 */
class ProposalEditor extends Control {
    /** @var callable on form success event */
    private $submitHandler;
    /** @var  array */
    private $revisionDictionary;

    /** @var Dao */
    private $proposalRepository;

    /** @var Revision actual revision */
    private $activeRevision;

    /**
     * @param array $revisionDictionary
     */
    public function setRevisionDictionary($revisionDictionary) {
        $this->revisionDictionary = $revisionDictionary;
    }

    /**
     * @return array
     */
    public function getRevisionDictionary() {
        return $this->revisionDictionary;
    }


    function __call($name, $arguments) {
        $mapComponent = $this->getMapComponent();
        if(method_exists($mapComponent, $name)) {
            call_user_func_array(array($mapComponent, $name), $arguments);
        } else {
            parent::__call($name, $arguments);
        }
    }



    /**
     * @return PolyLinesEditor
     */
    private function getMapComponent() {
        return $this['mapEditor'];
    }

    /**
     * @param Revision $activeRevision
     */
    public function setActiveRevision($activeRevision) {
        $this->activeRevision = $activeRevision;
    }


    /**
     * @param Dao $proposalRepository
     */
    public function setProposalRepository(Dao $proposalRepository) {
        $this->proposalRepository = $proposalRepository;
    }



    public function render() {
        if($this->activeRevision != NULL) {
            $nodes = $this->activeRevision->nodes;

            $nodeIds = [];
            foreach ($nodes as $node) {
                $this->addPoint($node->properties->gpsCoordinates, [
                    "draggable" => TRUE,
                    "title" => $this->getNodeTitle($node->properties),
                    "type" => $node->properties->type,
                    "appOptions" => $node->jsonSerialize(),
                ]);
                if(in_array($node->properties->getType(), ['elevator','stairs','passage'])) {
                    $nodeIds[] = $node->properties->id;
                }
            }

            $paths = $this->activeRevision->paths;

            foreach ($paths as $path) {
                    $this->addPath($path->properties->startNode->gpsCoordinates, $path->properties->endNode->gpsCoordinates);
            }
            if(!empty($nodeIds)) {
                $floorExchange = $this->getPresenter()->context->em->getRepository('Maps\\Model\\Metadata\\FloorConnection')->fetchAssoc(new FloorExchangePaths($nodeIds, $this->activeRevision), 'id');
                $this->setFloorExchangePaths($floorExchange);
            }
        }

        $template = $this->createTemplate();

        $template->setFile(__DIR__ . '/templates/proposalEditor.latte');

        $args = func_get_args();
        if (!empty($args)) {
            $args = array_shift($args);

            if (isset($args['size'])) {
                $template->mapWidth = $args['size'][0];
                $template->mapHeight = $args['size'][1];
            }
        }
        $proposals = $this->getProposals();
        $template->collisions = $this->collisionResolution($proposals);
        $template->proposals = $proposals;



        $template->render();
    }

    public function createComponentMapEditor($name) {
        $editor = new PolyLinesEditor();
        $editor->overridden = TRUE;
        return $editor;
    }

    /**
     * @return Changeset[]
     */
    private function getProposals() {
        static $items;
        if($items == NULL && $this->activeRevision != NULL) {
            $items = $this->proposalRepository->fetchAssoc(new ActiveProposals(NULL, $this->activeRevision), 'id');
        }
        if($items == NULL) {
            $items = array();
        }
        return $items;

    }

    /**
     * @param NodeProperties $properties
     * @return string readable node title
     */
    private function getNodeTitle(NodeProperties $properties) {
        $title = "";
        $nodeTypes = $this->getMapComponent()->getNodeTypes();
        if(isset($nodeTypes[$properties->getType()])) {
            $title.= $nodeTypes[$properties->getType()]['legend'];
        }

        if($properties->getRoom() != "") {
            $title.= ": ".$properties->getRoom();
        }

        return $title;
    }

    public function createComponentProposalForm($name) {
        $form = new Form($this, $name);
        foreach($this->getProposals() as $proposal) {
            $form->addOptionList('proposal' . $proposal->id, NULL, ['approve'=>'Zařadit do revize','reject'=>'Zamítnout']);
            $form->addText('proposaltext'.$proposal->id);
        }
        $form->addTextArea("custom_changes");
        $form->addSubmit("send", 'Zpracovat');
        $form->addHidden("revision", $this->activeRevision->id);
        $form->onSuccess[] = $this->submitHandler;
    }

    /**
     * Finds collisions in proposals and returns collision map
     *
     * @param Changeset[] $proposals
     * @return array
     */
    private function collisionResolution($proposals) {
        $changesNodes = [];
        $changedPaths = [];
        foreach($proposals as $proposal) {
            foreach($proposal->nodes as $node) {
                if($node->original != NULL) {
                    $changesNodes[$proposal->id][$node->original->id] = TRUE;
                }
            }
            foreach($proposal->paths as $path) {
                if($path->original != NULL) {
                    $changedPaths[$proposal->id][$path->original->id] = TRUE;
                }
            }
        }

        $collisions = [];
        foreach($changesNodes as $proposalId => $nodes) {
            foreach($changesNodes as $secondId => $n) {
                if($proposalId == $secondId) continue;

                foreach($nodes as $nodeId => $foo) {
                    if(array_key_exists($nodeId, $n)) {
                        $collisions[$proposalId][$secondId] = TRUE;
                    }
                }
            }
        }

        foreach ($changedPaths as $proposalId => $path) {
            foreach ($changedPaths as $secondId => $p) {
                if ($proposalId == $secondId)
                    continue;

                foreach ($path as $pathId => $foo) {
                    if (array_key_exists($pathId, $p)) {
                        $collisions[$proposalId][$secondId] = TRUE;
                    }
                }
            }
        }
        return $collisions;
    }

    /**
     * @param callable $submitHandler
     */
    public function setSubmitHandler($submitHandler)
    {
        $this->submitHandler = $submitHandler;
    }

    /**
     * @return callable
     */
    public function getSubmitHandler()
    {
        return $this->submitHandler;
    }

    public function createComponentRevisionChanger() {
        $form = new Form();

        $form->addSelect("against",NULL, $this->revisionDictionary)
            ->setDefaultValue($this->activeRevision->id);

        $form->onSuccess[] = function($form) {
            $id = $form->values['against'];
            $revision = $this->proposalRepository->getEntityManager()->getRepository("Maps\\Model\\Metadata\\Revision")->find($id);
            $arr = ["nodes" => $revision->nodes->toArray()];

            foreach($revision->paths as $path) {
                $arr['paths'][] = [
                    'start' => explode(",",$path->properties->startNode->position),
                    'end' => explode(",", $path->properties->endNode->position),
                ];
            }

            echo json_encode($arr);
            exit;
        };

        return $form;
    }


}