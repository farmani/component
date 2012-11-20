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
class CTLangugeHandler extends CApplicationComponent {
    public $languages = array();
    public function init() {
        $this->languages = array_keys(Yii::app()->params['languages']);
        array_push($this->languages, Yii::app()->getLanguage());
        $this->parseLanguage();
    }

    private function parseLanguage() {
        Yii::app()->urlManager->parseUrl(Yii::app()->getRequest());
        if(!isset($_GET['lang'])) {
            $defaultLang = Yii::app()->getRequest()->getPreferredLanguage();
            if (in_array($defaultLang, $this->languages)){
                Yii::app()->setLanguage($defaultLang);
            }else{
                Yii::app()->setLanguage($this->languages[0]);
            }
        }elseif($_GET['lang']!=Yii::app()->getLanguage() && in_array($_GET['lang'],$this->languages)) {
            Yii::app()->setLanguage($_GET['lang']);
        }
        var_dump($this->languages);
        exit();
    }
}
?>