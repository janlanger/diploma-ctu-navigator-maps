<?php
namespace Maps\Components\GoogleMaps;
use Maps\InvalidStateException;
use Nette\Templating\ITemplate;

/**
 * Base class for all Google Maps components
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
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
    /** @var array node types definition */
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

    /**
     * @return string Google API key
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * @param string $center GPS of center of map
     */
    public function setCenter($center) {
        $this->center = $this->formatCoordinates($center);
    }

    /**
     * @return string GPS of center of map
     */
    public function getCenter() {
        return $this->center;
    }

    /**
     * @param int $zoomLevel initial zoom level
     */
    public function setZoomLevel($zoomLevel) {
        $this->zoomLevel = $zoomLevel;
    }

    /**
     * @return int initial zoom level
     */
    public function getZoomLevel() {
        return $this->zoomLevel;
    }

    /**
     * Adds point (marker to map)
     * @param string $point GPS coordinated
     * @param array $options points options (type, title...)
     */
    public function addPoint($point, $options=[]) {
        $options['position'] = $this->formatCoordinates($point);
        $this->points[]= $options;
    }

    /**
     * @param array $options path style definition
     */
    public function setPathOptions(array $options) {
        $this->pathOptions = $options;
    }

    /**
     * Adds path between $start and $end gpps coordinates
     *
     * @param string $start
     * @param string $destination
     */
    public function addPath($start, $destination) {
        $this->paths[] = [$this->formatCoordinates($start), $this->formatCoordinates($destination)];
    }

    /**
     * Converst string lat;lng format to array
     * @param string $point
     * @return array
     */
    protected function formatCoordinates($point) {
        $parts = \Nette\Utils\Strings::split($point,"/;|,/");;
        //TODO some validation
        return ['lat'=>$parts[0],'long'=>$parts[1]];
    }

    /**
     * @param string $title layer title
     * @param string $basePath base url for tiles
     */
    public function addCustomTilesLayer($title, $basePath)
    {
        $this->customLayers[$title] = $basePath;
    }

    /**
     * Sets base path for node icons
     * @param string $nodeIconBase
     */
    public function setNodeIconBase($nodeIconBase) {
        $this->nodeIconBase = $nodeIconBase;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Show map legend?
     * @param bool $i
     */
    public function showLegend($i) {
        $this->showLegend = $i;
    }

    /**
     * @param array $types
     */
    public function setNodeTypes(array $types) {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getNodeTypes() {
        return $this->types;
    }

    /**
     * Sets template variables for map width and height
     *
     * @param ITemplate $template
     * @param array $args
     */
    protected function setMapSize($template, $args) {
        if(!empty($args)) {
            $args = array_shift($args);

            if(isset($args['size'])) {
                $template->mapWidth = $args['size'][0];
                $template->mapHeight = $args['size'][1];
            }
        }
    }

    /**
     * @return array
     */
    public function getPathOptions() {
        return $this->pathOptions;
    }

}
