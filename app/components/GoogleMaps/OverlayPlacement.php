<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 2.3.13
 * Time: 14:27
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Components\GoogleMaps;


class OverlayPlacement extends BaseMapControl {

    private $overlayImage;

    public function setOverlayImage($overlayImage) {
        $this->overlayImage = $overlayImage;
    }

    public function getOverlayImage() {
        return $this->overlayImage;
    }

    public function render() {

        $template = $this->createTemplate();

        $template->setFile(__DIR__.'/templates/overlayPlacement.latte');

        $this->setMapSize($template, func_get_args());
        $template->overlayImage = $this->overlayImage;

        $template->render();
    }

}