<?php

namespace Maps\Templates;
use Imagine\Imagick\Imagine;
use Maps\Components\ImageMagick;
use Nette\Image;
use Nette\Utils\Strings;

/**
 * Description of TemplateHelpers
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class TemplateHelpers {

    public static function loader($method) {
        if (\is_callable(__CLASS__ . '::' . $method)) {
            return (__CLASS__ . '::' . $method);
        }
    }

    public static function dateInWords($time, $format = 'j.n.Y H:i', $onlyDate=false) {
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof \DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }

        $delta = time() - $time;

        $delta = round($delta / 60);
        if ($delta >= 0) {
            if ($delta == 0 && !$onlyDate)
                return 'před okamžikem';
            if ($delta == 1 && !$onlyDate)
                return 'před minutou';
            if ($delta < 60 && !$onlyDate)
                return "před $delta minutami";
            if ($delta <= 75 && !$onlyDate)
                return 'před hodinou';
            if ($delta < 1440 && date("j") == date("j", $time))
                return 'dnes, ' . date("H:i", $time);
            if ($delta < 2880 && date("j") - 1 == date("j", $time))
                return 'včera, ' . date("H:i", $time);
        }
        return date($format, $time);
    }

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    public static function plural($n) {
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

    public static function linkEncode($s) {
        if (@eregi("^http:\/\/[[:alnum:]]+([-_\.]?[[:alnum:]])*\.[[:alpha:]]{2,4}(\/{1}[-_~&=\?\.a-z0-9]*)*$", $s))
            return NHtml::el('a')->href($s)->setText($s);
        if (@eregi("[^http:\/]{7}[[:alnum:]]+([-_\.]?[[:alnum:]])*\.[[:alpha:]]{2,4}(\/{1}[-_~&=\?\.a-z0-9]*)*$", $s))
            return NHtml::el('a')->href('http://' . $s)->setText($s);

        return $s;
    }

    public static function mailEncode($s) {
        if (@eregi("^[_a-zA-Z0-9\.\-]+@[_a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,4}$", $s))
            return NHtml::el('a')->href(Tools::entityEncode('mailto:' . $s))->setHtml(Tools::entityEncode($s));
        else
            return $s;
    }

    public static function hyperlinks($s) {
        $pattern = "(https?|ftp:((//)|(\\\\))+[\w\d:#@%/;$()~_?\+-=\\\.&]*)";
        return preg_replace('#(http://|ftp://|(www\.))([\w\-]*\.[\w\-\.]*([/?][^\s]*)?)#e', "'<a href=\"'.('\\1'=='www.'?'http://':'\\1').'\\2\\3\">'.((strlen('\\2\\3')>43)?(substr('\\2\\3',0,40).'&hellip;'):'\\2\\3').'</a>'", $s);
    }

    public static function removeEntities($s) {
        return preg_replace('#(&[^\s]*;)#', "", $s);
    }

    /**
     * Truncates string containing XHTML tags to maximal length
     * @param string UTF-8 encoding
     * @param int
     * @param string UTF-8 encoding
     * @return string
     * @copyright Jakub Vrána, http://php.vrana.cz/
     * @author Endrju (modifications)
     */
    public static function xhtmlTruncate($s, $maxLen, $append = "\xE2\x80\xA6") {
        // ma vubec smysl retezec zkracovat?
        if (iconv_strlen(trim(strip_tags($s), 'UTF-8')) > $maxLen) {
            // zkratime $maxLen o delku $append
            $maxLen = $maxLen - iconv_strlen($append, 'UTF-8');
            // pokud je nyni $maxLen kratsi bez $appedm vratime samotonty $append
            if ($maxLen < iconv_strlen($append, 'UTF-8')) {
                return $append;
            }
            $s = \Nette\Utils\Strings::normalize($s);
            // vybrane znaky, ktere muzou ukoncovat vetu nebo vyznam casti vety
            $separators = array(' ', ',', '.', ';', '?', '!', ':');
            $pos = 0; // pozice posledniho nalezeneho separatoru
            // prekodujeme z UTF-8 do windows-1250,
            // znaky s diakritikou atp pak budou pocitany jako jeden cely znak
            $s = @iconv('UTF-8', 'windows-1250//TRANSLIT', $s);
            $INITAL_S = $s; // originalni vstupni retezec $s, ktery nebude po dobu behu programu zmenen.
            $append = iconv('UTF-8', 'windows-1250//TRANSLIT', $append);

            // odstranime neviditelne znaky,
            // ktere se ve vygenerovanem a zobrazenem HTML textu stejne nezobrazi


            $customWhitespaces = array("\x09", "\x0a", "\x0d", "\x00", "\x0b");

            foreach ($customWhitespaces AS $customWhitespace) {
                $s = trim($s, $customWhitespace);
            }

            $length = 0;
            $tags = array(); // dosud neuzavřené značky
            for ($i = 0; $i < strlen($s) && $length < $maxLen; $i++) {
                switch ($s[$i]) {
                    case '<':
                        // načtení značky
                        $start = $i + 1;
                        while ($i < strlen($s) && $s[$i] != '>' && !ctype_space($s[$i])) {
                            $i++;
                        }
                        $tag = strtolower(substr($s, $start, $i - $start));
                        // přeskočení případných atributů
                        $in_quote = '';
                        while ($i < strlen($s) && ($in_quote || $s[$i] != '>')) {
                            if (($s[$i] == '"' || $s[$i] == "'") && !$in_quote) {
                                $in_quote = $s[$i];
                            } elseif ($in_quote == $s[$i]) {
                                $in_quote = '';
                            }
                            $i++;
                        }
                        if ($s[$start] == '/') { // uzavírací značka
                            array_shift($tags); // v XHTML dokumentu musí být vždy uzavřena poslední neuzavřená značka
                        } elseif ($s[$i - 1] != '/') { // otevírací značka
                            array_unshift($tags, $tag);
                        }
                        break;

                    case '&':
                        $length++;
                        while ($i < strlen($s) && $s[$i] != ';') {
                            $i++;
                        }
                        break;

                    default:
                        $length++;
                        if (in_array($s[$i], $customWhitespaces)) {
                            $length--;
                        }

                        /* V případě kódování UTF-8:
                          while ($i+1 < strlen($s) && ord($s[$i+1]) > 127 && ord($s[$i+1]) < 192) {
                          $i++;
                          } */

                        // je znak separatorem?
                        if (in_array($s[$i], $separators)) {
                            // * a neni nasledujici (nebo predchozi) znak totozny,
                            // jako nyni nacteny separator?
                            if ($i > 1 && $i + 1 < strlen($s) && ($s[$i] != $s[$i + 1]) && ($s[$i - 1] != $s[$i])) {
                                $pos = $i; // pak ulozime pozici separatoru
                            } // nakonec tak ziskame pozici posledniho separatoru
                            // * tou druhou podminkou chceme zachytit pripady, kdy je v textu
                            // nekolik separatotu (napr. tecek) za sebou, pak to nebudeme povazovat za separator,
                            // ale jako vyznam k predchazejicimu slovu (vete).
                            // Navic pokud by takovy separator byl na konci oriznuteho textu, bude tak v podstate
                            // nahrazen volitelnym retezcem $append (defaultne trojtecka).
                        }
                }
            }

            // pokud nenalezneme hledany pocet znaku, obsah je nejspis slozen ciste z HTML tagu
            // (napr. flashova videa a jine medialni objekty)
            if ($length >= $maxLen) {

                $s = substr($s, 0, $i);

                // uzavreme vsechny tagy
                $enclosingTags = "";
                if ($tags) {
                    $enclosingTags .= "</" . implode("></", $tags) . ">";
                }

                // Nyni potrebujeme probublat od konce pres vsechny tagy az na konec zobrazovaneho textu $s
                // (tam pak budeme pridavat $append)
                $s_beforeInnerEnclosingTags = $s;
                $innerEnclosingTags = "";
                while (substr(rtrim($s_beforeInnerEnclosingTags), - 1, 1) == ">") {
                    $innerEnclosingTags = strrchr($s_beforeInnerEnclosingTags, "<");
                    $s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, strlen($s_beforeInnerEnclosingTags) - strlen($innerEnclosingTags));
                }

                // Pokud je nastaven $append na trojtecku,
                // orezeme jeste samotne tecky na konci rezce (pokud nejake jsou)
                if ($append == iconv('UTF-8', 'windows-1250//TRANSLIT', "\xE2\x80\xA6")) {
                    $s_beforeInnerEnclosingTags = rtrim($s_beforeInnerEnclosingTags, '.');
                }

                // pokud byl nejaky separator nalezen
                // a zaroven znak za zkracovanym textem neni separatorem
                if (($pos > 0) && (!in_array(substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1), $separators))) {
                    // tak orizneme text za poslednim nalezenym separatorem
                    $s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, $pos);
                }
                // nebo take pokud byl nejaky separator nalezen
                // a zaroven 2 znaky za zkracovanym textem jsou 2 stejne separatory (viz. * o neco vyse - stejny pripad)
                else if (($pos > 0) && (in_array(substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1), $separators)) && ((substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1) == ((substr($INITAL_S, strlen($s_beforeInnerEnclosingTags) + 1, 1)))))) {
                    // tak orizneme text za poslednim nalezenym separatorem a dva vyse zminene ignorujeme
                    $s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, $pos);
                }

                // Nyni vse spojime dohromady a pripojime $append
                $s = $s_beforeInnerEnclosingTags . $append . $innerEnclosingTags . $enclosingTags;
            }

            // Vystupni retezec prekodujeme zpet z windows-1250 do UTF-8
            $s = iconv('windows-1250', 'UTF-8//TRANSLIT', $s);
        }

        return $s;
    }

    /**
     * 
     * @param type $path
     * @param type $width
     * @param type $height
     * @param type $quality
     * @param type $crop true = crop image to match width x height (no deformation), false = resize with aspect ration (dimensions <= specified)
     */
    public static function thumbnail($path, $width = 100, $height = 100, $quality = 85, $crop = false) {
        if(!file_exists(WWW_DIR.'/'.$path) || !is_file(WWW_DIR.'/'.$path)) {
            return "/".$path;
        }
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $dir = \Nette\Utils\Strings::split(trim(pathinfo($path, PATHINFO_DIRNAME), "\\/"), "#[\\/]#");
        if ($dir[0] == 'data') {
            array_shift($dir);
        }

        $thumb_wwwpath = 'data/thumbs/' . implode("/", $dir) . "/" . $filename . "." . $width . "x" . $height . "." . $quality . "." . ((int) $crop) . "." . $extension;
        if (file_exists(WWW_DIR . "/" . $thumb_wwwpath) && is_file(WWW_DIR . "/" . $thumb_wwwpath)) {
            return "/" . $thumb_wwwpath;
        } else {
            if (self::generateThumbnail($path, $thumb_wwwpath, $width, $height, $quality, $crop)) {
                return "/" . $thumb_wwwpath;
            }
        }
        throw new \Nette\InvalidStateException("Unable to convert $path to thumbnail.");
    }

    private static function generateThumbnail($original, $thumbpath, $width, $height,$quality, $crop) {
        if ($original == "") {
            return "";
        }
        //createthumb

        $image = Image::fromFile(WWW_DIR . '/' . $original);
        $image->resize($width, $height, ($crop ? Image::FILL | Image::EXACT : Image::FIT | Image::SHRINK_ONLY));

        $image->sharpen();
        $fullpath = WWW_DIR . '/' . $thumbpath;
        if (!(file_exists(dirname($fullpath)) && is_dir(dirname($fullpath)))) {
            mkdir(dirname($fullpath), 0777, true);
        }
        if ($image->save($fullpath, $quality)) {
            chmod($fullpath, 0666);
            return true;
        }
        return false;
    }


    public static function image($path, $format='png', $force=false, $width, $height) {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), WWW_DIR.'/'.$path);
        $fullpath = WWW_DIR.'/'.$path;
        $newPath = pathinfo($fullpath, PATHINFO_DIRNAME). '/' . pathinfo($fullpath,PATHINFO_FILENAME).'.'.$format;
        if(is_file($newPath)) {
            return str_replace(WWW_DIR,'',$newPath);
        }
        if(!($force == false && Strings::match($mime, "#image/*#")) || ($force && !Strings::match($mime, '#image/'.$format.'#'))) {
            //try converting it using imagemagick
            $i = new ImageMagick($fullpath);

            switch ($format) {
                case 'jpg':
                    $type = Image::JPEG;
                    break;
                case 'gif':
                    $type = Image::GIF;
                    break;
                default:
                    $type = Image::PNG;

            }

            $i->save($newPath,null, $type, [
                'density'=> 100,
                'trim'=> true,
                'geometry' => $width.'x'.($height == null?$width:$height.'!').'>',

            ]);

            return str_replace(WWW_DIR,'',$newPath);
        }
        return $path;

    }

}
