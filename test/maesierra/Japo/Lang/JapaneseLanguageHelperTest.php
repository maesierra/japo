<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 18/09/2018
 * Time: 1:16
 */

namespace maesierra\Japo\Lang;


if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class JapaneseLanguageHelperTest extends \PHPUnit_Framework_TestCase {



    public function testToHiragana() {
        $this->assertEquals('', JapaneseLanguageHelper::toHiragana(''));
        $this->assertEquals('aa', JapaneseLanguageHelper::toHiragana('aa'));
        $this->assertEquals('かみ', JapaneseLanguageHelper::toHiragana('かみ'));
        $this->assertEquals('かみ', JapaneseLanguageHelper::toHiragana('カミ'));
        $this->assertEquals('aかaみ', JapaneseLanguageHelper::toHiragana('aかaみ'));
        $this->assertEquals('かみた', JapaneseLanguageHelper::toHiragana('カミた'));
    }

    public function testToKatakana() {
        $this->assertEquals('', JapaneseLanguageHelper::toKatakana(''));
        $this->assertEquals('aa', JapaneseLanguageHelper::toKatakana('aa'));
        $this->assertEquals('カミ', JapaneseLanguageHelper::toKatakana('かみ'));
        $this->assertEquals('カミ', JapaneseLanguageHelper::toKatakana('カミ'));
        $this->assertEquals('aカaミ', JapaneseLanguageHelper::toKatakana('aかaみ'));
        $this->assertEquals('カミタ', JapaneseLanguageHelper::toKatakana('カミた'));
    }

    public function testGetKanji() {
        $this->assertEquals([], JapaneseLanguageHelper::getKanji(''));
        $this->assertEquals([], JapaneseLanguageHelper::getKanji('aa'));
        $this->assertEquals(['漢'=> 1, '字' => 2, '新' => 3], JapaneseLanguageHelper::getKanji('a漢字新しい  カミタａ'));
    }

    public function testCountKanji() {
        $this->assertEquals(0, JapaneseLanguageHelper::countKanji(''));
        $this->assertEquals(0, JapaneseLanguageHelper::countKanji('aa'));
        $this->assertEquals(3, JapaneseLanguageHelper::countKanji('a漢字新しい  カミタａ'));
    }

    public function testIsKanji() {
        $this->assertFalse(JapaneseLanguageHelper::isKanji('a'));
        $this->assertTrue (JapaneseLanguageHelper::isKanji('漢'));
        $this->assertTrue (JapaneseLanguageHelper::isKanji('字'));
        $this->assertTrue (JapaneseLanguageHelper::isKanji('新'));
        $this->assertFalse(JapaneseLanguageHelper::isKanji('し'));
        $this->assertFalse(JapaneseLanguageHelper::isKanji('い'));
        $this->assertFalse(JapaneseLanguageHelper::isKanji(' '));
        $this->assertFalse(JapaneseLanguageHelper::isKanji('カ'));
        $this->assertFalse(JapaneseLanguageHelper::isKanji('ミ'));
        $this->assertFalse(JapaneseLanguageHelper::isKanji('タ'));
    }

    public function testVerbForm() {
        $this->assertEquals('あいます', JapaneseLanguageHelper::verbForm(1, 'あう', 'ます'));
        $this->assertEquals('会います', JapaneseLanguageHelper::verbForm(1, '会う', 'ます'));
        $this->assertEquals('とおります', JapaneseLanguageHelper::verbForm(1, 'とおる', 'ます'));
        $this->assertEquals('はたらきます', JapaneseLanguageHelper::verbForm(1, 'はたらく', 'ます'));
        $this->assertEquals('やすみます', JapaneseLanguageHelper::verbForm(1, 'やすむ', 'ます'));
        $this->assertEquals('およぎます', JapaneseLanguageHelper::verbForm(1, 'およぐ', 'ます'));
        $this->assertEquals('あそびます', JapaneseLanguageHelper::verbForm(1, 'あそぶ', 'ます'));
        $this->assertEquals('たちます', JapaneseLanguageHelper::verbForm(1, 'たつ', 'ます'));
        $this->assertEquals('しにます', JapaneseLanguageHelper::verbForm(1, 'しぬ', 'ます'));
        $this->assertEquals('おします', JapaneseLanguageHelper::verbForm(1, 'おす', 'ます'));
        $this->assertEquals('おきます', JapaneseLanguageHelper::verbForm(2, 'おきる', 'ます'));
        $this->assertEquals('ねます', JapaneseLanguageHelper::verbForm(2, 'ねる', 'ます'));
        $this->assertEquals('します', JapaneseLanguageHelper::verbForm(3, 'する', 'ます'));
        $this->assertEquals('きます', JapaneseLanguageHelper::verbForm(3, 'くる', 'ます'));
        $this->assertEquals('しゅっちょうします', JapaneseLanguageHelper::verbForm(3, 'しゅっちょうする', 'ます'));
        $this->assertEquals('コピーします', JapaneseLanguageHelper::verbForm(3, 'コピーする', 'ます'));

        $this->assertEquals('あって', JapaneseLanguageHelper::verbForm(1, 'あう', 'て'));
        $this->assertEquals('会って', JapaneseLanguageHelper::verbForm(1, '会う', 'て'));
        $this->assertEquals('とおって', JapaneseLanguageHelper::verbForm(1, 'とおる', 'て'));
        $this->assertEquals('はたらいて', JapaneseLanguageHelper::verbForm(1, 'はたらく', 'て'));
        $this->assertEquals('やすんで', JapaneseLanguageHelper::verbForm(1, 'やすむ', 'て'));
        $this->assertEquals('およいで', JapaneseLanguageHelper::verbForm(1, 'およぐ', 'て'));
        $this->assertEquals('あそんで', JapaneseLanguageHelper::verbForm(1, 'あそぶ', 'て'));
        $this->assertEquals('たって', JapaneseLanguageHelper::verbForm(1, 'たつ', 'て'));
        $this->assertEquals('しんで', JapaneseLanguageHelper::verbForm(1, 'しぬ', 'て'));
        $this->assertEquals('おって', JapaneseLanguageHelper::verbForm(1, 'おす', 'て'));
        $this->assertEquals('おきて', JapaneseLanguageHelper::verbForm(2, 'おきる', 'て'));
        $this->assertEquals('ねて', JapaneseLanguageHelper::verbForm(2, 'ねる', 'て'));
        $this->assertEquals('して', JapaneseLanguageHelper::verbForm(3, 'する', 'て'));
        $this->assertEquals('きって', JapaneseLanguageHelper::verbForm(3, 'くる', 'て'));
        $this->assertEquals('しゅっちょうして', JapaneseLanguageHelper::verbForm(3, 'しゅっちょうする', 'て'));
        $this->assertEquals('コピーして', JapaneseLanguageHelper::verbForm(3, 'コピーする', 'て'));

    }
}
