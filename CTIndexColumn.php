<?php
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
/**
 * CTHtml class
 *
 * @author Ramin Farmani <ramin.farmani@gmail.com>
 * @link http://www.cryptext.org/
 * @copyright Copyright &copy; 2011-2012 Binalood Cloud Computing Engineering Group Ltd.
 * @license http://www.cryptext.org/license/
 */
Yii::import('zii.widgets.grid.CGridColumn');

class CTIndexColumn extends CGridColumn {

    public $sortable = false;

    public function init()
    {
        parent::init();
    }

    protected function renderDataCellContent($row,$data)
    {
        $pagination = $this->grid->dataProvider->getPagination();
        $index = $pagination->pageSize * $pagination->currentPage + $row + 1;
        echo $index;
    }

}