<?php

/**
 * BB_TinyFileManager Upload Block Override
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Block_Adminhtml_System_Convert_Gui_Edit_Tab_Upload extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bb/tinyfilemanager/upload.phtml');
    }

    public function getPostMaxSize()
    {
        return ini_get('post_max_size');
    }

    public function getUploadMaxSize()
    {
        return ini_get('upload_max_filesize');
    }

    public function getDataMaxSize()
    {
        return min($this->getPostMaxSize(), $this->getUploadMaxSize());
    }

    /**
     * Get import directory path
     *
     * @return string
     */
    public function getImportPath()
    {
        return Mage::getBaseDir('var') . DS . 'import';
    }

    /**
     * Get allowed upload extensions from the current dataflow profile's parse type.
     * Returns 'csv' or 'xml' depending on profile setting, empty string = allow all.
     *
     * @return string
     */
    public function getAllowedExtensions()
    {
        $profile = Mage::registry('current_convert_profile');
        if (!$profile) {
            return '';
        }
        $guiData = $profile->getGuiData();
        if (empty($guiData['parse']['type'])) {
            return '';
        }
        $type = strtolower(trim($guiData['parse']['type']));
        // Map known parse types to file extensions
        $map = array('csv' => 'csv', 'xml' => 'xml');
        return isset($map[$type]) ? $map[$type] : '';
    }

    /**
     * Get TinyFileManager file path
     *
     * @return string
     */
    public function getTinyFileManagerPath()
    {
        $modulePath = Mage::getModuleDir('', 'BB_TinyFileManager');
        return $modulePath . DS . 'lib' . DS . 'tinyfilemanager.php';
    }
}
