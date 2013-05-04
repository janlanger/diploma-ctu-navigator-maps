<?php
namespace Maps\Components\GoogleMaps;
use Maps\InvalidStateException;
use Maps\Model\Metadata\Queries\FloorByNodePropertiesId;

/**
 * Base class for all Google Maps components
 * @author Jan Langer, <langeja1@fit.cvut.cz>
 */
abstract class BaseMapControl extends \Nette\Application\UI\Control{

    /**
     * @var string base icons path
     */
    private $nodeIconBase;

    /** @var array custom layer paths */
    private $customLayers = [];
    /** @var bool show node legend in map? */
    private $showLegend = FALSE;

    /** @var  string google API key */
    private $apiKey;
    /** @var  string GPS position of center of map */
    private $center;
    /** @var int map initial zoom level */
    private $zoomLevel=10;
    /** @var array point definition */
    protected  $points = [];

    /** @var  array path style detinition */
    private $pathOptions;
    /** @var array paths definition */
    private $paths = [];
    /** @var arrat node types definition */
    private $types;

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Set custom layers array - rewrites already defined ones
     * @param array $customLayers
     */
    public function setCustomLayers($customLayers) {
        $this->customLayers = $customLayers;
    }

    /**
     * @return array
     */
    public function getCustomLayers() {
        return $this->customLayers;
    }

    /**
     * @param array $paths
     */
    public function setPaths($paths) {
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function getPaths() {
        return $this->paths;
    }

    /**
     * @param array $points
     */
    public function setPoints($points) {
        $this->points = $points;
    }

    /**
     * @return array
     */
    public function getPoints() {
        return $this->points;
    }

    /**
     * @return bool
     */
    public function getShowLegend() {
        return $this->showLegend;
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
            throw new InvalidStateException("Google Maps API key must be set before component rendering.");
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



    public function getNodeTypes() {
        return $this->types;
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

    public function getPathOptions() {
        return $this->pathOptions;
    }

}
