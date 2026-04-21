<?php
/**
 * BB_TinyFileManager — rewrites Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tabs
 * to inject import-only and export-only TFM tabs.
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Block_Adminhtml_System_Convert_Gui_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('convert_profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Import/Export Profile'));
    }

    protected function _beforeToHtml()
    {
        $profile = Mage::registry('current_convert_profile');

        $wizardBlock = $this->getLayout()->createBlock('adminhtml/system_convert_gui_edit_tab_wizard');
        $wizardBlock->addData($profile->getData());

        $new      = !$profile->getId();
        $isExport = ($profile->getDirection() === 'export');

        $this->addTab('wizard', [
            'label'   => Mage::helper('adminhtml')->__('Profile Wizard'),
            'content' => $wizardBlock->toHtml(),
            'active'  => true,
        ]);

        if (!$new) {
            if (!$isExport) {
                // Import-only tabs
                $this->addTab('upload', [
                    'label'   => Mage::helper('adminhtml')->__('Upload File'),
                    'content' => $this->getLayout()->createBlock('adminhtml/system_convert_gui_edit_tab_upload')->toHtml(),
                ]);

                $this->addTab('image_upload', [
                    'label'   => Mage::helper('adminhtml')->__('Upload Images'),
                    'content' => $this->getLayout()->createBlock('bb_tinyfilemanager/adminhtml_system_convert_gui_edit_tab_imageupload')->toHtml(),
                ]);
            } else {
                // Export-only tabs
                $this->addTab('download_files', [
                    'label'   => Mage::helper('adminhtml')->__('Download Files'),
                    'content' => $this->getLayout()->createBlock('bb_tinyfilemanager/adminhtml_system_convert_gui_edit_tab_download')->toHtml(),
                ]);
            }

            $this->addTab('run', [
                'label'   => Mage::helper('adminhtml')->__('Run Profile'),
                'content' => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_run')->toHtml(),
            ]);

            $block = $this->getLayout()->createBlock('adminhtml/system_convert_gui_edit_tab_view');
            $this->addTab('view', [
                'label'   => Mage::helper('adminhtml')->__('Profile Actions XML'),
                'content' => $block->initForm()->toHtml(),
            ]);

            $this->addTab('history', [
                'label'   => Mage::helper('adminhtml')->__('Profile History'),
                'content' => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_history')->toHtml(),
            ]);
        }

        return parent::_beforeToHtml();
    }
}
