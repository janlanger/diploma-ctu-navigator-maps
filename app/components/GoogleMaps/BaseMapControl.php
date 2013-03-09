<?php
namespace Maps\Components\GoogleMaps;
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 10.2.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */
abstract class BaseMapControl extends \Nette\Application\UI\Control{

    private $apiKey;
    private $center;
    private $zoomLevel=10;
    private $points = [];

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function setCenter($center) {
        $this->center = $this->formatCoordinates($center);
    }

    public function getCenter() {
        return $this->center;
    }

    public function setZoomLevel($zoomLevel) {
        $this->zoomLevel = $zoomLevel;
    }

    public function getZoomLevel() {
        return $this->zoomLevel;
    }

    public function addPoint($point) {
        $this->points[]= $this->formatCoordinates($point);
    }

    protected function formatCoordinates($point) {
        $parts = \Nette\Utils\Strings::split($point,"/;|,/");;
        //TODO some validation
        return ['lat'=>$parts[0],'long'=>$parts[1]];
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);

        $template->registerHelperLoader("Maps\\Templates\\TemplateHelpers::loader");
        if($this->apiKey == null) {
            throw new \Nette\InvalidStateException("Google Maps API key must be set before component rendering.");
        }
        $template->apiKey = $this->apiKey;

        $template->mapWidth = "200px";
        $template->mapHeight = "200px";

        $template->center = $this->center;
        $template->points = $this->points;
        $template->zoomLevel = $this->zoomLevel;
        return $template;

    }

    protected function setMapSize($template, $args) {
        if(!empty($args)) {
            $args = array_shift($args);

            if(isset($args['size'])) {
                $template->mapWidth = $args['size'][0];
                $template->mapHeight = $args['size'][1];
            }
        }
    }


}
