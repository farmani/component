<?php if (!defined('YII_PATH')) exit('No direct script access allowed!');

################################################################################
#
#       ,-----.                     ,--------.                ,--------. 
#      '  .--./,--.--.,--. ,--.,---.'--.  .--',---. ,--.  ,--.'--.  .--' 
#      |  |    |  .--' \  '  /| .-. |  |  |  | .-. : \  `'  /    |  |    
#      '  '--'\|  |     \   ' | '-' '  |  |  \   --. /  /.  \    |  |    
#       `-----'`--'   .-'  /  |  |-'   `--'   `----''--'  '--'   `--'    
#                     `---'   `--'                                       
# Copyright 2012 CrypTexT Security Framework based on Yii Framework
# License: MIT
# Website: http://www.cryptext.org/
################################################################################

/**
 * CTUserIdentity class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 * @package CrypText CT
 * @uses CTUserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks wether the provided
 * data can identity the user.
 */
class CTUserIdentity extends CUserIdentity {
    const ERROR_STATUS_PENDING=3;
    const ERROR_STATUS_LOCKED=4;
    /**
     * @var int unique member id
     */
    private $_id;
    private $_username;
    private $_email;
    private $_securityKey;

    /**
     * Authenticate a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    public function authenticate() {
        if(strpos($this->_username,'@'))
            $record = Yii::app()->db->createCommand()
                ->select('id, username, password, email, status, role')
                ->from('user')
                ->where('email=:email', array(':email'=>$this->username))
                ->queryRow();
        else
            $record = Yii::app()->db->createCommand()
                    ->select('id, username, password, email, status, role')
                    ->from('user')
                    ->where('username=:username', array(':username'=>$this->username))
                    ->queryRow();
        //CVarDumper::dump($record,10,true);
        //exit($this->password . ' ' . $record['email'] . ' ' . Yii::app()->func->hash($this->password, $record['email']));
        if ($record === false) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
            $this->errorMessage = Yii::t('app', 'Sorry, But we can\'t find a member with those login information.');
        } else if ($record['status'] === 'Pending') {
            $this->errorCode = self::ERROR_STATUS_PENDING;
            $this->errorMessage = Yii::t('app', 'Sorry, But your account does not active yet.');
        } else if ($record['status'] === 'Locked') {
            $this->errorCode = self::ERROR_STATUS_LOCKED;
            $this->errorMessage = Yii::t('app', 'Sorry, But your account has been locked by system administrator.');
        } else if (Yii::app()->func->hash($this->password, md5($record['username'])) !== $record['password']) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
            $this->errorMessage = Yii::t('app', 'Sorry, But the password did not match the one in our records.');
        } else {
            $this->_id = $record['id'];
            $this->_username = $record['username'];
            $this->_email = $record['email'];

            $auth = Yii::app()->authManager;
            if (!$auth->isAssigned($record['role'], $this->_id)) {
                if ($auth->assign($record['role'], $this->_id)) {
                    Yii::app()->authManager->save();
                }
            }

            // We add username to the state 
            $this->setState('username', $this->_username);
            $this->setState('email', $this->_email);
            $this->errorCode = self::ERROR_NONE;
            $this->_securityKey = md5(rand(1, 10000));
            ($record['role']==='SuperAdministrator')?$this->setState('isAdmin', true):$this->setState('isAdmin', false);


            $this->_secureLogin();

            $this->setState('securityKey', $this->_securityKey);
        }
        return!$this->errorCode;
    }

    public function getId() {
        return $this->_id;
    }

    public function getUsername() {
        return $this->_username;
    }
    
    public function getEmail() {
        return $this->_email;
    }
    
    protected function _secureLogin() {
        $user = User::model()->findByPk($this->_id);
        $user->last_visit = time();
        $user->last_ip = ip2long(Yii::app()->func->getUserHostAddress());
        $user->security_key = $this->_securityKey;
        $user->save();
    }

}