<?php

/**
 * SluggableBehavior
 *
 * @uses CActiveRecordBehavior
 * @package
 * @version $id$
 * @copyright 2011 mintao GmbH & Co. KG
 * @author Florian Fackler <florian.fackler@mintao.com>
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class SlugBehavior extends CActiveRecordBehavior {
    /**
     * @var array Column name(s) to build a slug
     */
    public $columns = array();

    /**
     * Wether the slug should be unique or not.
     * If set to true, a number is added
     *
     * @var bool
     */
    public $unique = true;

    /**
     * Update the slug every time the row is updated?
     *
     * @var bool $update
     */
    public $update = true;

    /**
     * Name of table column to store the slug in
     *
     * @var string $slugColumn
     */
    public $slugColumn = 'url';

    /**
     * Default columns to build slug if none given
     *
     * @var array Columns
     */
    protected $_defaultColumnsToCheck = array('name', 'title');
    protected static $_transliteration = array(
        '/ä|æ|ǽ/' => 'ae',
        '/ö|œ/' => 'oe',
        '/ü/' => 'ue',
        '/Ä/' => 'Ae',
        '/Ü/' => 'Ue',
        '/Ö/' => 'Oe',
        '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
        '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
        '/ç|ć|ĉ|ċ|č/' => 'c',
        '/Ð|Ď|Đ/' => 'D',
        '/ð|ď|đ/' => 'd',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
        '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
        '/ĝ|ğ|ġ|ģ/' => 'g',
        '/Ĥ|Ħ/' => 'H',
        '/ĥ|ħ/' => 'h',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
        '/Ĵ/' => 'J',
        '/ĵ/' => 'j',
        '/Ķ/' => 'K',
        '/ķ/' => 'k',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
        '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
        '/Ñ|Ń|Ņ|Ň/' => 'N',
        '/ñ|ń|ņ|ň|ŉ/' => 'n',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
        '/Ŕ|Ŗ|Ř/' => 'R',
        '/ŕ|ŗ|ř/' => 'r',
        '/Ś|Ŝ|Ş|Š/' => 'S',
        '/ś|ŝ|ş|š|ſ/' => 's',
        '/Ţ|Ť|Ŧ/' => 'T',
        '/ţ|ť|ŧ/' => 't',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
        '/Ý|Ÿ|Ŷ/' => 'Y',
        '/ý|ÿ|ŷ/' => 'y',
        '/Ŵ/' => 'W',
        '/ŵ/' => 'w',
        '/Ź|Ż|Ž/' => 'Z',
        '/ź|ż|ž/' => 'z',
        '/Æ|Ǽ/' => 'AE',
        '/ß/' => 'ss',
        '/Ĳ/' => 'IJ',
        '/ĳ/' => 'ij',
        '/Œ/' => 'OE',
        '/ƒ/' => 'f'
    );

    /**
     * beforeSave
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function beforeSave($event) {
// Slug already created and no updated needed
        if (true !== $this->update && !empty($this->getOwner()->{$this->slugColumn})) {
            Yii::trace(
                    'Slug found - no update needed.', __CLASS__ . '::' . __FUNCTION__
            );
            return parent::beforeSave($event);
        }

        if (!is_array($this->columns)) {
            Yii::trace(
                    'Columns are not defined as array', __CLASS__ . '::' . __FUNCTION__
            );
            throw new CException('Columns have to be in array format.');
        }

        $availableColumns = array_keys(
                $this->getOwner()->tableSchema->columns
        );

// Try to guess the right columns
        if (0 === count($this->columns)) {
            $this->columns = array_intersect(
                    $this->_defaultColumnsToCheck, $availableColumns
            );
        } else {
// Unknown columns on board?
            foreach ($this->columns as $col) {
                if (!in_array($col, $availableColumns)) {
                    if (false !== strpos($col, '.')) {
                        Yii::trace(
                                'Dependencies to related models found', __CLASS__
                        );
                        list($model, $attribute) = explode('.', $col);
                        if ($this->getOwner()->$model) {
                            $externalColumns = array_keys(
                                    $this->getOwner()->$model->tableSchema->columns
                            );
                            if (!in_array($attribute, $externalColumns)) {
                                throw new CException(
                                        "Model $model does not haz $attribute"
                                );
                            }
                        }
                    } else {
                        throw new CException(
                                'Unable to build slug, column ' . $col . ' not found.'
                        );
                    }
                }
            }
        }

// No columns to build a slug?
        if (0 === count($this->columns)) {
            throw new CException(
                    'You must define "columns" to your sluggable behavior.'
            );
        }

// Fetch values
        $values = array();
        foreach ($this->columns as $col) {
            if (false !== strpos($col, '.')) {
                list($model, $attribute) = explode('.', $col);
                if ($this->getOwner()->$model) {
                    $values[] = $this->getOwner()->$model->$attribute;
                }
            } else {
                $values[] = $this->getOwner()->$col;
            }
        }

// First version of slug
        $slug = $checkslug = $this->getSlug(implode('-', $values));
        if(empty($slug))
            $slug = $checkslug = time();

// Check if slug has to be unique
        if (false === $this->unique ||
                (!$this->getOwner()->getIsNewRecord() && $slug === $this->getOwner()->{$this->slugColumn})
        ) {
            Yii::trace('Non unique slug or slug already set', __CLASS__);
            $this->getOwner()->{$this->slugColumn} = $slug;
        } else {
            $counter = 0;
            while ($this->getOwner()->resetScope()
                    ->findByAttributes(array($this->slugColumn => $checkslug))
            ) {
                Yii::trace("$checkslug found, iterating", __CLASS__);
                $checkslug = sprintf('%s-%d', $slug, ++$counter);
            }
            $this->getOwner()->{$this->slugColumn} = $counter > 0 ? $checkslug : $slug;
        }
        return parent::beforeSave($event);
    }

    /**
     * Returns a string with all spaces converted to underscores (by default), accented
     * characters converted to non-accented characters, and non word characters removed.
     *
     * @param string $string the string you want to slug
     * @param string $replacement will replace keys in map
     * @return string
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::slug
     */
    protected function getSlug($string, $replacement = '-') {
        $string = strip_tags($string);
// Preserve escaped octets.
        $string = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $string);
