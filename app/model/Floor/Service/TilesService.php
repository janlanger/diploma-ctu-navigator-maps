<?php
namespace Maps\Model\Floor\Service;


use Maps\Components\GoogleMaps\GDALWrapper;
use Maps\Components\ImageMagick;
use Maps\InvalidStateException;
use Maps\Model\Floor\Plan;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

/**
 * Wrapping class for tiles generation
 *
 * @package Maps\Model\Floor\Service
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class TilesService extends Object {

    /** @var  string */
    private $sourceFile;
    /** @var GDALWrapper */
    private $wrapper;
    /** @var  string */
    private $baseUrl;
    /** @var string */
    private $wwwDir;

    /** @var Plan */
    private $plan;

    /** @var int  */
    private $maxZoom=21;
    /** @var int  */
    private $minZoom=16;

    /** @var string  */
    private $tmpDir;

    /**
     * @param string $baseUrl
     * @param string $wwwDir
     * @param int $minZoom
     * @param int $maxZoom
     */
    public function __construct($baseUrl, $wwwDir, $minZoom, $maxZoom) {
        $this->baseUrl = $baseUrl;
        $this->wwwDir = $wwwDir;
        $this->minZoom = $minZoom;
        $this->maxZoom = $maxZoom;
        $this->tmpDir = WWW_DIR.'/../temp/gdal';
    }

    /**
     * Returns base path from WWW_DIR to tiles of provided plan
     * @param Plan $plan
     * @return string path from WWW_DIR
     */
    public function getTilesBasePath($plan) {
        return $this->baseUrl.'/'.($plan->floor->building->id).'/'.$plan->floor->id;
    }

    /**
     * Executes plan tiles generation
     *
     * @param Plan $plan
     * @throws \Maps\InvalidStateException when some of reference points is not set
     */
    public function generateTiles(Plan $plan) {
        if($plan->getReferenceTopLeft() == NULL ||
            $plan->getReferenceTopRight() == NULL ||
            $plan->getReferenceBottomRight() == NULL) {
            throw new InvalidStateException('All reference points are not set.');
        }
        $this->plan = $plan;
        if(!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, TRUE);
        }



        $this->sourceFile = $this->prepareFile(WWW_DIR.'/data/plans/raw/'.$plan->getPlan(), $plan->getSourceFilePage());
        $this->wrapper = new GDALWrapper();


        $file = $this->translateImage();
        $tilesTemp = $this->tmpDir.'/'.str_replace($this->baseUrl."/", '', $this->getTilesBasePath($plan));

        $this->generate($file, $tilesTemp);
        $this->moveToFinalLocation($tilesTemp);
        $this->computeBoundingCoordinates($this->plan);

        $this->plan->setMaxZoom($this->maxZoom);
        $this->plan->setMinZoom($this->minZoom);

        //cleanup
        foreach(Finder::find('*')->from($this->tmpDir)->childFirst() as $f) {
            if ($f->isDir()) {
                @rmdir($f->getRealPath());
            }
            else {
                @unlink($f->getRealPath());
            }
        }
    }

    /**
     * Translate image
     *
     * @see GDALWrapper::translate
     * @return string translated file name
     */
    private function translateImage() {
        $translated = dirname($this->sourceFile).'/'.md5(basename($this->sourceFile)).'.tif';
        $this->wrapper->translate(
            $this->sourceFile,
            $translated,
            $this->plan->getReferenceTopLeft(),
            $this->plan->getReferenceTopRight(),
            $this->plan->getReferenceBottomRight());
        return $translated;
    }

    /**
     * Moves generated tiles from temp to final destination
     *
     * @param $tmpDir
     * @return string final destination
     */
    private function moveToFinalLocation($tmpDir) {
        $tilesDir = $this->getTilesBasePath($this->plan);
        $fullPath = WWW_DIR.'/'.$tilesDir;
        $tmpDir = realpath($tmpDir);

        if(is_dir($fullPath)) {
            //delete contents
            foreach(Finder::find('*')->from($fullPath)->childFirst() as $file) {
                if($file->isDir()) {
                    rmdir($file->getRealPath());
                }
                else {
                    unlink($file->getRealPath());
                }
            }
        } else {
            mkdir($fullPath,0777,TRUE);
        }
        foreach (Finder::find('*')->from($tmpDir) as $file) {
            $newPath = str_replace($tmpDir, $fullPath, $file->getRealPath());
            if($file->isDir() && !is_dir($newPath)) {
                mkdir($newPath, 0777, TRUE);
            } else {
                copy($file->getRealPath(), $newPath);
            }
        }

        return $fullPath;
    }

    /**
     * Executes the generation of tiles
     *
     * @see GDALWrapper::generate
     * @param string $file source file
     * @param string $destination destionation dir
     */
    private function generate($file, $destination) {
        if(!is_dir($destination)) {
            mkdir($destination, 0777, TRUE);
        }
        $this->wrapper->generate($file, $destination, $this->minZoom, $this->maxZoom);
    }

    /**
     * Converts the source file to input format for GDAL
     *
     * @param string $sourcePath
     * @param int|null $page
     * @return string
     * @throws \Maps\InvalidStateException
     */
    private function prepareFile($sourcePath, $page=NULL) {
        if(!file_exists($sourcePath)) {
            throw new InvalidStateException("Source file does not exists.");
        }
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $sourcePath);
        $tempFile = $this->tmpDir.'/gdal_temp.png';
        if(is_file($tempFile)) {
            @unlink($tempFile);
        }

        if($mime != "image/png") {
            $i = new ImageMagick($sourcePath, $page-1);
            $i->save($tempFile, NULL, $i::PNG, [
		'transparent'=>'white',
		'type'=>'TrueColorMatte',
		'depth'=>8,
                'density' => 300,
                'trim' => TRUE,
            ]);
        }
        return $tempFile;

    }

    /**
     * Computes bounding box around plan tiles
     * @param Plan $plan
     */
    private function computeBoundingCoordinates(Plan $plan) {
        $path = WWW_DIR.'/'.$this->getTilesBasePath($plan)."/".$this->maxZoom;

        $folders = scandir($path);
        sort($folders, SORT_NUMERIC);

        foreach($folders as $key => $value) {
            if(!is_numeric($value)) {
                unset($folders[$key]);
            }
        }
        $sw_x = (int) array_shift($folders);
        $ne_x = (int) array_pop($folders) +1; //+1 to get top right point (top left of next tile)

        $files = scandir($path . "/" . $sw_x);
        foreach($files as $key => $value) {
            if(Strings::endsWith($value, ".png")) {
                $files[$key] = (int) str_replace(".png","", $value);
            } else {
                unset($files[$key]);
            }
        }
        sort($files, SORT_NUMERIC);

        $sw_y = array_shift($files) -1; // -1 to jet down left point (top left of next tile)
        $sw_y = pow(2,$this->maxZoom) - $sw_y - 1;
        $ne_y = array_pop($files);
        $ne_y = pow(2,$this->maxZoom) - $ne_y -1;

        $sw = $this->tilesNumberToGps($sw_x, $sw_y, $this->maxZoom);
        $ne = $this->tilesNumberToGps($ne_x, $ne_y, $this->maxZoom);

        $plan->setBoundingSW($sw['lat'] . "," . $sw['lng']);
        $plan->setBoundingNE($ne['lat'] . "," . $ne['lng']);
    }

    /**
     * Converts tiles number to GPS position of top left corner
     *
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @return array['lng','lat']
     */
    private function tilesNumberToGps($x, $y, $zoom) {
        $lng = (($x * 256) - (256 * (pow(2,$zoom) - 1)))/((256 * (pow(2,$zoom))) / 360);

        while ($lng > 180) $lng -= 360;
        while ($lng < -180) $lng += 360;
        if($lng<0) $lng += 180;


        $exp = (($y * 256) - (256 * (pow(2,$zoom-1)))) / ((-256 * (pow(2,$zoom))) / (2 * M_PI));
        $lat = ((2 * atan(exp($exp))) - (M_PI / 2)) / (M_PI / 180);
        if ($lat < -90) $lat = -90;
        if ($lat > 90) $lat = 90;

        return ['lng'=>$lng, 'lat'=>$lat];

    }

}
