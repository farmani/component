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
 * CTWebUser class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */
class CTWebUser extends CWebUser {
    /**
     * @var object
     */
    private $_model = null;
    public $_firstLoginTry = true;

    public function init()
    {
        $this->identityCookie=Yii::app()->settings->get('system', 'cookieSettings');
        parent::init();
    }

    /**
     * This is here since there is a bug with cookies
     * that have been saved to a domain name such as
     * .domain.com so all subdomains can access it as well
     * @see http://code.google.com/p/yii/issues/detail?id=856
     */
    public function logout($destroySession = true) {
        if ($this->allowAutoLogin && isset($this->identityCookie['domain'])) {
            $cookies = Yii::app()->getRequest()->getCookies();

            if (null !== ($cookie = $cookies[$this->getStateKeyPrefix()])) {
                $originalCookie = new CHttpCookie($cookie->name, $cookie->value);
                $cookie->domain = $this->identityCookie['domain'];
                $cookies->remove($this->getStateKeyPrefix());
                $cookies->add($originalCookie->name, $originalCookie);
            }

            // Remove Roles
            $assignedRoles = Yii::app()->authManager->getRoles(Yii::app()->user->id);
            if (!empty($assignedRoles)) {
                $auth = Yii::app()->authManager;
                foreach ($assignedRoles as $n => $role) {
                    if ($auth->revoke($n, Yii::app()->user->id))
                        Yii::app()->authManager->save();
                }
            }
        }

        parent::logout($destroySession);
    }

    /**
     * @return string - User role
     */
    public function getRole() {
        if ($user = $this->getModel()) {
            return $user->role;
        }
    }

    /**
     * @return object - Members AR Object
     */
    private function getModel() {
        if (!$this->isGuest && $this->_model === null) {
            $this->_model = User::model()->findByPk($this->id, array('select' => 'role'));
        }
        return $this->_model;
    }

    /*
     * extra security
     */
    protected function beforeLogin($id, $states, $fromCookie) {
        parent::beforeLogin($id, $states, $fromCookie);
        if ($fromCookie) {
            //die('cookie');
            $user = Yii::app()->db->createCommand()
                ->from('user')
                ->where('id=:id AND security_key=:security_key', array(
                    ':id'=>$id,
                    ':security_key' => $states['securityKey']))
                ->queryRow();
            if ($user === null) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * @return array flash message keys array
     */
    public function getFlashKeys() {
        $counters = $this->getState(self::FLASH_COUNTERS);
        if (!is_array($counters))
            return array();
        return array_keys($counters);
    }

    public function getIsAdmin(){
        Yii::app()->user->getState('isAdmin');
    }

}