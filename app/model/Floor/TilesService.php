<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 7.3.13
 * Time: 16:34
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Components\GoogleMaps\GDALWrapper;
use Maps\Components\ImageMagick;
use Nette\InvalidStateException;
use Nette\NotImplementedException;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class TilesService extends Object {

    private $sourceFile;
    /** @var GDALWrapper */
    private $wrapper;
    private $baseUrl;
    private $wwwDir;

    /** @var Plan */
    private $plan;

    private $maxZoom=21;
    private $minZoom=16;

    public function __construct($baseUrl, $wwwDir, $minZoom, $maxZoom) {
        $this->baseUrl = $baseUrl;
        $this->wwwDir = $wwwDir;
        $this->minZoom = $minZoom;
        $this->maxZoom = $maxZoom;
    }

    public function getTilesBasePath($plan) {
        return $this->baseUrl.'/'.($plan->floor->building->id).'/'.$plan->floor->id;
    }

    public function generateTiles(Plan $plan) {
        if($plan->getReferenceTopLeft() == NULL ||
            $plan->getReferenceTopRight() == NULL ||
            $plan->getReferenceBottomRight() == NULL) {
            throw new InvalidStateException('All reference points are not set.');
        }
        $this->plan = $plan;



        $this->sourceFile = $this->prepareFile(WWW_DIR.'/data/plans/raw/'.$plan->getPlan(), $plan->getSourceFilePage());
        $this->wrapper = new GDALWrapper();
        $file = $this->translateImage();
        $dir = $this->prepareDirectory();
        $this->generate($file, $dir);


        $this->computeBoundingCoordinates($this->plan);

        $this->plan->setMaxZoom($this->maxZoom);
        $this->plan->setMinZoom($this->minZoom);

        //cleanup
        foreach(Finder::findFiles(basename($file).'*')->in(WWW_DIR.'/../temp') as $f) {
            unlink($f->getRealPath());
        }
        unlink($this->sourceFile);
    }

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

    private function prepareDirectory() {
        $tilesDir = $this->getTilesBasePath($this->plan);
        $fullPath = $this->wwwDir.'/'.$tilesDir;

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
        return $fullPath;
    }

    private function generate($file, $destination) {
        $this->wrapper->generate($file, $destination, $this->minZoom, $this->maxZoom);
    }

    private function prepareFile($sourcePath, $page=NULL) {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $sourcePath);
        $tempFile = $this->wwwDir.'/../temp/gdal_temp.png';
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
