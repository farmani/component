<?php if (!defined('YII_PATH')) exit('No direct script access allowed!');

################################################################################
#
#       ,-----.                     ,--------.                ,--------. 
#      '  .--./,--.--.,--. ,--.,---.'--.  .--',---. ,--.  ,--.'--.  .--' 
#      |  |    |  .--' \  '  /| .-. |  |  |  | .-. : \  `'  /    |  |    
#      '  '--'\|  |     \   ' | '-' '  |  |  \   --. /  /.  \    |  |    
#       `-----'`--'   .-'  /  |  |-'   `--'   `----''--'  '--'   `--'    
#                     `---'   `--'                                       
# Copyright 2012 CrypTexT Security Framework based on Yii
# License: MIT
# Website: http://www.cryptext.org/
################################################################################

/**
 * CTFunctions class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */
class CTFunctions extends CApplicationComponent
{

    public function init() {

    }

    /**
     * Display an image based on the value (tick or cross image)
     */
    public function adminYesNoImage($value, $url = null, $imageOptions = array(), $linkOptions = array()) {

        $true = Yii::app()->themeManager->baseUrl . '/images/icons/tick_circle.png';
        $false = Yii::app()->themeManager->baseUrl . '/images/icons/cross_circle.png';

        $image = $value ? $true : $false;

        if ($url) {
            return CHtml::link(CHtml::image($image, '', $imageOptions), $url, $linkOptions);
        } else {
            return CHtml::image($image, '', $imageOptions);
        }
    }

