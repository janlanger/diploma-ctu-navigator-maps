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

    private $nodeIconBase;

    private $customLayers = [];
    private $showLegend = FALSE;
    private $apiKey;
    private $center;
    private $zoomLevel=10;
    private $points = [];

    private $pathOptions;
    private $paths = [];


    private $types;

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

    public function addPoint($point, $options=[]) {
        $options['position'] = $this->formatCoordinates($point);
       /* if(!isset($options['icon']) && $this->types != NULL) {
            if(isset($options['type']) && isset($this->types[$options['type']])) {
                $options['icon'] = $this->types[$options['type']];
            }
            else {
                $options['icon'] = $this->pointsInfo['default'];
            }
        }*/
        $this->points[]= $options;
    }


    public function setPathOptions(array $options) {
        $this->pathOptions = $options;
    }

    public function addPath($start, $destination) {
        $this->paths[] = [$this->formatCoordinates($start), $this->formatCoordinates($destination)];
    }

    protected function formatCoordinates($point) {
        $parts = \Nette\Utils\Strings::split($point,"/;|,/");;
        //TODO some validation
        return ['lat'=>$parts[0],'long'=>$parts[1]];
    }

    public function addCustomTilesLayer($title, $basePath)
    {
        $this->customLayers[$title] = $basePath;
    }



    public function setNodeIconBase($nodeIconBase) {
        $this->nodeIconBase = $nodeIconBase;
    }



    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);

        $template->registerHelperLoader("Maps\\Templates\\TemplateHelpers::loader");
        if($this->apiKey == NULL) {
            throw new \Nette\InvalidStateException("Google Maps API key must be set before component rendering.");
        }
        $template->apiKey = $this->apiKey;

        $template->mapWidth = "200px";
        $template->mapHeight = "200px";

        $template->center = $this->center;
        $template->points = $this->points;

        $template->pathOptions = $this->pathOptions;
        $template->paths = $this->paths;

        $template->zoomLevel = $this->zoomLevel;

        $template->showLegend = $this->showLegend;
        $template->pointsTypes = $this->types;

        $template->iconsBasePath = $this->nodeIconBase;

        $template->customLayers = $this->customLayers;

        return $template;
    }

    public function showLegend($i) {
        $this->showLegend = $i;
    }

    public function setNodeTypes(array $types) {
        $this->types = $types;
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
