<?php


namespace Maps\Components\GoogleMaps;


use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\Metadata\NodeProperties;
use Maps\Model\Metadata\Queries\ActiveProposals;
use Nette\Application\UI\Control;

class ProposalEditor extends Control {

    /** @var Dao */
    private $proposalRepository;

    private $activeRevision;



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

        $template->proposals = $this->getProposals();
        $template->collisions = $this->collisionResolution();



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
            $items = $this->proposalRepository->fetch(new ActiveProposals());
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
            $form->addCheckbox('proposal'.$proposal->id);
        }
        $form->addSubmit("send", 'Zpracovat');
    }

    private function collisionResolution() {
        $proposals = $this->getProposals();
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

        dump($collisions);
    }

}