<?php

namespace maesierra\Japo\Lang;

use maesierra\Japo\UTF8\UTF8Utils;

/**
 * Class JapaneseLanguageHelper
 * Japanese language functions for grammar and character operations
 */
class JapaneseLanguageHelper {

    private static $verbForms = [
        "ます" => [
            "く" => "きます",
            "ぐ" => "ぎます",
            "ぶ" => "びます",
            "む" => "みます",
            "つ" => "ちます",
            "う" => "います",
            "ぬ" => "にます",
            "す" => "します",
            "る" => "ります",
            "2" => "ます",
            "くる" => "きます",
            "する" => "します"
        ],
        "て" => [
            "く" => "いて",
            "ぐ" => "いで",
            "ぶ" => "んで",
            "む" => "んで",
            "つ" => "って",
            "う" => "って",
            "ぬ" => "んで",
            "す" => "って",
            "る" => "って",
            "2" => "て",
            "くる" => "きって",
            "する" => "して"
        ]
    ];
    private static $START_HIRAGANA_CODEPOINT = 0x3041;
    private static $END_HIRAGANA_CODEPOINT = 0x3096;
    private static $START_KATAKANA_CODEPOINT =  0x30A1;
    private static $END_KATAKANA_CODEPOINT =  0x30FA;

    /**
     * Moves the text if is within a UTF-8 range to a new range.
     * It will allow to swap between hiragana and katakana
     * @param $text string text to be converted
     * @param $start int range start
     * @param $end int range end
     * @param $newStart int new range starting point
     * @return string a string with all the characters in the range are moved to a new range starting on newStart
     */
    private static function moveRange($text, $start, $end, $newStart) {
        $res = "";
        foreach (UTF8Utils::toCharArray($text) as $part) {
            $ord = UTF8Utils::ord($part);
            if (($ord >= $start) && ($ord <= $end)) {
                //is in range
                $index = $ord - $start;
                $res = $res.UTF8Utils::chr($newStart + $index);
            } else {
                $res = $res.$part;
            }
        }
        return $res;
    }

    /**
     * Calculates a verb form from the given dict form
     * @param $verbGroup
     * @param $dictForm string dictionary from in kana
     * @param $form string て or ます
     * @return null|string
     */
    public static function verbForm($verbGroup, $dictForm, $form) {
        $verbForm = self::$verbForms[$form];
        if (!isset($verbForm)) {
            return null;
        }
        switch ($verbGroup)
        {
            case 1:
                $end = mb_substr($dictForm, -1);
                $root = mb_substr($dictForm, 0, -1);
                break;
            case 2:
                $end = "2";
                $root = mb_substr($dictForm, 0, -1);
                break;
            case 3:
            default:
                if (($dictForm != "くる") && ($dictForm != "する")) {
                    $end = mb_substr($dictForm, -2);
                    $root = mb_substr($dictForm, 0, -2);
                }
                else  {
                    $end = $dictForm;
                    $root = "";
                }
                break;
        }
        return isset($verbForm[$end]) ? $root.$verbForm[$end] : '';
    }

    /**
     * Check if a character is a kanji
     * @param $chr
     * @return bool
     */
    public static function isKanji($chr) {
        $unicodeNumber = UTF8Utils::ord($chr);
        return (($unicodeNumber >= 19968) && ($unicodeNumber <= 40959));
    }

    /**
     * Counts the number of kanjis in a string
     * @param $str
     * @return integer
     */
    public static function countKanji($str) {
        return count(self::getKanji($str));
    }

    /**
     * Returns all the kanji found in the string
     * @param $str
     * @return array each kanji found mapped to its position in the string
     */
    public static function getKanji($str) {
        $kanjis = [];
        foreach (UTF8Utils::toCharArray($str) as $pos => $chr) {
            if (self::isKanji($chr)) {
                $kanjis[$chr] = $pos;
            }
        }
        return $kanjis;
    }

    /**
     * Converts a kana into its katakana, regardless of the original kana
     * @param $kana
     * @return string
     */
    public static function toKatakana($kana) {
        return self::moveRange(
            $kana,
            self::$START_HIRAGANA_CODEPOINT,
            self::$END_HIRAGANA_CODEPOINT,
            self::$START_KATAKANA_CODEPOINT
        );
    }
    /**
     * Converts a kana into its hiragana, regardless of the original kana
     * @param $kana
     * @return string
     */
    public static function toHiragana($kana) {
        return self::moveRange(
            $kana,
            self::$START_KATAKANA_CODEPOINT,
            self::$END_KATAKANA_CODEPOINT,
            self::$START_HIRAGANA_CODEPOINT
        );
    }

}
?>