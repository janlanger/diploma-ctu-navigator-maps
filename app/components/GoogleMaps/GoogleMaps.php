<?php
namespace Maps\Components;
use Nette\Forms\Controls\BaseControl;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 6.2.13
 * Time: 23:25
 * To change this template use File | Settings | File Templates.
 */
class GoogleMaps extends \Nette\Application\UI\Control{

    private $apikey;
    private $center;
    private $points = [];
    private $geodecoderEnabled = false;
    private $geodecoderAddress;
    private $geodecoderGPS;

    public function setCenter($center) {
        $this->center = $this->formatCoordinates($center);
    }

    public function addPoint($point) {
        $this->points[]= $this->formatCoordinates($point);
    }

    private function formatCoordinates($point) {
        $parts = \Nette\Utils\Strings::split($point,"/;|,/");;
        //TODO some validation
        return ['lat'=>$parts[0],'long'=>$parts[1]];
    }



    public function setApikey($apikey) {
        $this->apikey = $apikey;
    }

    public function enableGeodecoder(BaseControl $addressField, BaseControl $gpsField) {
        if(trim($gpsField->value) != "") {
            $this->setCenter($gpsField->value);
            $this->addPoint($gpsField->value);
        }
        $this->geodecoderEnabled = true;
        $this->geodecoderAddress = $addressField;
        $this->geodecoderGPS = $gpsField;
    }

    public function render() {

        if($this->apikey == null) {
            throw new \Nette\InvalidStateException("Google Maps API key must be set before component rendering.");
        }
        $template = $this->createTemplate();
        $template->setFile(__DIR__.'/googleMaps.latte');

        $renderPar = func_get_args();
        if(!empty($renderPar)) {
            $renderPar = array_shift($renderPar);

            if(isset($renderPar['size'])) {
                $template->mapWidth = $renderPar['size'][0];
                $template->mapHeight = $renderPar['size'][1];
            }
        }

        $template->apikey = $this->apikey;
        $template->center = $this->center;
        $template->points = $this->points;
        $template->geodecoder = $this->geodecoderEnabled;
        $template->geodecoderAddress = $this->geodecoderAddress;
        $template->geodecoderGPS = $this->geodecoderGPS;
        $template->render();
    }

}
