<?php

namespace maesierra\Japo\UTF8;
/**
 * Helper to add some UTF8 functions that aren't still available on mbytes for php 5.6
 */

class UTF8Utils {


    private static function mbSetup() {
        mb_language('Neutral');
        mb_internal_encoding('UTF-8');
        mb_detect_order(['UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII']);
    }

    public static function ord($c) {
        self::mbSetup();
        $result = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
        return is_array($result) === true ? $result[1] : false;

    }

    public static function chr($src) {
        self::mbSetup();
        return mb_convert_encoding('&#' . intval($src) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    public static function toCharArray($string) {
        return preg_split('//u',$string, -1, PREG_SPLIT_NO_EMPTY);
    }


}