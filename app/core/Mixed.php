<?php

/**
 * This file is part of the Maps (http://www.Maps.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Maps\Tools;

use Nette;

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Mixed extends Nette\Object {

    /**
     * Static class - cannot be instantiated.
     *
     * @throws \Maps\StaticClassException
     */
    final public function __construct() {
        throw new \Exception;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function getType($value) {
        return is_object($value) ? 'instanceof ' . get_class($value) : gettype($value);
    }

    /**
     * @param mixed $value
     * @param boolean $short
     * @return string
     */
    public static function toString($value, $short = FALSE) {
        if (is_array($value) || is_object($value)) {
            if (!$short) {
                return "\n" . print_r($value, TRUE);
            }

            return is_array($value) ? 'array(' . count($value) . ')' : get_class($value);
        }

        if (is_string($value) && strpos($value, "\n") !== FALSE) {
            return 'text';
        }

        $value = is_null($value) ? 'NULL' : $value;
        $value = $value === TRUE ? 'TRUE' : $value;
        $value = $value === FALSE ? 'FALSE' : $value;

        return $value . (!is_null($value) ? ' (' . gettype($value) . ')' : '');
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public static function isSerializable($value) {
        return is_scalar($value);
    }

    public static function sanitazeCKEditor($value) {
        static $texy;

        if (!$texy instanceof \Texy) {
            $texy = new \Texy();
            \Texy::$advertisingNotice = FALSE; //sorry, but it adds the notice every time someone edit the article... and thats weird
            $texy->setOutputMode(\Texy::HTML5);
            $texy->allowed['blocks'] = FALSE;
            $texy->allowed['figure'] = FALSE;
            $texy->allowed['heading/underlined'] = FALSE;
            $texy->allowed['heading/surrounded'] = FALSE;
            $texy->allowed['horizline'] = FALSE;
            $texy->allowed['list'] = FALSE;
            $texy->allowed['list/definition'] = FALSE;
            $texy->allowed['table'] = FALSE;
            $texy->allowed['phrase/strong'] = FALSE;
            $texy->allowed['phrase/em'] = FALSE;
            $texy->allowedStyles = array("color", "font-weight", "font-variant");
            //  $texy->htmlModule->allowed['font']=FALSE;
        }
        //replace wrong new lines
        $value = \Nette\Utils\Strings::replace($value, "|<p>&nbsp;</p>|", "");
        //remove empty pair tags
        $value = \Nette\Utils\Strings::replace($value, "#<[^>/]*>\s*</[^>]*>#", "");
        //remove subsequent &nbsps
        $value = \Nette\Utils\Strings::replace($value, "/(&nbsp;){2,}/", "&nbsp;");
        //remove nbsp at start of paragraph
        $value = \Nette\Utils\Strings::replace($value, "/(<p>&nbsp;)/", "<p>");
        
        
        $value = $texy->process($value);
        
        
        
        return $value;
    }

    public static function mapAssoc($collection, $key) {
        $arr = [];
        foreach($collection as $item) {
            if(!isset($item->$key)) {
               throw new \InvalidArgumentException('Key '.$key.' does not exists in every item.');
            }
            if(array_key_exists($item->$key, $arr)) {
                throw new Nette\InvalidStateException('Associative key '.$key.' is not unique in collection.');
            }
            $arr[$item->$key] = $item;
        }
        return $arr;
    }

    /**
     * @param $one string GPS coords
     * @param $two string GPS coords
     * @return float
     */
    public static function  calculateDistanceBetweenGPS($one, $two) {
        $one = explode(",", $one);
        $two = explode(",", $two);

        $lat1 = (float)$one[0];
        $lng1 = (float)$one[1];

        $lat2 = (float)$two[0];
        $lng2 = (float)$two[1];

        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
                sin($dLng / 2) * sin($dLng / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $R * $c;

        return $d * 1000;
    }

}