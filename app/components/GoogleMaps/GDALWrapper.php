<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 7.3.13
 * Time: 18:45
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Components\GoogleMaps;


use Maps\Components\ImageMagick;
use Nette\Image;
use Nette\Object;

class GDALWrapper extends Object {


    public function __construct() {

        if(!function_exists('exec')) {
            throw new \InvalidArgumentException("GDAL requires exec() function, but it seems it's not available.");
        }

        exec('gdal_translate --version', $out, $status);
        if($status != 0) {
            throw new \InvalidArgumentException("GDAL executable was not found. Is it really installed and reachable inside PATH?");
        }
    }

    public function translate($source, $destination, $topLeft, $topRight, $bottomRight) {
        $image = new ImageMagick($source);
        $command= 'gdal_translate -of PNG';
        $topLeft = explode(",", $topLeft);
        $topRight = explode(",", $topRight);
        $bottomRight = explode(",", $bottomRight);

        //gcp uses lat and longtitude in oposite order than google maps

        $command.= ' -gcp 0 0 '.((double)$topLeft[1]).' '.((double)$topLeft[0]);
        $command.= ' -gcp '.$image->getWidth().' 0 '.((double)$topRight[1]).' '.((double)$topRight[0]);
        $command.= ' -gcp '.$image->getWidth().' '.$image->getHeight().' '.((double)$bottomRight[1]).' '.((double)$bottomRight[0]);
        $command.= ' '.escapeshellarg($source).' '.escapeshellarg($destination);
        $this->execute($command);
    }

    public function generate($source, $destinationDir) {
        $this->execute('echo %GDAL_DATA%');
        $this->execute('gdal2tiles -s EPSG:4326 -g AIzaSyBTcOLRLRr9kEYkl98O1oFxicSsVqmdaIk -z 16-21 '.escapeshellarg($source).' '.escapeshellarg($destinationDir));
    }

    private function execute($command) {
        exec($command.' 2>&1', $out, $status);

     //   dump( $out, $status);
     //   echo $command;
    }
}