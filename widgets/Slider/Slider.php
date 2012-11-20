<?php
	/**
	* slider class file.
	*
	* @author Ovidiu Pop <matricks@webspider.ro>
	* @link http://www.webspider.ro/
	* @copyright Copyright &copy; 2012 Ovidiu Pop
	* Dual licensed under the MIT and GPL licenses:
	* http://www.opensource.org/licenses/mit-license.php
	* http://www.gnu.org/licenses/gpl.html
	*
	* slider - yii extension
	* - create a photo slideshow
	* version: 1.2

	* To use slider:
	* Extract archive in your extensions folder
	* In the site root create a folder named "images" and copy content of image folder from archive

	* To include a slider in a page:
	* <?php $this->widget('application.extensions.slider.slider');?> or <?php $this->widget('ext.slider.slider');?>
		
	* If you wish to customize your slider, you can set options:
		<?php
		$this->widget('ext.slider.slider', array(

			'sliderBase'=>'/slider/',
			'imagesPath'=>'banners',
			
			'container'=>'slideshow',
			'width'=>960, 
			'height'=>240, 
			'timeout'=>6000,
			'infos'=>true,
			'constrainImage'=>true,
			'images'=>array('01.jpg','02.jpg','03.jpg','04.jpg'),
			'alts'=>array('First description','Second description','Third description','Four description'),
			'defaultUrl'=>Yii::app()->request->hostInfo
			)
		);
		?>
		
		sliderBase = string - set the folder base for slider, related to webapp root
		imagesPath = string - set the folder for images used in active page
		container = string - container class for slider
		width = integer - the width of container
		height = integer - the height of container
		timeout = integer - speed to change images
		infos = string - display the information over every picture
		constrainImage = boolean - force the image to fill the container
		images = array - set the name and the order of the pictures which will be displayied
		alts = array - set alt attribute for every picture used in slider and definder in images attribute
		urls = array - set url for every picture used in slider and definder in images parameter
		defaultUrl = url - set the url where will be redirected at click on picture	
		defaultAlt = url - set default alt atribute to be used for all pictures used in slider
		sameToAll = boolean - if is set to true, the set of images will be taken from "all"" folder from sliderBase folder and can be displayied in all pages (as a banner for example), if is set to false, you must create a folder named by page id, and slider will use that folder
	*/

class Slider extends CWidget
{
    public function run()
    {
        Yii::app()->clientScript->registerScriptFile('http://'.CURRENT_ACTIVE_DOMAIN.Yii::app()->assetManager->publish(
            Yii::getPathOfAlias('webroot.themes') . '/' . Yii::app()->theme->name .
                '/js/widgets/widget-slider.js'), CClientScript::POS_END);

        $photos = array();
        $photos[] = CTHtml::imageFile('slider/battery.jpg','',array('title'=>'DURACELL Battery'));
        $photos[] = CTHtml::imageFile('slider/car.jpg','',array('title'=>'Lexus SUV RX-350'));
        $photos[] = CTHtml::imageFile('slider/pack.jpg','',array('title'=>'Packaging Industries'));
        $photos[] = CTHtml::imageFile('slider/server.jpg','',array('title'=>'Highest Stability with Linode Cloud Hosting'));

        $this->render('Slider', array('photos' => $photos));
    }
}