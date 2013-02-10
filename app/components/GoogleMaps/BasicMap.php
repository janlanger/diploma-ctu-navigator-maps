<?php
namespace Maps\Components\GoogleMaps;
use Nette\Forms\Controls\BaseControl;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 6.2.13
 * Time: 23:25
 * To change this template use File | Settings | File Templates.
 */
class BasicMap extends BaseMapControl{

    private $geodecoderEnabled = false;
    private $geodecoderAddress;
    private $geodecoderGPS;


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

        $template = $this->createTemplate();
        $template->setFile(__DIR__.'/templates/googleMaps.latte');
        $this->setMapSize($template, func_get_args());

        $template->geodecoder = $this->geodecoderEnabled;
        $template->geodecoderAddress = $this->geodecoderAddress;
        $template->geodecoderGPS = $this->geodecoderGPS;
        $template->render();
    }

}