    /**
     * load zend components and autoloader
     */
    public function loadZend() {
        Yii::import('ext.*');
        require_once 'Zend/Loader/Autoloader.php';
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));
        spl_autoload_register(array('YiiBase', 'autoload'));
    }

    /**
     * Display RSS Data
     */
    public function displayRss($array, $type = 'rss') {
        $this->loadZend();

        $feed = Zend_Feed::importArray($array, $type);

        $feed->send();
        exit;
    }

    /**
     * Download content as text
     */
    public function downloadAs($title, $name, $content, $type = 'text') {
        $types = array(
            'text' => 'text/plain',
            'pdf' => 'application/pdf',
            'word' => 'application/msword'
        );

        $exts = array(
            'text' => 'txt',
            'pdf' => 'pdf',
            'word' => 'doc'
        );

        // Load anything?
        if ($type == 'pdf') {
            $pdf = Yii::createComponent('application.extensions.tcpdf.ETcPdf', 'P', 'cm', 'A4', true, 'UTF-8');
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(Yii::app()->name);
            $pdf->SetTitle($title);
            $pdf->SetSubject($title);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->writeHTML($content, true, 0, true, 0);
            $pdf->Output($name . '.' . $exts[$type], "I");
        }


        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Pragma: no-cache');
        header("Content-Type: " . $types[$type] . "");
        header("Content-Disposition: attachment; filename=\"" . $name . '.' . $exts[$type] . "\";");
        header("Content-Length: " . mb_strlen($content));
        echo $content;
        exit;
    }

    /**
     * Convert bytes to human readable format
     *
     * @param integer bytes Size in bytes to convert
     * @return string
     */
    public function bytesToSize($bytes, $precision = 2) {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' KB';
        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' MB';
        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' GB';
        } elseif ($bytes >= $terabyte) {
            return round($bytes / $gigabyte, $precision) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * Make an SEO title for use in the URL
     *
     * @access    public
     * @param    string        Raw SEO title or text
     * @return    string        Cleaned up SEO title
     */
    static public function makeAlias($text) {
        if (!$text) {
            return '';
        }

        $text = str_replace(array('`', ' ', '+', '.', '?', '_'), '-', $text);

        /* Strip all HTML tags first */
        $text = strip_tags($text);

        /* Preserve %data */
        $text = preg_replace('#%([a-fA-F0-9][a-fA-F0-9])#', '-xx-$1-xx-', $text);
        $text = str_replace(array('%', '`'), '', $text);
        $text = preg_replace('#-xx-([a-fA-F0-9][a-fA-F0-9])-xx-#', '%$1', $text);

        /* Convert accented chars */
        $text = self::convertAccents($text);

        /* Convert it */
        if (self::isUTF8($text)) {
            if (function_exists('mb_strtolower')) {
                $text = mb_strtolower($text, 'UTF-8');
            }

            $text = self::utf8Encode($text, 500);
        }

        /* Finish off */
        $text = strtolower($text);

        if (strtolower(Yii::app()->charset) == 'utf-8') {
            $text = preg_replace('#&.+?;#', '', $text);
            $text = preg_replace('#[^%a-z0-9 _-]#', '', $text);
        } else {
            /* Remove &#xx; and &#xxx; but keep &#xxxx; */
            $text = preg_replace('/&#(\d){2,3};/', '', $text);
            $text = preg_replace('#[^%&\#;a-z0-9 _-]#', '', $text);
            $text = str_replace(array('&quot;', '&amp;'), '', $text);
        }

        $text = str_replace(array('`', ' ', '+', '.', '?', '_'), '-', $text);
        $text = preg_replace("#-{2,}#", '-', $text);
        $text = trim($text, '-');

        return ($text) ? $text : '-';
    }

    /**
     * Seems like UTF-8?
     * hmdker at gmail dot com {@link php.net/utf8_encode}
     *
     * @access    public
     * @param    string        Raw text
     * @return    boolean
     */
    static public function isUTF8($str) {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);

            if ($c > 128) {
                if (($c >= 254))
                    return false;
                elseif ($c >= 252)
                    $bits = 6;
                elseif ($c >= 248)
                    $bits = 5;
                elseif ($c >= 240)
                    $bits = 4;
                elseif ($c >= 224)
                    $bits = 3;
                elseif ($c >= 192)
                    $bits = 2;
                else
                    return false;

                if (($i + $bits) > $len)
                    return false;

                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191)
                        return false;
                    $bits--;
                }
            }
        }

        return true;
    }

    /**
     * Converts accented characters into their plain alphabetic counterparts
     *
     * @access    public
     * @param    string        Raw text
     * @return    string        Cleaned text
     */
    static public function convertAccents($string) {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if (self::isUTF8($string)) {
            $_chr = array(
                /* Latin-1 Supplement */
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
                /* Latin Extended-A */
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
                /* Euro Sign */
                chr(226) . chr(130) . chr(172) => 'E',
                /* GBP (Pound) Sign */
                chr(194) . chr(163) => '');

            $string = strtr($string, $_chr);
        } else {
            $_chr = array();
            $_dblChars = array();

            /* We assume ISO-8859-1 if not UTF-8 */
            $_chr['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(199) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(249) . chr(250) . chr(251)
                . chr(252) . chr(253) . chr(255) . chr(191) . chr(182) . chr(179) . chr(166)
                . chr(230) . chr(198) . chr(175) . chr(172) . chr(188)
                . chr(163) . chr(161) . chr(177);

            $_chr['out'] = "EfSZszYcYuAAAACEEEEIIIINOOOOUUUUYaaaaceeeeiiiinoooouuuuyyzslScCZZzLAa";

            $string = strtr($string, $_chr['in'], $_chr['out']);
            $_dblChars['in'] = array(chr(140), chr(156), chr(196), chr(197), chr(198), chr(208), chr(214), chr(216), chr(222), chr(223), chr(228), chr(229), chr(230), chr(240), chr(246), chr(248), chr(254));
            $_dblChars['out'] = array('Oe', 'oe', 'Ae', 'Aa', 'Ae', 'DH', 'Oe', 'Oe', 'TH', 'ss', 'ae', 'aa', 'ae', 'dh', 'oe', 'oe', 'th');
            $string = str_replace($_dblChars['in'], $_dblChars['out'], $string);
        }

        return $string;
    }

    /**
     * Manually utf8 encode to a specific length
     * Based on notes found at php.net
     *
     * @access    public
     * @param    string        Raw text
     * @param    int            Length
     * @return    string
     */
    static public function utf8Encode($string, $len = 0) {
        $_unicode = '';
        $_values = array();
        $_nOctets = 1;
        $_unicodeLength = 0;
        $stringLength = strlen($string);

        for ($i = 0; $i < $stringLength; $i++) {
            $value = ord($string[$i]);

            if ($value < 128) {
                if ($len && ($_unicodeLength >= $len)) {
                    break;
                }

                $_unicode .= chr($value);
                $_unicodeLength++;
            } else {
                if (count($_values) == 0) {
                    $_nOctets = ($value < 224) ? 2 : 3;
                }

                $_values[] = $value;

                if ($len && ($_unicodeLength + ($_nOctets * 3)) > $len) {
                    break;
                }

                if (count($_values) == $_nOctets) {
                    if ($_nOctets == 3) {
                        $_unicode .= '%' . dechex($_values[0]) . '%' . dechex($_values[1]) . '%' . dechex($_values[2]);
                        $_unicodeLength += 9;
                    } else {
                        $_unicode .= '%' . dechex($_values[0]) . '%' . dechex($_values[1]);
                        $_unicodeLength += 6;
                    }

                    $_values = array();
                    $_nOctets = 1;
                }
            }
        }

        return $_unicode;
    }

    /**
     * Returns the user real IP address.
     * @return string user IP address
     */
    public static function getUserHostAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }
        return $ip;
    }

    /**
     * Returns the hashed value of $data.
     * @return string hash string
     */
    public static function hash($data, $saltString = null, $algorithm = 'whirlpool') {
        $salt = is_null($saltString) ? uniqid(rand(), TRUE) : $saltString;

        $salt = substr(md5($salt), 0, 10);

        if (function_exists('hash') && in_array($algorithm, hash_algos())) {
            $hashed = hash($algorithm, $salt . $data);
        } else {
            $hashed = sha1($salt . $data);
        }
        return $hashed;
    }

    public static function languageGenerator($language) {
        switch ($language) {
            case 'fa':
                return 'fa_ir';
                break;
            case 'zh':
                return 'zh_cn';
                break;
            default:
                return $language;
                break;
        }
    }


    public static function strtolower($str)
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_strtolower'))
            return mb_strtolower($str, 'utf-8');
        return strtolower($str);
    }

    public static function strlen($str, $encoding = 'UTF-8')
    {
        if (is_array($str))
            return false;
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        if (function_exists('mb_strlen'))
            return mb_strlen($str, $encoding);
        return strlen($str);
    }

    public static function stripslashes($string)
    {
        if (_PS_MAGIC_QUOTES_GPC_)
            $string = stripslashes($string);
        return $string;
    }

    public static function strtoupper($str)
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_strtoupper'))
            return mb_strtoupper($str, 'utf-8');
        return strtoupper($str);
    }

    public static function substr($str, $start, $length = false, $encoding = 'utf-8')
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_substr'))
            return mb_substr($str, (int)($start), ($length === false ? self::strlen($str) : (int)($length)), $encoding);
        return substr($str, $start, ($length === false ? self::strlen($str) : (int)($length)));
    }

    public static function ucfirst($str)
    {
        return self::strtoupper(self::substr($str, 0, 1)).self::substr($str, 1);
    }

    /**
     * Replace all accented chars by their equivalent non accented chars.
     *
     * @param string $str
     * @return string
     */
    public static function replaceAccentedChars($str) {
        $str = preg_replace('/[\x{0105}\x{0104}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u', 'a', $str);
        $str = preg_replace('/[\x{00E7}\x{010D}\x{0107}\x{0106}]/u', 'c', $str);
        $str = preg_replace('/[\x{010F}]/u', 'd', $str);
        $str = preg_replace('/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{011B}\x{0119}\x{0118}]/u', 'e', $str);
        $str = preg_replace('/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}]/u', 'i', $str);
        $str = preg_replace('/[\x{0142}\x{0141}\x{013E}\x{013A}]/u', 'l', $str);
        $str = preg_replace('/[\x{00F1}\x{0148}]/u', 'n', $str);
        $str = preg_replace('/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{00D3}]/u', 'o', $str);
        $str = preg_replace('/[\x{0159}\x{0155}]/u', 'r', $str);
        $str = preg_replace('/[\x{015B}\x{015A}\x{0161}]/u', 's', $str);
        $str = preg_replace('/[\x{00DF}]/u', 'ss', $str);
        $str = preg_replace('/[\x{0165}]/u', 't', $str);
        $str = preg_replace('/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}]/u', 'u', $str);
        $str = preg_replace('/[\x{00FD}\x{00FF}]/u', 'y', $str);
        $str = preg_replace('/[\x{017C}\x{017A}\x{017B}\x{0179}\x{017E}]/u', 'z', $str);
        $str = preg_replace('/[\x{00E6}]/u', 'ae', $str);
        $str = preg_replace('/[\x{0153}]/u', 'oe', $str);
        return $str;
    }

    function removeStopCharacters($word) {
        $signChars = array('!','"','#','$','%','&','\'','(',')','*','+',',','-','.','/',':',';','<','=','>','?','@','[','\\',']','^','_','`','{','|','}','~');
        return str_replace($signChars, '', $word);
    }

    function generatePassword($length = 9, $strength = 0) {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2) {
            $vowels .= "AEUY";
        }
        if ($strength & 4) {
            $consonants .= '23456789';
        }
        if ($strength & 8) {
            $consonants .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    /* takes the input, scrubs bad characters */

    function generateSEOLink($input, $replace = '-', $remove_words = true, $words_array = array()) {

        //make it lowercase, remove punctuation, remove multiple/leading/ending spaces
        $return = trim(ereg_replace(' +', ' ', preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($input))));
        //remove words, if not helpful to seo
        //i like my defaults list in remove_words(), so I wont pass that array
        if ($remove_words) {
            $return = remove_words($return, $replace, $words_array);
        }
        //convert the spaces to whatever the user wants usually a dash or underscore..
        //...then return the value.
        return str_replace(' ', $replace, $return);
    }

    /* takes an input, scrubs unnecessary words */
    function remove_words($input, $replace, $words_array = array(), $unique_words = true) {
        //separate all words based on spaces
        $input_array = explode(' ', $input);
        ////create the return array
        $return = array();
        ////loops through words, remove bad words, keep good ones
        foreach ($input_array as $word) {
            //if it's a word we should add...
            if (!in_array($word, $words_array) && ($unique_words ? !in_array($word, $return) : true)) {
                $return[] = $word;
            }
        }
        //return good words separated by dashes
        return implode($replace, $return);
    }

    public static function purify($string,$html=false){
        $purifier = new CHtmlPurifier();
        if($html){
            $purifier->options = array(
                'HTML.Doctype' => 'HTML 4.01 Strict',
                'URI.AllowedSchemes' => array(
                    'http' => true,
                    'https' => true,
                ),
                'Attr.AllowedFrameTargets'=> array('_blank','_self','_parent','_top'),
                //'HTML.Allowed'=> 'p,a[href|target],b,i,br,em,h1,h2,h3,h4,h5,strong,ul,ol,li,code,pre,blockquote,img[src|alt|height|width],sub,sup',
                'HTML.Allowed'=> 'p,b,i,br,em,h2,h3,h4,h5,strong,ul,ol,li,code,pre,blockquote,sub,sup',
            );
        }
        return $purifier->purify($string);
    }

}