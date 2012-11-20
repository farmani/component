<?php
class LanguageSelector extends CWidget
{
    public $languages = array();
    public function run()
    {
        $currentLang = Yii::app()->language;
        if(empty($this->languages))
            $this->languages = Yii::app()->params->languages;
        else{

        }
        $this->render('languageSelector', array('currentLang' => $currentLang, 'languages'=>$this->languages));
    }
}
?>