<?php

namespace DataGrid\Columns;

use Nette;
use Nette\Image;
/**
 * Representation of image data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class ImageColumn extends Column {
    
    private $width = 100;
    private $height = 100;
    private $dir = null;
    
    public function setThumbSize($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    public function setThumbDir($dir) {
        $this->dir = $dir;
    }
    

    /**
     * Formats cell's content.
     * @param  mixed
     * @param  \DibiRow|array
     * @return string
     */
    public function formatContent($value, $data = NULL) {
        foreach ($this->formatCallback as $callback) {
            if (is_callable($callback)) {
                $value = call_user_func($callback, $value, $data);
            }
        }
       // $value = $this->getThumbnail($value);
        $image = Nette\Utils\Html::el('img')->src(\SeriesCMS\Templates\TemplateHelpers::thumbnail($value,$this->width,$this->height));

        return (string) $image;
    }
    
    private function getThumbnail($value) {
        if($value == "") {
            return "";
        }
        if($this->dir == null) {
            throw new \Nette\InvalidStateException("Thumbnail directory is not set.");
        }
        $filename = pathinfo($value, PATHINFO_FILENAME);
        $ext = pathinfo($value, PATHINFO_EXTENSION);
        
        $thumbpath = $this->dir."/".$filename."_".$this->width.$this->height.".".$ext;
        $fullpath = \Nette\Environment::expand("%wwwDir%/$thumbpath");
        if(file_exists($fullpath) && is_file($fullpath)) {
            return "/".$thumbpath;
        }
        //createthumb
        
        $image = Image::fromFile(\Nette\Environment::expand("%wwwDir%{$value}"));
        $image->resize($this->width, $this->height, Image::FIT | Image::SHRINK_ONLY);
        
        $image->sharpen();
        if($image->save($fullpath)) {
            chmod($fullpath, 0666);
            return "/".$thumbpath;
        }
        return "";
        
    }

}