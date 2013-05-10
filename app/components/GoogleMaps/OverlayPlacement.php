<?php


namespace Maps\Components\GoogleMaps;


use Maps\Components\Forms\Form;
use Maps\Model\Floor\GeoreferenceForm;

/**
 * Component for definition of ground control points
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
 */
class OverlayPlacement extends BaseMapControl {

    /** @var string path to image which will be georeferenced */
    private $overlayImage;

    /**
     * @param string $overlayImage path to image
     */
    public function setOverlayImage($overlayImage) {
        $this->overlayImage = $overlayImage;
    }


    /**
     * @return string path to image
     */
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