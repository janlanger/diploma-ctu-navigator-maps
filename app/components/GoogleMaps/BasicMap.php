<?php
namespace Maps\Components\GoogleMaps;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;

/**
 * Basic Map optionally with geodecoder
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
 */
class BasicMap extends BaseMapControl{

    /** @var bool is geodecoder enabled */
    private $geodecoderEnabled = FALSE;
    /** @var IControl form field for addres (=geodecoder source) */
    private $geodecoderAddress;

    /** @var  IControl form field for GPS coords (=geodecoder destination) */
    private $geodecoderGPS;


    /**
     * Enable gendecoder support
     * @param BaseControl $addressField address source
     * @param BaseControl $gpsField coordinates destination
     */
    public function enableGeodecoder(BaseControl $addressField, BaseControl $gpsField) {
        if(trim($gpsField->value) != "") {
            $this->setCenter($gpsField->value);
            $this->addPoint($gpsField->value, ['draggable'=>TRUE]);
        }
        $this->geodecoderEnabled = TRUE;
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
