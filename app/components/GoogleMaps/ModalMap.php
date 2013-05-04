<?php

namespace Maps\Components\GoogleMaps;

/**
 * Handles modal map - has ability to return payload and don't generate the map
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
 */
class ModalMap extends BaseMapControl {

    public function render() {

        $template = $this->createTemplate();
        $template->setFile(__DIR__ . '/templates/modalMap.latte');
        $this->setMapSize($template, func_get_args());


        $template->render();
    }

    /**
     * @return array payload for map initialization
     */
    public function getPayload() {

        $layers = [];
        $baseUri = $this->template->baseUri;

        foreach($this->getCustomLayers() as $title => $l) {
            $layers[$title] = $baseUri. "/".$l;
        }
        $paths = [];
        foreach($this->getPaths() as $path) {
            $paths[] = [
                'start' => $path[0],
                'end' => $path[1],
            ];
        }

        return [
            "zoom"=> $this->getZoomLevel(),
            "center" => ['lat' => $this->getCenter()['lat'], 'lng' => $this->getCenter()['long']],
            "customLayers" => $layers,
            "points" => $this->getPoints(),
            "paths" => $paths,
            "pathOptions" => $this->getPathOptions(),
        ];
    }

}