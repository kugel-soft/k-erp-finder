<?php

namespace Kugel\Utils;

class StringUtils {
    public static function startsWith($haystack, $needle) {
        $len = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    
    public static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
    
    public static function isEmpty($input) {
        return empty(trim($input));
    }
    
    public static function contains($haystack, $needle) {
        $position = strpos($haystack, $needle);
        return $position !== FALSE;
    }
}