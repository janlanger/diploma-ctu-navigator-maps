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
        return $this->baseUrl.'/'.Strings::webalize($plan->floor->building->name).'/'.$plan->floor->floorNumber;
    }

    public function generateTiles(Plan $plan) {
        if($plan->getReferenceTopLeft() == null ||
            $plan->getReferenceTopRight() == null ||
            $plan->getReferenceBottomRight() == null) {
            throw new InvalidStateException('All reference points are not set.');
        }
        $this->plan = $plan;
        $this->sourceFile = $this->prepareFile(WWW_DIR.'/data/plans/raw/'.$plan->getPlan(), $plan->getSourceFilePage());
        $this->wrapper = new GDALWrapper();
        $file = $this->translateImage();
        $dir = $this->prepareDirectory();
        $this->generate($file, $dir);

        //cleanup
        foreach(Finder::findFiles(basename($file).'*')->in(WWW_DIR.'/../temp') as $f) {
  //          unlink($f->getRealPath());
        }
//        unlink($this->sourceFile);
    }

    private function translateImage() {
        $translated = dirname($this->sourceFile).'/'.md5(basename($this->sourceFile)).'.png';
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
            mkdir($fullPath,0777,true);
        }
        return $fullPath;
    }

    private function generate($file, $destination) {
        $this->wrapper->generate($file, $destination, $this->minZoom, $this->maxZoom);
    }

    private function prepareFile($sourcePath, $page=null) {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $sourcePath);
        $tempFile = $this->wwwDir.'/../temp/gdal_temp.png';
        if(is_file($tempFile)) {
            @unlink($tempFile);
        }

        if($mime != "image/png") {
            $i = new ImageMagick($sourcePath, $page-1);
            $i->save($tempFile, null, $i::PNG, [
		'transparent'=>'white',
		'type'=>'TrueColorMatte',
		'depth'=>8,
                'density' => 300,
                'trim' => true,
            ]);
        }
        return $tempFile;

    }

}
