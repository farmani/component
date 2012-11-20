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

class CTVarDumper extends CVarDumper
{

    /**
     * Makes the given URL relative to the /image directory
     */
    public static function dump($var,$depth=10,$highlight=true,$die=true)
    {
        if($die)
            die(parent::dump($var, $depth, $highlight));
        return parent::dump($var, $depth, $highlight);
    }

}

?>
