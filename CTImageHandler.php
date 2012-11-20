<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ramin
 * Date: 7/23/12
 * Time: 1:45 AM
 * To change this template use File | Settings | File Templates.
 */
class CTImageHandler
{

    public static function generateMultiThumb($dir,$name){
        $imageFile = $dir . $name;
        Yii::import('application.vendors.PHPImageWorkshop.ImageWorkshop');
        // generating copyright material for medium and big images
        $copyrightImage = Yii::getPathOfAlias('webroot.uploads').'/copyright.png';
        $watermarkLayer = new ImageWorkshop(array("imageFromPath" => $copyrightImage));
        $watermarkLayer->opacity(60);

        $baseLayer = new ImageWorkshop(array(
            "width" => 800, // This layer will have the width and height of the document
            "height" => 800,
            "backgroundColor" => "FFFFFF",
        ));

        // generating image
        $imageHandler = new ImageWorkshop(array("imageFromPath" => $imageFile));
        // resize image to 800px*800px for zooming
        if($imageHandler->getWidth() > $imageHandler->getHeight())
            $imageHandler->resizeInPixel(800, null, true);
        else
            $imageHandler->resizeInPixel(null, 800, true);

        // adding copyright layer to 800px*800px image

        $baseLayer->addLayer(1, $imageHandler, 0, 0, "LT");
        $baseLayer->mergeAll();

        // adding copyright layer to 800px*800px image
        $imageHandler->addLayer(1, $watermarkLayer, 12, 12, "LT");

        // clone from source for 56px*56px show in product/offer page
        $image56 = clone $baseLayer;
        // adding copyright layer to 265px*265px image
        $image56->resizeInPixel(56,56,true);
        $filename56 = substr($name, 0, -4) . '_thumb_56' . substr($name, -4, 4);
        $image56->save($dir, $filename56, true, null, 75);

        // clone from source for 130px*130px show in product/offer page
        $image130 = clone $baseLayer;
        // adding copyright layer to 130px*130px image
        $image130->resizeByLargestSideInPixel(130,true);
        $filename130 = substr($name, 0, -4) . '_thumb_130' . substr($name, -4, 4);
        $image130->save($dir, $filename130, true, null, 75);

        // clone from source for 265px*265px show in product/offer page
        $image265 = clone $baseLayer;
        $image265->resizeByLargestSideInPixel(265, true);
        // adding copyright layer to 265px*265px image
        $image265->addLayer(1, $watermarkLayer, 0, 12, "LT");
        $filename265 = substr($name, 0, -4) . '_thumb_265' . substr($name, -4, 4);
        $image265->save($dir, $filename265, true, null, 75);

        $baseLayer->save($dir, $name, true, null, 75);
    }

    public static function generateProfilePicture($dir,$name){
        $imageFile = $dir . $name;
        Yii::import('application.vendors.PHPImageWorkshop.ImageWorkshop');

        $baseLayer = new ImageWorkshop(array(
            "width" => 256, // This layer will have the width and height of the document
            "height" => 256,
            "backgroundColor" => "FFFFFF",
        ));

        // generating image
        $imageHandler = new ImageWorkshop(array("imageFromPath" => $imageFile));
        if($imageHandler->getWidth() > $imageHandler->getHeight())
            $imageHandler->resizeInPixel(256, null, true);
        else
            $imageHandler->resizeInPixel(null, 256, true);
        $baseLayer->addLayer(1, $imageHandler, 0, 0, "LT");
        $baseLayer->mergeAll();
        $baseLayer->save($dir, $name, true, null, 75);
    }

    public static function generateSuitablePicture($dir,$srcName,$desName,$width,$height,$bgColor){
        $imageFile = $dir . $srcName;
        Yii::import('application.vendors.PHPImageWorkshop.ImageWorkshop');

        $baseLayer = new ImageWorkshop(array(
            "width" => $width, // This layer will have the width and height of the document
            "height" => $height,
            "backgroundColor" => $bgColor,
        ));

        // generating image
        $imageHandler = new ImageWorkshop(array("imageFromPath" => $imageFile));
        if($imageHandler->getWidth() > $imageHandler->getHeight())
            $imageHandler->resizeInPixel($width, null, true);
        else
            $imageHandler->resizeInPixel(null, $height, true);
        $baseLayer->addLayer(1, $imageHandler, 0, 0, "LT");
        $baseLayer->mergeAll();
        $baseLayer->save($dir, $desName, true, null, 75);
    }
}
