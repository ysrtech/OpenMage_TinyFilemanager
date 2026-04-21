<?php
/**
 * BB_TinyFileManager Image Upload Tab Block
 * TinyFileManager pointed at media/import for image uploads.
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Block_Adminhtml_System_Convert_Gui_Edit_Tab_Imageupload extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bb/tinyfilemanager/imageupload.phtml');
    }

    /**
     * Absolute path to the media/import directory
     *
     * @return string
     */
    public function getMediaImportPath()
    {
        return Mage::getBaseDir('media') . DS . 'import';
    }
}
