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
 * CTUrlManager class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */
class CTUrlManager extends CUrlManager {

    public $currentDomain;
    public $urlTables;

    
    /**
     * Build the rules from the DB
     */
    /*
    protected function processRules() {

        if (($urlRules = Yii::app()->cache->get('CTUrlRules')) === false) {

            foreach ($this->urlTables as $table) {
                $sql = 'SELECT `url` FROM `' . $table . '` WHERE 1;';
                $rules = Yii::app()->db->cache(10000)->createCommand($sql)->queryAll();
                $_more = array();
                foreach ($rules as $rule) {
                    $_more['http://' . $this->currentDomain . '/' . $table .'/<alias:(' . $rule['url'] . ')>'] = array($table . '/show/index');
                }
            }

            $this->rules = array(
                '<language:(en|fa|ar|zh)>/<_m:([a-zA-z0-9-]+)>/<_c:([a-zA-z0-9-]+)>/<_a:([a-zA-z0-9-]+)>//*' => '<_m>/<_c>/<_a>/',
                '<language:(en|fa|ar|zh)>/<_m:([a-zA-z0-9-]+)>/<_c:([a-zA-z0-9-]+)>/<_a:([a-zA-z0-9-]+)>' => '<_m>/<_c>/<_a>',
                '<language:(en|fa|ar|zh)>/<_c:([a-zA-z0-9-]+)>/<_a:([a-zA-z0-9-]+)>//*' => 'site/<_c>/<_a>/',
                '<language:(en|fa|ar|zh)>/<_c:([a-zA-z0-9-]+)>/<_a:([a-zA-z0-9-]+)>' => 'site/<_c>/<_a>',
                '<language:(en|fa|ar|zh)>/<_c:([a-zA-z0-9-]+)>' => 'site/<_c>/index',
                '<language:(en|fa|ar|zh)>/' => 'site/home/index',
            );
            $urlRules = array_merge($_more, $this->rules);
            Yii::app()->cache->set('CTUrlRules', $urlRules);
        }

        $this->rules = $urlRules;

        // Run parent
        parent::processRules();
    }
    */

    /**
     *
     * @see CUrlManager 
     *
     * Constructs a URL.
     * @param string the controller and the action (e.g. article/read)
     * @param array list of GET parameters (name=>value). Both the name and value will be URL-encoded.
     * If the name is '#', the corresponding value will be treated as an anchor
     * and will be appended at the end of the URL. This anchor feature has been available since version 1.0.1.
     * @param string the token separating name-value pairs in the URL. Defaults to '&'.
     * @return string the constructed URL
     */
    public function createUrl($route, $params = array(), $ampersand = '&') {
        if (!isset($params['language'])) {
            if (Yii::app()->user->hasState('language'))
                Yii::app()->language = Yii::app()->user->getState('language');
            else if (isset(Yii::app()->request->cookies['language']))
                Yii::app()->language = Yii::app()->request->cookies['language']->value;
            //$params['language'] = substr(Yii::app()->language, 0, 2);
            $route = substr(Yii::app()->language, 0, 2).'/'.$route;
        } else {
            $params['language']=  substr($params['language'], 0,2);
        }
        return parent::createUrl($route, $params, $ampersand);
    }

    /**
     * Clear the url manager cache
     */
    public function clearCache() {
        Yii::app()->cache->delete('customurlrules');
    }

}

?>