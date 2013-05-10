<?php

namespace Maps\Components\GoogleMaps;


use Maps\Components\ImageMagick;
use Maps\InvalidArgumentException;
use Maps\ShellCommandException;
use Nette\Diagnostics\Debugger;
use Nette\Image;
use Nette\Object;

/**
 * Wraps gdal tools execution. Requires exec function.
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\GoogleMaps
 */
class GDALWrapper extends Object {


    public function __construct() {

        if(!function_exists('exec')) {
            throw new InvalidArgumentException("GDAL requires exec() function, but it seems it's not available.");
        }

        exec('gdal_translate --version', $out, $status);
        if($status != 0) {
            throw new InvalidArgumentException("GDAL executable was not found. Is it really installed and reachable inside PATH?");
        }
    }

    /**
     * Translates source image and converts it to GeoTiff format
     *
     * @param string $source source file path
     * @param string $destination destination file path
     * @param string $topLeft coordinates of top left corner of image
     * @param string $topRight coordinates of top right corner of image
     * @param string $bottomRight coordinates of bottom right corner
     * @throws ShellCommandException if command returns an error
     */
    public function translate($source, $destination, $topLeft, $topRight, $bottomRight) {
        $image = new ImageMagick($source);
        $command= 'gdal_translate -of GTiff -expand rgba';
        $topLeft = explode(",", $topLeft);
        $topRight = explode(",", $topRight);
        $bottomRight = explode(",", $bottomRight);

        //gcp uses lat and longitude in opposite order than google maps

        $command.= ' -gcp 0 0 '.((double)$topLeft[1]).' '.((double)$topLeft[0]);
        $command.= ' -gcp '.$image->getWidth().' 0 '.((double)$topRight[1]).' '.((double)$topRight[0]);
        $command.= ' -gcp '.$image->getWidth().' '.$image->getHeight().' '.((double)$bottomRight[1]).' '.((double)$bottomRight[0]);
        $command.= ' '.escapeshellarg($source).' '.escapeshellarg($destination);

        $this->execute($command);
    }

    /**
     * Generates the tiles
     *
     * @param string $source source file with ground control points included
     * @param string $destinationDir path where the tiles will be generated
     * @param int $minZoom minimal zoom level to generate
     * @param int $maxZoom maximum zoom level to generate
     * @throws ShellCommandException if command returns an error
     */
    public function generate($source, $destinationDir, $minZoom = 16, $maxZoom = 21) {
        $this->execute('gdal2tiles -s EPSG:4326 -w none -z '.$minZoom.'-'.$maxZoom.' '.escapeshellarg($source).' '.escapeshellarg($destinationDir));
    }

    /**
     * Executes the command in shell
     *
     * @param $command
     * @throws \Exception|\Maps\ShellCommandException
     */
    private function execute($command) {
        exec($command.' 2>&1', $out, $status);
        try {
            if($status != 0) {
                throw new ShellCommandException("Shell command returned $status. Command $command");
            }
        } catch (ShellCommandException $e) {
            Debugger::log($e);
            throw $e;
        }
    }
}