<?php

namespace Maps\Components;

use Maps\InvalidArgumentException;
use Maps\InvalidStateException;
use Maps\ShellCommandException;
use Nette;
use Nette\Image;



/**
 * Manipulation with large images using ImageMagick.
 *
 * <code>
 * $image = Image::fromFile('bigphoto.jpg');
 * $image->resize(150, 100);
 * $image->sharpen();
 * $image->send();
 * </code>
 *
 * @author  David Grudl
 * @author Jan Langer
 */
class ImageMagick extends Image
{
    /** @var string  path to ImageMagick library */
    public static $path = '';

    /** @var string */
    public static $tempDir;

    /** @var string */
    private $file;

    /** @var bool */
    private $isTemporary = FALSE;

    /** @var int */
    private $width;

    /** @var int */
    private $height;
    /** @var int */
    private $page;


    /**
     * Wraps image file.
     * @param string $file
     * @param int $page page for multi-page files
     * @param string $format detected format
     * @throws \Maps\InvalidArgumentException
     */
    public function __construct($file, $page=NULL, & $format = NULL)
    {
        if(!function_exists('exec')) {
            throw new InvalidArgumentException("exec() function doesn't exists, probably disabled.");
        }

        exec("convert -version", $out, $returnCode);

        if(!Nette\Utils\Strings::startsWith($out[0],'Version: ImageMagick')) {
            throw new InvalidArgumentException("ImageMagick is not installed, or I cannot execute it.");
        }

        if($returnCode != 0) {
            throw new InvalidArgumentException("exec(convert -version) returned code ".$returnCode.", so it propably doesn't work...");
        }



        if (!is_file($file)) {
            throw new InvalidArgumentException("File '$file' not found.");
        }
        $this->page = $page;
        $format = $this->setFile(realpath($file));
        if ($format === 'JPEG') $format = self::JPEG;
        elseif ($format === 'PNG') $format = self::PNG;
        elseif ($format === 'GIF') $format = self::GIF;
    }



    /**
     * Returns image width.
     * @return int
     */
    public function getWidth()
    {
        return $this->file === NULL ? parent::getWidth() : $this->width;
    }



    /**
     * Returns image height.
     * @return int
     */
    public function getHeight()
    {
        return $this->file === NULL ? parent::getHeight() : $this->height;
    }



    /**
     * Returns image GD resource.
     * @return resource
     */
    public function getImageResource()
    {
        if ($this->file !== NULL) {
            if (!$this->isTemporary) {
                $this->execute("convert -strip %input %output", self::PNG);
            }
            $this->setImageResource(imagecreatefrompng($this->file));
            if ($this->isTemporary) {
                unlink($this->file);
            }
            $this->file = NULL;
        }

        return parent::getImageResource();
    }



    /**
     * Resizes image.
     * @param  mixed  $width in pixels or percent
     * @param  mixed  $height in pixels or percent
     * @param  int    $flags
     * @return ImageMagick  provides a fluent interface
     */
    public function resize($width, $height, $flags = self::FIT)
    {
        if ($this->file === NULL) {
            return parent::resize($width, $height, $flags);
        }

        $mirror = '';
        if ($width < 0) $mirror .= ' -flop';
        if ($height < 0) $mirror .= ' -flip';
        list($newWidth, $newHeight) = self::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);
        $this->execute("convert -resize {$newWidth}x{$newHeight}! {$mirror} -strip %input %output", self::PNG);
        return $this;
    }



    /**
     * Crops image.
     * @param  mixed  x-offset in pixels or percent
     * @param  mixed  y-offset in pixels or percent
     * @param  mixed  width in pixels or percent
     * @param  mixed  height in pixels or percent
     * @return ImageMagick  provides a fluent interface
     */
    public function crop($left, $top, $width, $height)
    {
        if ($this->file === NULL) {
            return parent::crop($left, $top, $width, $height);
        }

        list($left, $top, $width, $height) = self::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);
        $this->execute("convert -crop {$width}x{$height}+{$left}+{$top} -strip %input %output", self::PNG);
        return $this;
    }



    /**
     * Saves image to the file.
     * @param  string  filename
     * @param  int  quality 0..100 (for JPEG and PNG)
     * @param  int  optional image type
     * @return bool TRUE on success or FALSE on failure.
     */
    public function save($file = NULL, $quality = NULL, $type = NULL, $options = NULL)
    {
        if ($this->file === NULL) {
            return parent::save($file, $quality, $type);
        }

        $quality = $quality === NULL ? '' : '-quality ' . max(0, min(100, (int) $quality));
        if ($file === NULL) {
            $this->execute("convert $quality -strip %input %output", $type === NULL ? self::PNG : $type, $options);
            readfile($this->file);

        } else {
            $this->execute("convert $quality -strip %input %output", (string) $file, $options);
        }
        return TRUE;
    }



    /**
     * Change and identify image file.
     * @param  string  filename
     * @return string  detected image format
     */
    private function setFile($file)
    {
        $this->file = $file;
        $res = $this->execute('identify -format "%w,%h,%m" ' . escapeshellarg($this->file));
        if (!$res) {
            throw new InvalidStateException("Unknown image type in file '$file' or ImageMagick not available.");
        }
        list($this->width, $this->height, $format) = explode(',', $res, 3);
        return $format;
    }


    /**
     * Executes command.
     * @param string $command
     * @param bool $output
     * @param array $options
     * @throws \Maps\ShellCommandException
     * @return string
     */
    private function execute($command, $output = NULL, $options= NULL)
    {
        $arguments = "";
        if($options != NULL) {
            foreach($options as $argument => $value) {
                $arguments.=' -'.$argument;
                if(!is_bool($value)) {
                    $arguments.=' '.escapeshellarg($value);
                }
            }
        }


        $command = str_replace('%input', $arguments.' '.escapeshellarg($this->file.($this->page!=NULL?"[".$this->page."]":"")), $command);
        if ($output) {
            $newFile = is_string($output)
                ? $output
                : (self::$tempDir ? self::$tempDir : dirname($this->file)) . '/' . uniqid('_tempimage', TRUE) . image_type_to_extension($output);
            $command = str_replace('%output', escapeshellarg($newFile), $command);
        }



        $lines = array();
        exec(self::$path . $command, $lines, $status); // $status: 0 - ok, 1 - error, 127 - command not found?
        if ($status != 0) {
            throw new ShellCommandException("Unknown error while calling ImageMagick. Command " . $command);
        }
        if ($output) {

            if ($this->isTemporary) {
                unlink($this->file);
            }
            $this->setFile($newFile);
            $this->isTemporary = !is_string($output);
        }

        return $lines ? $lines[0] : FALSE;
    }



    /**
     * Delete temporary files.
     * @return void
     */
    public function __destruct()
    {
        if ($this->file !== NULL && $this->isTemporary) {
            unlink($this->file);
        }
    }

}