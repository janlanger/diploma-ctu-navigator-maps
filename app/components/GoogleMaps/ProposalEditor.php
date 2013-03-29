<?php


namespace Maps\Components\GoogleMaps;


use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\Metadata\NodeProperties;
use Maps\Model\Metadata\Queries\ActiveProposals;
use Maps\Model\Metadata\Queries\RevisionProcessor;
use Nette\Application\UI\Control;

class ProposalEditor extends Control {
    private $submitHandler;
    private $revisionDictionary;

    /** @var Dao */
    private $proposalRepository;

    private $activeRevision;

    public function setRevisionDictionary($revisionDictionary) {
        $this->revisionDictionary = $revisionDictionary;
    }

    public function getRevisionDictionary() {
        return $this->revisionDictionary;
    }



    function __call($name, $arguments) {
        $mapComponent = $this->getMapComponent();
        if(method_exists($mapComponent, $name)) {
            call_user_func_array(array($mapComponent, $name), $arguments);
        }
    }



    /**
     * @return PolyLinesEditor
     */
    private function getMapComponent() {
        return $this['mapEditor'];
    }



    public function setActiveRevision($activeRevision) {
        $this->activeRevision = $activeRevision;
    }



    public function setProposalRepository(Dao $proposalRepository) {
        $this->proposalRepository = $proposalRepository;
    }



    public function render() {

        $nodes = $this->activeRevision->nodes;

        foreach ($nodes as $node) {
            $this->addPoint($node->properties->gpsCoordinates, [
                "draggable" => TRUE,
                "title" => $this->getNodeTitle($node->properties),
                "type" => $node->properties->type,
                "appOptions" => json_encode($node),
            ]);
        }

        $paths = $this->activeRevision->paths;

        foreach ($paths as $path) {
            $this->addPath($path->properties->startNode->gpsCoordinates, $path->properties->endNode->gpsCoordinates);
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
        $editor->overiden = TRUE;
        return $editor;
    }



    private function getProposals() {
        static $items;
        if($items == NULL) {
            $items = $this->proposalRepository->fetchAssoc(new ActiveProposals(NULL, $this->activeRevision), 'id');
        }
        if($items == NULL) {
            $items = array();
        }
        return $items;

    }

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
        }
        $form->addTextArea("custom_changes");
        $form->addSubmit("send", 'Zpracovat');
        $form->addHidden("revision", $this->activeRevision->id);
        $form->onSuccess[] = $this->submitHandler;
    }

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

    public function setSubmitHandler($submitHandler)
    {
        $this->submitHandler = $submitHandler;
    }

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