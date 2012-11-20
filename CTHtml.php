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
 * CTHtml class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */

class CTHtml extends CHtml
{

    /**
     * Makes the given URL relative to the /image directory
     */
    public static function imageFile($src, $alt = '', $htmlOptions = array())
    {
        //$file = Yii::app()->theme->baseUrl . '/images/' . $src;
        //$file = 'http://static.generalb2b/images/' . $src;
        $file = 'http://' . substr(CURRENT_ACTIVE_DOMAIN,0,-4) . '.net/themes/' . Yii::app()->theme->name . '/images/' . $src;
        if (YII_DEBUG){
            $file = 'http://' . CURRENT_ACTIVE_DOMAIN . '/themes/' . Yii::app()->theme->name . '/images/' . $src;
        }
        return parent::image($file, $alt, $htmlOptions);
    }

    /**
     * Makes the given URL relative to the /image directory
     */
    public static function uploadedImageFile($src, $alt = '', $htmlOptions = array())
    {
        //$file = Yii::app()->theme->baseUrl . '/images/' . $src;
        //$file = 'http://static.generalb2b/images/' . $src;
		$file = 'http://' . substr(CURRENT_ACTIVE_DOMAIN,0,-4) . '.net/uploads/' . $src;
		if (YII_DEBUG){
            $file = 'http://' . CURRENT_ACTIVE_DOMAIN . '/uploads/' . $src;
        }
        
        return parent::image($file, $alt, $htmlOptions);
    }

    /**
     * Makes the given URL relative to the /css directory
     */
    public static function cssFile($file, $media = '')
    {
        //die(CVarDumper::dump(Yii::app()->theme,10,true));
        //$file = Yii::app()->theme->baseUrl . '/css/' . $url;
        $url = 'http://' . substr(CURRENT_ACTIVE_DOMAIN,0,-4) . '.net/themes/' . Yii::app()->theme->name . '/css/' . $file;
		if (YII_DEBUG){
            $url = 'http://' . CURRENT_ACTIVE_DOMAIN . '/themes/' . Yii::app()->theme->name . '/css/' . $file;
        }
        $time = time();
        if (YII_DEBUG){
            $url = substr($url,0,-8) . '.css?'.$time;
        }

        return Yii::app()->clientScript->registerCssFile($url, $media);
    }

    /**
     * Makes the given URL relative to the /js directory
     */
    public static function scriptFile($file, $position = CClientScript::POS_END)
    {
        //$file = Yii::app()->theme->baseUrl . '/js/' . $url;
        //$file = 'http://static.generalb2b/js/' . $url;
        /*
        if (YII_DEBUG)
            return Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(Yii::getPathOfAlias('webroot.themes') . '/' . Yii::app()->theme->name . '/js/' . $file), $position);
        */
        $url = 'http://' . substr(CURRENT_ACTIVE_DOMAIN,0,-4) . '.net/themes/' . Yii::app()->theme->name . '/js/' . $file;
		if (YII_DEBUG){
            $url = 'http://' . CURRENT_ACTIVE_DOMAIN . '/themes/' . Yii::app()->theme->name . '/js/' . $file;
        }
        return Yii::app()->clientScript->registerScriptFile($url, $position);
    }

    public static function cssDropDownList($name,$select,$data,$htmlOptions=array())
    {
        /*
        <div class="dropdown">
            <ul class="g-input-dropdown span-3">
            <li class="span-3">
            <a href="#"><span><img src="http://generalb2b/themes/generalb2b/images/icons/<?= substr($currentLang, 0, 2);?>.png"/><?= $languages[$currentLang];?></span><![if gt IE 6]></a><![endif]>
                    <!--[if lte IE 6]><table><tr><td><![endif]-->
                    <ul class="span-3">
                        <?php foreach ($languages as $key => $lang): ?>
                            <?php if ($key != $currentLang):?>
                                <?= '<li class="input-dropdowni span-3"><a class="input-dropdowni" href="'.$this->getOwner()->createMultilanguageReturnUrl(substr($key, 0, 2)).'"><img src="http://generalb2b/themes/generalb2b/images/icons/'.substr($key, 0, 2).'.png"/>'.$lang.'</a></li>'; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <!--[if lte IE 6]></td></tr></table></a><![endif]-->
                </li>
            </ul>
        </div>
        */
        $htmlOptions['name']=$name;
        if(!isset($htmlOptions['id']))
            $htmlOptions['id']=self::getIdByName($name);
        else if($htmlOptions['id']===false)
            unset($htmlOptions['id']);

        self::clientChange('change',$htmlOptions);

        $options="\n".self::listOptions($select,$data,$htmlOptions);

        $html='<ul' . self::renderAttributes($htmlOptions);

        return $html.'>'.$options.'</ul>';
    }

}

?>
