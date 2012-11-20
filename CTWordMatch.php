<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ramin
 * Date: 7/23/12
 * Time: 1:45 AM
 * To change this template use File | Settings | File Templates.
 */
class CTWordMatch
{

    /**
     * Google Search for PDA (because it's smaller)
     *
     * @var string
     */
    private $_url = 'http://google.com/pda?q=%s&hl=%s';

    /**
     * Use Google to find out if the entered query is correctly spelled
     *
     * @param string $query
     * @return mixed
     */
    public static function check($query) {
        // build url
        $url = sprintf('http://google.com/pda?q=%s&hl=%s', urlencode($query), Yii::app()->language);

        // store html output
        $source = file_get_contents($url);

        // strip other html data
        preg_match("'<b><i>(.*?)</i></b></a>'si", $source, $match);

        return (isset($match[0])) ? strip_tags($match[0]) : FALSE;

    }

}
