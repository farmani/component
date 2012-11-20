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
 * CTMathCaptchaAction class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */
class CTMathCaptchaAction extends CCaptchaAction
{
    protected function generateVerifyCode()
    {
        return mt_rand((int)$this->minLength, (int)$this->maxLength);
    }

    public function renderImage($code)
    {
        parent::renderImage($this->getText($code));
    }

    protected function getText($code)
    {
        $code = (int)$code;
        $rand = mt_rand(1, $code - 1);
        $op = mt_rand(0, 1);
        if ($op)
            return $code-$rand.' + '.$rand;
        else
            return $code+$rand.' - '.$rand;
    }
}