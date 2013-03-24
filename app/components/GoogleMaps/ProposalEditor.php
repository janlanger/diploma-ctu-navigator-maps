<?php


namespace Maps\Components\GoogleMaps;


use Maps\Model\Dao;
use Maps\Model\Metadata\NodeProperties;
use Maps\Model\Metadata\Queries\ActiveProposals;

class ProposalEditor extends BaseMapControl {

    /** @var Dao */
    private $proposalRepository;

    private $activeRevision;



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
                "title" => $this->getNodeTitle($node->properties),
                "type" => $node->properties->type,
            ]);
        }

        $paths = $this->activeRevision->paths;

        foreach ($paths as $path) {
            $this->addPath($path->properties->startNode->gpsCoordinates, $path->properties->endNode->gpsCoordinates);
        }

        $template = $this->createTemplate();

        $template->setFile(__DIR__ . '/templates/proposalEditor.latte');

        $this->setMapSize($template, func_get_args());

        $template->proposals = $this->loadProposals();



        $template->render();
    }



    private function loadProposals() {
        return $this->proposalRepository->fetch(new ActiveProposals());
    }

    private function getNodeTitle(NodeProperties $properties) {
        $title = "";
        if(isset($this->nodeTypes[$properties->getType()])) {
            $title.= $this->nodeTypes[$properties->getType()]['legend'];
        }

        if($properties->getRoom() != "") {
            $title.= ": ".$properties->getRoom();
        }

        return $title;
    }

}