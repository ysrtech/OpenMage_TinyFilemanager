<?php
/**
 * BB_TinyFileManager Download Tab Block
 * TinyFileManager pointed at var/export for downloading export files.
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Block_Adminhtml_System_Convert_Gui_Edit_Tab_Download extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bb/tinyfilemanager/download.phtml');
    }

    /**
     * Absolute path to the var/export directory
     *
     * @return string
     */
    public function getExportPath()
    {
        return Mage::getBaseDir('var') . DS . 'export';
    }
}