// Remove percent signs that are not part of an octet.
        $string = str_replace('%', '', $string);
// Restore octets.
        $string = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $string);

        $string = $this->removeAccents($string);
        if ($this->isUTF8($string)) {
            if (function_exists('mb_strtolower')) {
                $string = mb_strtolower($string, 'UTF-8');
            }
            $string = $this->utf8_uri_encode($string, 200);
            $string = urldecode($string);
        }else
            $string = strtolower($string);


        $quotedReplacement = preg_quote($replacement, '/');

        $merge = array(
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );

        $map = self::$_transliteration + $merge;
        $slug = preg_replace(array_keys($map), array_values($map), $string);
        return $this->removeStopWords($slug);
    }

    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *       has Bytes Sequences with a maximum length of 4.
     *
     * @author bmorel at ssi dot fr (modified)
     *
     * @param string $str The string to be checked
     * @return bool True if $str fits a UTF-8 model, false otherwise.
     */
    private function isUTF8($str) {
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80)
                $n = 0;# 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0)
                $n = 1;# 110bbbbb
            elseif (($c & 0xF0) == 0xE0)
                $n = 2;# 1110bbbb
            elseif (($c & 0xF8) == 0xF0)
                $n = 3;# 11110bbb
            elseif (($c & 0xFC) == 0xF8)
                $n = 4;# 111110bb
            elseif (($c & 0xFE) == 0xFC)
                $n = 5;# 1111110b
            else
                return false;# Does not match any model
            for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Encode the Unicode values to be used in the URI.
     *
     * @param string $utf8_string
     * @param int $length Max length of the string
     * @return string String with Unicode encoded for URI.
     */
    private function utf8_uri_encode($utf8_string, $length = 0) {
        $unicode = '';
        $values = array();
        $num_octets = 1;
        $unicode_length = 0;

        $string_length = strlen($utf8_string);
        for ($i = 0; $i < $string_length; $i++) {

            $value = ord($utf8_string[$i]);

            if ($value < 128) {
                if ($length && ( $unicode_length >= $length ))
                    break;
                $unicode .= chr($value);
                $unicode_length++;
            } else {
                if (count($values) == 0)
                    $num_octets = ( $value < 224 ) ? 2 : 3;

                $values[] = $value;

                if ($length && ( $unicode_length + ($num_octets * 3) ) > $length)
                    break;
                if (count($values) == $num_octets) {
                    if ($num_octets == 3) {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                        $unicode_length += 9;
                    } else {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                        $unicode_length += 6;
                    }

                    $values = array();
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    private function removeAccents($string) {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;

        if ($this->isUTF8($string)) {
            $chars = array(
// Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
                // Euro Sign
                chr(226) . chr(130) . chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194) . chr(163) => '');

            $string = strtr($string, $chars);
        } else {
// Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                    . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                    . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                    . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                    . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                    . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                    . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                    . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                    . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                    . chr(252) . chr(253) . chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    function removeStopWords($slug) {
        $slug = explode('-', $slug);
        foreach ($slug as $key => $word) {
            //stop words list separated for commas
            $stopWords = "a,able,about,above,abroad,according,accordingly,across,actually,adj,after,afterwards,again,against,ago,ahead,ain't,all,allow,allows,almost,alone,along,alongside,already,also,although,always,am,amid,amidst,among,amongst,an,and,another,any,anybody,anyhow,anyone,anything,anyway,anyways,anywhere,apart,appear,appreciate,appropriate,are,aren't,around,as,a's,aside,ask,asking,associated,at,available,away,awfully,b,back,backward,backwards,be,became,because,become,becomes,becoming,been,before,beforehand,begin,behind,being,believe,below,beside,besides,best,better,between,beyond,both,brief,but,by,c,came,can,cannot,cant,can't,caption,cause,causes,certain,certainly,changes,clearly,c'mon,co,co.,com,come,comes,concerning,consequently,consider,considering,contain,containing,contains,corresponding,could,couldn't,course,c's,currently,d,dare,daren't,definitely,described,despite,did,didn't,different,directly,do,does,doesn't,doing,done,don't,down,downwards,during,e,each,edu,eg,eight,eighty,either,else,elsewhere,end,ending,enough,entirely,especially,et,etc,even,ever,evermore,every,everybody,everyone,everything,everywhere,ex,exactly,example,except,f,fairly,far,farther,few,fewer,fifth,first,five,followed,following,follows,for,forever,former,formerly,forth,forward,found,four,from,further,furthermore,g,get,gets,getting,given,gives,go,goes,going,gone,got,gotten,greetings,h,had,hadn't,half,happens,hardly,has,hasn't,have,haven't,having,he,he'd,he'll,hello,help,hence,her,here,hereafter,hereby,herein,here's,hereupon,hers,herself,he's,hi,him,himself,his,hither,hopefully,how,howbeit,however,hundred,i,i'd,ie,if,ignored,i'll,i'm,immediate,in,inasmuch,inc,inc.,indeed,indicate,indicated,indicates,inner,inside,insofar,instead,into,inward,is,isn't,it,it'd,it'll,its,it's,itself,i've,j,just,k,keep,keeps,kept,know,known,knows,l,last,lately,later,latter,latterly,least,less,lest,let,let's,like,liked,likely,likewise,little,look,looking,looks,low,lower,ltd,m,made,mainly,make,makes,many,may,maybe,mayn't,me,mean,meantime,meanwhile,merely,might,mightn't,mine,minus,miss,more,moreover,most,mostly,mr,mrs,much,must,mustn't,my,myself,n,name,namely,nd,near,nearly,necessary,need,needn't,needs,neither,never,neverf,neverless,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,no-one,nor,normally,not,nothing,notwithstanding,novel,now,nowhere,o,obviously,of,off,often,oh,ok,okay,old,on,once,one,ones,one's,only,onto,opposite,or,other,others,otherwise,ought,oughtn't,our,ours,ourselves,out,outside,over,overall,own,p,particular,particularly,past,per,perhaps,placed,please,plus,possible,presumably,probably,provided,provides,q,que,quite,qv,r,rather,rd,re,really,reasonably,recent,recently,regarding,regardless,regards,relatively,respectively,right,round,s,said,same,saw,say,saying,says,second,secondly,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sensible,sent,serious,seriously,seven,several,shall,shan't,she,she'd,she'll,she's,should,shouldn't,since,six,so,some,somebody,someday,somehow,someone,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specified,specify,specifying,still,sub,such,sup,sure,t,take,taken,taking,tell,tends,th,than,thank,thanks,thanx,that,that'll,thats,that's,that've,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,there'd,therefore,therein,there'll,there're,theres,there's,thereupon,there've,these,they,they'd,they'll,they're,they've,thing,things,think,third,thirty,this,thorough,thoroughly,those,though,three,through,throughout,thru,thus,till,to,together,too,took,toward,towards,tried,tries,truly,try,trying,t's,twice,two,u,un,under,underneath,undoing,unfortunately,unless,unlike,unlikely,until,unto,up,upon,upwards,us,use,used,useful,uses,using,usually,v,value,various,versus,very,via,viz,vs,w,want,wants,was,wasn't,way,we,we'd,welcome,well,we'll,went,were,we're,weren't,we've,what,whatever,what'll,what's,what've,when,whence,whenever,where,whereafter,whereas,whereby,wherein,where's,whereupon,wherever,whether,which,whichever,while,whilst,whither,who,who'd,whoever,whole,who'll,whom,whomever,who's,whose,why,will,willing,wish,with,within,without,wonder,won't,would,wouldn't,x,y,yes,yet,you,you'd,you'll,your,you're,yours,yourself,yourselves,you've,z,zero,قادر,درحدود,فوق,خارجازکشور,مطابق,برایناساس,درسراسر,درحقیقت,عاج,پساز,بعدازآن,دوباره,دربرابر,پیش,پیش,نیست,تمام,اجازهمیدهد,اجازهمیدهدتا,تقریبا,تنها,همراه,درکنار,قبلا,همچنین,هرچند,همیشه,هستم,درمیان,و,دیگر,هر,کسی,بهرحال,هرکس,هرچیزی,بههرحال,بههرحال,هرکجا,جدا,بهنظرمیرسد,قدردانی,مناسب,هستند,نیستند,دراطراف,مانند,است,کنار,درخواست,خواستار,همراه,در,دردسترس,دور,بدجور,ب,بهعقب,بهعقب,بهعقب,باشد,شد,زیرا,شدن,میشود,تبدیلشدنبه,بوده,قبلاز,پیش,شروع,پشتسر,بودن,باور,درزیر,درکنار,درکنار,بهترین,بهتر,میان,خارجاز,هردو,مختصر,اما,توسط,ج,آمد,میتواند,نمیتواند,نمیتونم,نمیتواند,عنوان,علت,علل,معین,مطمئنا,تغییرات,بهوضوح,بیا,همکاری,شرکت,آمدهاست,میآید,درباره,درنتیجه,درنظر,باتوجهبه,شامل,حاوی,شامل,مربوطبه,میتواند,نمیتوانست,دوره,ج,درحالحاضر,د,شهامت,قطعا,شرحدادهشده,باوجود,انجامداد,نمی,مختلف,مستقیما,انجام,میکند,نمیکند,انجام,انجامشده,کارراانجامندهید,پایین,روبهپایین,درطی,الکترونیک,هر,بهعنوانمثال,هشتاد,هردو,دیگر,درجایدیگر,پایان,پایان,کافی,کاملا,بهخصوص,وهمکاران,وغیره,حتی,همیشه,همیشه,هر,هرکس,هرکس,همهچیز,درهمهجا,سابق,کاملا,مثال,جز,منصفانه,بسیار,دورتر,معدود,کمتر,پنجم,اول,پنج,بهدنبال,پیروی,شرحزیراست,برای,برایهمیشه,سابق,قبلا,جلو,بهجلو,یافت,چهار,از,بیشتر,بعلاوه,گرمدر,دریافتکنید,میشود,گرفتن,داده,میدهد,رفتن,میرود,رفتن,رفته,کردم,وبعدفورارفتواز,سلام,ساعت,بود,نداشت,نیم,اتفاقمیافتد,بهسختی,است,نه,داشتهباشد,ندارد,داشتن,او,اومیخواهم,او,سلام,کمک,ازاینرو,او,اینجا,آخرت,بدینوسیله,دراینجا,دراینجا,ازاینرو,مالانزن,خودشرا,او,سلام,اورا,خود,خودرا,اینجا,امیدوارم,چگونه,هرچند,اما,صد,من,منمیخواهم,بهعنوانمثال,اگر,نادیدهگرفتهمیشوند,من,من,فوری,به,اذا,وارز,وارز.,درواقع,نشاندادن,نشانداد,نشانمیدهد,داخلی,داخل,تاآنجا,درعوض,به,درون,است,نیست,آن,آنرامیخواهم,آنرامیخواهیم,آن,آن,خود,من,د,تنها,ک,نگهداشتن,نگهمیدارد,نگهداشته,دانستن,شناختهشده,میداند,ل,آخر,اخیرا,بعد,آخر,latterly,کمترین,کمتر,مبادا,اجازه,بیایید,مانند,دوست,احتمالا,نیز,کوچک,نگاه,بهدنبال,بهنظرمیرسد,پایین,کاهش,بامسئولیتمحدود,متر,ساخته,بهطورعمده,ساخت,میسازد,بسیاری,ممکناست,شاید,بهمن,متوسط,ضمنا,درضمن,تنها,ممکناست,ممکننیست,مالمن,منهای,ازدست,بیشتر,علاوهبراین,بیشترین,غالبا,آقای,خانم,بسیار,باید,نباید,من,خودم,نفر,نام,یعنی,دومین,نزدیک,تقریبا,لازم,نیاز,لازمنیست,نیاز,هیچیک,هرگز,بااینوجود,جدید,بعد,نهنفر,نود,هیچ,هیچکس,غیر,هیچیک,بااینحال,هیچکس,هیچکس,نه,بهطورمعمول,نه,هیچ,باوجود,رمان,اکنون,هیچجا,درجه,بدیهیاستکه,از,خاموش,غالبا,اوه,خوب,خوب,قدیمی,بر,یکبار,یک,آنهاییکه,یکنیست,تنها,برروی,مقابل,یا,دیگر,دیگران,وگرنه,باید,شایستهنیست,ما,خودمان,خودمان,خارج,خارجاز,روی,بهطورکلی,خود,ص,خاص,بهخصوص,گذشته,درهر,شاید,قرارداده,لطفا,بعلاوه,ممکن,احتمالا,شاید,ارائه,فراهممیکند,درخواست,رای,کاملا,بلکه,سوم,دوباره,واقعا,منطقی,اخیر,تازه,درمورد,بدوندرنظرگرفتن,دررابطه,نسبتا,بهترتیب,حق,دور,ها,گفت:,همان,دید,گفتن,گفته,میگوید:,دوم,درمرحلهدوم,دیدن,مشاهده,بهنظرمیرسد,بهنظرمیرسید,زیبایی,بهنظرمیرسد,دیدهمیشود,خود,خودشان,معقول,فرستاده,جدی,بهطورجدی,هفت,چند,باید,نباید,او,اومیخواهم,اوتورابه,او,باید,نباید,پساز,شش,پس,برخیاز,کسی,روزی,بهنحوی,کسی,چیزی,گاهیاوقات,گاهی,قدری,درمکانی,بزودی,متاسف,مشخصشده,مشخصکردن,مشخص,هنوز,زیر,چنین,شامخوردن,مطمئن,تی,گرفتن,گرفتهشده,مصرف,گفتن,بهسمت,هفتم,نسبتبه,تشکر,باتشکر,ثبت,که,کهبهدنبال,بدین,که,شان,خودمانی,آنهارا,خودشان,سپس,پسازان,آنجا,بعدازآن,درنتیجه,وجودداردمیخواهم,بنابراین,درآن,آنجاخواهم,وجودداردهستیم,هست,وجوددارد,پسازان,وجودداردام,این,آنها,آنهامیخواهم,آنهاخواهیم,آنها,آنها,چیز,چیز,فکرمیکنم,سوم,سی,این,کامل,بطورکامل,آن,اگرچه,سه,ازطریق,سراسر,بواسطه,درنتیجه,تا,به,باهم,هم,درزمان,نسبتبه,نسبتبه,سعی,تلاشمیکند,صادقانه,امتحان,تلاش,تی,دوبرابر,دو,تو,سازمانمللمتحد,تحت,درزیر,لغو,متاسفانه,مگر,برخلاف,بعید,تا,جزء,بالا,بر,بطرفبالا,ما,استفاده,استفادهمیشود,مفید,بااستفادهاز,بااستفادهاز,معمولا,ارزش,مختلف,درمقابل,بسیار,ازطریق,مختصر,درمقابل,میخواهم,میخواهد,بود,بود,راه,ما,میبایستی,خوشآمد,خوب,خواهیم,رفت,بود,ما,نمیشد,ماام,چه,هرچه,چهخواهیم,چه,وقتیکه,ازکجا,هرزمانکه,جاییکه,درحالیکه,بهموجبآن,درجاییکه,کهدرآناست,کهبررویان,هرجاکه,چه,که,هرکدامکه,درحالیکه,درحالیکه,کجا,که,کهمیخواهم,هرکسیکه,تمام,کهسایهیتوروتویقلبمنگهمیدارم,چهکسی,بهرکسکه,چهکسی,که,چرا,اراده,مایل,آرزو,با,درداخل,بدون,تعجب,نخواهدشد,خواهدبود,نبایستی,بله,هنوز,شما,شمامیخواهم,نظرشما,خودرا,شما,مالشما,خودت,خودتان,شمادارید,صفر,قادر,حول,فوق,فيالخارج,وفقا,وفقالذلك,عبر,فيالواقع,صفة,بعد,بعدذلك,ثانية,ضد,منذ,قدما,لا,جميع,السماحلل,يسمح,تقريبا,وحده,علىطول,جنباإلىجنب,قد,أيضا,رغمأن,دائما,صباحا,وسط,وسط,بين,فيمابين,و,آخر,أي,أيشخص,علىأيةحال,أيشخص,أيشئ,علىأيحال,علىأيحال,فيأيمكان,بعيدا,تظهر,نقدر,مناسب,هي,لا,حول,كما,هو,جانبا,تطلب,يسأل,المرتبطة,في,متاح,بعيدا,علىنحوبغيض,ب,ظهر,الىالوراء,الىالوراء,أنتكون,وأصبح,لأن,أصبح,يصبح,أنتصبح,كان,قبل,سلفا,تبدأ,وراء,يجري,اعتقد,أقلمن,بجانب,بالإضافةإلى,أفضل,أفضل,بين,وراء,علىحدسواء,موجز,لكن,منقبل,ج,جاء,يمكن,لايمكن,غيرقادرعلى,لايمكن,شرح,سبب,الأسباب,معين,بالتأكيد,التغييرات,بوضوح,هيا,شارك,المشترك.,كوم,جاء,ويأتي,حول,وبناءعلىذلك,نظر,النظرفي,تحتويعلى,تحتويعلى,يحتوي,المقابلة,ويمكن,لميستطع,مسار,جفي,حاليا,د,يجرؤ,daren't,قطعا,ووصف,علىالرغممن,فعل,لم,مختلف,مباشرة,فعل,لا,لا,فعل,فعل,لا,إلىأسفل,نزولا,خلال,ه,كل,ايدو,علىسبيلالمثال,ثمانية,ثمانون,إما,آخر,فيمكانآخر,نهاية,إنهاء,كاف,تماما,خاصة,وآخرون,الخ,حتى,أبدا,إلىالأبد,كل,الجميع,كلشخص,كلشيء,فيكلمكان,السابقين,بالضبط,مثال,ماعدا,و,بإنصاف,بعيدا,أبعد,قليل,أقل,خامس,الأول,خمسة,يتبع,بعد,يتبع,لل,إلىالأبد,سابق,سابقا,عليها,إلىالأمام,وجدت,أربعة,من,مزيد,علاوةعلىذلك,ز,الحصولعلى,يحصل,الحصولعلى,نظرا,يعطي,ذهاب,وغني,الذهاب,ذهب,حصلتعلى,حصلتعلى,تحياتي,ح,وكان,لم,نصف,يحدث,بالكاد,لديه,لم,لديك,لم,وجود,هو,عنيدا,وقالانهسوف,مرحبا,مساعدة,ومنهنا,لها,هنا,الآخرة,بموجبهذهالوثيقة,هنا,اليك,لها,نفسها,انه,مرحبا,وسلم,نفسه,له,ههنا,أمل,كيف,لكن,مائة,أنا,فمااستقاموالكمفاستقيموا,أي,إذا,تجاهل,وسوفأكون,أنا,فوري,في,نظرا,المؤتمرالوطنيالعراقي,المؤتمرالوطنيالعراقي.,فيالواقع,تشير,وأشار,يشيرإلى,داخلي,فيالداخل,بقدرما,بدلامن,إلى,نحوالداخل,هو,لا,هذا,انهاتريدان,انهاسوف,لها,انها,نفسها,لدي,ي,فقط,ك,إبقاء,يحتفظ,أبقى,أعرف,معروف,يعلم,ل,آخر,مؤخرا,فيمابعد,الأخير,حديثا,علىالأقل,أقل,خشيةأن,اسمحوا,دعونا,مثل,أحببت,علىالأرجح,كذلك,القليل,بحث,أبحث,يبدو,منخفض,انخفاض,المحدودة,م,أدلى,أساسا,جعل,يجعل,كثير,قد,ربما,أنا,يعني,غضونذلك,فيهذهالأثناء,مجرد,ربما,قدلا,منجم,ناقص,افتقد,أكثر,علاوةعلىذلك,معظم,فيالغالب,السيد,السيدة,كثيرا,يجبأن,يجبألا,لي,نفسي,ن,اسم,أي,الثانية,قرب,تقريبا,ضروري,حاجة,ليسمنالضروري,الاحتياجات,لا,أبدا,معذلك,جديد,التالي,تسعة,تسعون,لا,لاأحد,غير,لاشيء,معذلك,لاأحد,لاأحد,ولا,عادة,ليس,لاشيء,علىالرغممن,رواية,الآن,لامكان,س,بوضوح,من,قبالة,كثيراما,يا,موافق,حسنا,قديم,في,مرة,واحد,منها,المرء,فقط,على,معاكس,أو,آخر,آخرون,وإلا,يجب,لنا,لنا,أنفسنا,خارج,خارج,خلال,شامل,الخاصة,ف,خاص,خاصة,الماضي,لكل,ربما,وضعت,منفضلك,زائد,ممكن,يفترض,ربما,المقدمة,ويوفر,س,كيو,تماما,ص,بالأحرى,الثالثة,إعادة,حقا,معقول,الأخيرة,مؤخرا,فيمايتعلق,بغضالنظر,التحيات,نسبيا,علىالتوالي,حق,جولة,ق,قال,نفسه,رأى,قول,قول,يقول,ثان,ثانيا,انظر,رؤية,يبدو,وبدا,يبدو,يبدو,ينظر,النفس,الأنفس,معقول,أرسلت,خطير,بجدية,سبعة,عدة,يتعينعلى,لايجوز,هي,وقالتانهاتريد,وقالتانهاسوف,انها,وينبغي,لاينبغيأن,منذ,ستة,هكذا,بعض,شخصما,يوماما,بطريقةأوبأخرى,شخصما,شيء,فيوقتما,أحيانا,قليلا,فيمكانما,قريبا,آسف,محدد,تحديد,تحديد,لايزال,فرعية,مثل,سوب,بالتأكيد,تي,أخذ,اتخذت,معالأخذ,اقول,يميل,ال,من,شكر,شكرا,أن,التيسوف,لthats,هذاهو,و,من,لهم,منهم,أنفسهم,ثم,منثم,هناك,بعدذلك,وبالتالي,كنتهناك,ولذلك,فيها,هناكوسوف,كنتهناك,ثيريس,هناك,عندذلك,هناكقمت,هؤلاء,هم,وهم(الايرانيون),وسوفهم,انهم,أنهمأبلوا,شيء,الأشياء,اعتقد,ثالث,ثلاثون,هذا,شامل,تماما,هؤلاء,رغمأن,ثلاثة,منخلال,طوال,منخلال,وهكذا,حتى,إلى,معا,أيضا,استغرق,نحو,نحو,حاول,يحاول,حقا,محاولة,يحاول,تيفي,مرتين,اثنان,ش,الأممالمتحدة,تحت,تحت,التراجع,لسوءالحظ,مالم,علىعكس,منغيرالمحتمل,حتى,حتى,حتى,على,إلىأعلى,لنا,استخدم,يستخدم,مفيد,يستخدم,باستخدام,عادة,قيمة,مختلف,مقابل,جدا,بواسطة,بمعنى,مباراة,ث,تريد,يريد,وكان,لميكن,طريق,نحن,عليناأننبادر,ترحيب,جيد,سنقوم,ذهب,وكانت,نحن,لمتكن,قمنا,ما,أياكان,وسوفما,ماهو,what've,عندما,منأين,كلما,حيث,whereafter,فيحين,حيث,حيث,أين,عندئذ,أينما,سواء,الذي,أيهما,فيحين,فيحين,الىأين,الذي,الذييهمني,أياكان,كامل,وسوفالذي,الذي,أياكان,منهو,الذي,لماذا,سوف,مستعد,رغبة,مع,ضمن,بدون,عجب,ولن,سوف,لن,س,ذ,نعم,حتىالآن,أنت,كنت,وسوفتقوم,لكم,كنت,لك,نفسك,أنفسكم,كنتقد,ض,صفر";
            $words = explode(',', $stopWords);
            foreach ($words as $index => $wordfalse) {
                if ($word == $wordfalse) {
                    unset($slug[$key]);
                }
            }
        }
        return implode('-', $slug);
    }

}

