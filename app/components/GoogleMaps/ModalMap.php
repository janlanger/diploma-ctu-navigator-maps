<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 12.4.13
 * Time: 20:38
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Components\GoogleMaps;


class ModalMap extends BaseMapControl {

    public function render() {

        $template = $this->createTemplate();
        $template->setFile(__DIR__ . '/templates/modalMap.latte');
        $this->setMapSize($template, func_get_args());


        $template->render();
    }

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