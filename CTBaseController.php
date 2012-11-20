<?php

if (!defined('YII_PATH'))
    exit('No direct script access allowed!');

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
 * CTBaseController class
 * Base controller for all controllers under this application
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */

class CTBaseController extends CController {
    /**
     * @var string the default layout for the controller view. Defaults to
     * '//layouts/column1',
     * meaning using a single column layout. See
     * 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column1';

    /**
     * @var array context menu items. This property will be assigned to {@link
     * CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array - array of {@link CBreadCrumbs} link
     */
    public $breadcrumbs = array();


    /**
     * Class constructor
     *
     */
    public function init() {
        /* Filter out garbage requests */
        $uri = Yii::app()->request->requestUri;
        if (strpos($uri, 'favicon') || strpos($uri, 'robots'))
            Yii::app()->end();

        /* Run init */
        parent::init();
    }

    public function __construct($id, $module = null) {
        parent::__construct($id, $module);
        // If there is a post-request, redirect the application to the provided url of
        // the selected language
        if (isset($_POST['language'])) {
            $lang = substr($_POST['language'],0,2);
            //$MultilangReturnUrl = $_POST[$lang];
            //$this->redirect($MultilangReturnUrl);
        }
        // Set the application language if provided by GET, session or cookie
        if (isset($_GET['language'])) {
            $language = Yii::app()->func->languageGenerator($_GET['language']);
            Yii::app()->language = $language;
            Yii::app()->user->setState('language', $language);
            $cookie = new CHttpCookie('language', $language);
            $cookie->expire = time() + (31536000);
            // (1 year)
            Yii::app()->request->cookies['language'] = $cookie;
        } else if (Yii::app()->user->hasState('language'))
            Yii::app()->language = Yii::app()->user->getState('language');
        else if (isset(Yii::app()->request->cookies['language']))
            Yii::app()->language = Yii::app()->request->cookies['language']->value;
    }

    public function createMultilanguageReturnUrl($lang = 'en') {
        if (count($_GET) > 0) {
            $arr = $_GET;
            $arr['language'] = $lang;
            if(isset($arr['url'])){
                $arr['returnRout'] = Yii::app()->language . '.' . $this->getModule()->id . '.' . $this->getId() . '.' . $this->getAction()->id . '.' . $arr['url'];
                unset($arr['url']);
                return $this->createUrl('/site/home/router', $arr);
            }
        } else
            $arr = array('language' => $lang);
        return $this->createUrl('', $arr);
        
    }

    /**
     * The filter method for 'rights' access filter.
     * This filter is a wrapper of {@link CAccessControlFilter}.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filterCTRights($filterChain) {
        $filter = new CTRightsFilter;
        $filter->allowedActions = $this->allowedActions();
        $filter->filter($filterChain);
    }

    /**
     * @return string the actions that are always allowed separated by commas.
     */
    public function allowedActions() {
        return '';
    }

    /**
     * Denies the access of the user.
     * @param string $message the message to display to the user.
     * This method may be invoked when access check fails.
     * @throws CHttpException when called unless login is required.
     */
    public function accessDenied($message = null) {
        if ($message === null)
            $message = Yii::t('core', 'You are not authorized to perform this action.');

        $user = Yii::app()->getUser();
        if ($user->isGuest === true)
            $user->loginRequired();
        else
            throw new CHttpException(403, $message);
    }

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array('CTRights',array(
            'application.filters.html.ECompressHtmlFilter',
            'gzip'    => false,
            'actions' => 'ALL'
        ),);
    }
}
