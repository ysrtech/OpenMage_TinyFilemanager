<?php
/**
 * BB_TinyFileManager — rewrites Mage_Adminhtml_Block_System_Convert_Profile_Run
 * to redirect the rendering template to the module's own copy, keeping all
 * modifications inside the BB_TinyFileManager module.
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Block_Adminhtml_System_Convert_Profile_Run
    extends Mage_Adminhtml_Block_System_Convert_Profile_Run
{
    protected function _beforeToHtml()
    {
        $this->setTemplate('bb/tinyfilemanager/profile/process.phtml');
        return parent::_beforeToHtml();
    }
}
