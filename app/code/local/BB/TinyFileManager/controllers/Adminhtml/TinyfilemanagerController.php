<?php

/**
 * BB_TinyFileManager Adminhtml Controller
 * Outputs TinyFileManager as a standalone page (no Magento layout).
 *
 * @category   BB
 * @package    BB_TinyFileManager
 */
class BB_TinyFileManager_Adminhtml_TinyfilemanagerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action - renders TinyFileManager
     * 
     * SECURITY: TinyFileManager is restricted to var/import directory ONLY
     */
    public function indexAction()
    {
        // Lock TinyFileManager to var/import directory
        $importPath = Mage::getBaseDir('var') . DS . 'import';
        if (!is_dir($importPath)) {
            mkdir($importPath, 0755, true);
        }

        // Derive allowed extensions from the dataflow profile
        $profileId  = (int)$this->getRequest()->getParam('profile_id');
        $allowedExt = '';
        if ($profileId) {
            $profile   = Mage::getModel('dataflow/profile')->load($profileId);
            $guiData   = $profile->getGuiData();
            $parseType = !empty($guiData['parse']['type']) ? strtolower(trim($guiData['parse']['type'])) : '';
            $typeMap   = array('csv' => 'csv', 'excel_xml' => 'xml');
            $allowedExt = isset($typeMap[$parseType]) ? $typeMap[$parseType] : '';
        }

        $selfUrl = rtrim($this->getUrl('*/*/index', array(
            'profile_id'  => $profileId,
            'allowed_ext' => $allowedExt,
            '_nosid'      => true,
        )), '/');

        $this->_serveTinyFileManager($importPath, $allowedExt, $selfUrl);
    }

    /**
     * Image Manager action — TFM pointed at media/import (all file types allowed)
     */
    public function imagemanagerAction()
    {
        $mediaImportPath = Mage::getBaseDir('media') . DS . 'import';
        if (!is_dir($mediaImportPath)) {
            mkdir($mediaImportPath, 0755, true);
        }
        $selfUrl = rtrim($this->getUrl('*/*/imagemanager', array('_nosid' => true)), '/');
        $this->_serveTinyFileManager($mediaImportPath, '', $selfUrl);
    }

    /**
     * Export Download action — TFM pointed at var/export (all file types, read-friendly)
     */
    public function exportmanagerAction()
    {
        $exportPath = Mage::getBaseDir('var') . DS . 'export';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }
        $selfUrl = rtrim($this->getUrl('*/*/exportmanager', array('_nosid' => true)), '/');
        $this->_serveTinyFileManager($exportPath, '', $selfUrl);
    }

    /**
     * Secure file download action for var/export files.
     * Only serves files that actually exist inside var/export — no path traversal.
     */
    public function downloadAction()
    {
        $filename = (string)$this->getRequest()->getParam('file');
        $filename = basename($filename); // strip any path components

        if ($filename === '' || strpos($filename, '\0') !== false) {
            $this->getResponse()->setHttpResponseCode(400)->setBody('Bad request.');
            return;
        }

        $exportDir = Mage::getBaseDir('var') . DS . 'export';
        $filePath  = $exportDir . DS . $filename;

        // Confirm the resolved path is still inside var/export
        $realFile = realpath($filePath);
        $realDir  = realpath($exportDir);
        if ($realFile === false || $realDir === false || strpos($realFile, $realDir . DIRECTORY_SEPARATOR) !== 0) {
            $this->getResponse()->setHttpResponseCode(403)->setBody('Forbidden.');
            return;
        }

        if (!is_file($realFile)) {
            $this->getResponse()->setHttpResponseCode(404)->setBody('File not found.');
            return;
        }

        $mimeType = 'application/octet-stream';
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'csv')  { $mimeType = 'text/csv'; }
        if ($ext === 'xml')  { $mimeType = 'application/xml'; }
        if ($ext === 'xlsx') { $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', $mimeType, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"', true)
            ->setHeader('Content-Length', filesize($realFile), true)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Expires', '0', true)
            ->sendHeaders();

        readfile($realFile);
        exit;
    }

    /**
     * Shared TFM renderer used by indexAction and imagemanagerAction.
     *
     * @param string $rootPath   Absolute filesystem path TFM is locked to
     * @param string $allowedExt Comma-separated extensions (empty = all allowed)
     * @param string $selfUrl    URL TFM posts back to for its own actions
     */
    protected function _serveTinyFileManager($rootPath, $allowedExt = '', $selfUrl = '')
    {
        $modulePath          = Mage::getModuleDir('', 'BB_TinyFileManager');
        $tinyFileManagerPath = $modulePath . DS . 'lib' . DS . 'tinyfilemanager.php';

        if (!file_exists($tinyFileManagerPath)) {
            $this->getResponse()->setBody(
                '<div style="padding:20px;font-family:Arial,sans-serif;">' .
                '<h2 style="color:#d00;">TinyFileManager Not Found</h2>' .
                '<p>Expected at: <code>' . htmlspecialchars($tinyFileManagerPath) . '</code></p>' .
                '</div>'
            );
            return;
        }

        // Build exclude list: hide all import file types EXCEPT the allowed one.
        $allImportExts = array('csv', 'xml', 'xlsx', 'txt');
        $excludeItems  = array();
        if ($allowedExt !== '') {
            foreach ($allImportExts as $ext) {
                if ($ext !== $allowedExt) {
                    $excludeItems[] = '*.' . $ext;
                }
            }
        }

        // CRITICAL: FM_Config uses `global $CONFIG`, `global $root_path` etc.
        // These must live in $GLOBALS so class methods can pull them via `global`.
        $GLOBALS['CONFIG']    = '{"lang":"en","error_reporting":false,"show_hidden":true,"hide_Cols":true,"theme":"light"}';
        $GLOBALS['root_path'] = $rootPath;
        $GLOBALS['root_url']  = '';
        $GLOBALS['http_host'] = $_SERVER['HTTP_HOST'];
        $GLOBALS['use_auth']           = false;
        $GLOBALS['auth_users']         = array();
        $GLOBALS['readonly_users']     = array();
        $GLOBALS['global_readonly']    = false;
        $GLOBALS['directories_users']  = array();
        $GLOBALS['use_highlightjs']    = true;
        $GLOBALS['highlightjs_style']  = 'github';
        $GLOBALS['edit_files']         = true;
        $GLOBALS['default_timezone']   = 'UTC';
        $GLOBALS['datetime_format']    = 'd.m.y H:i:s';
        $GLOBALS['iconv_input_encoding']      = 'UTF-8';
        $GLOBALS['path_display_mode']         = 'full';
        $GLOBALS['hide_Cols']                  = true;
        $GLOBALS['allowed_file_extensions']   = $allowedExt;
        $GLOBALS['allowed_upload_extensions'] = $allowedExt;
        $GLOBALS['favicon_path']              = '';
        $GLOBALS['exclude_items']             = $excludeItems;
        $GLOBALS['online_viewer']             = 'google';
        $GLOBALS['sticky_navbar']             = true;
        $GLOBALS['max_upload_size_bytes']     = 5000 * 1024 * 1024;
        $GLOBALS['upload_chunk_size_bytes']   = 2 * 1024 * 1024;
        $GLOBALS['ip_ruleset']   = 'OFF';
        $GLOBALS['ip_silent']    = true;
        $GLOBALS['ip_whitelist'] = '';
        $GLOBALS['ip_blacklist'] = '';

        // Mirror into local scope for TFM's top-level code
        $CONFIG                    = $GLOBALS['CONFIG'];
        $root_path                 = $rootPath;
        $root_url                  = '';
        $http_host                 = $_SERVER['HTTP_HOST'];
        $use_auth                  = false;
        $auth_users                = array();
        $readonly_users            = array();
        $global_readonly           = false;
        $directories_users         = array();
        $use_highlightjs           = true;
        $highlightjs_style         = 'github';
        $edit_files                = true;
        $default_timezone          = 'UTC';
        $datetime_format           = 'd.m.y H:i:s';
        $iconv_input_encoding      = 'UTF-8';
        $path_display_mode         = 'full';
        $allowed_file_extensions   = $allowedExt;
        $allowed_upload_extensions = $allowedExt;
        $favicon_path              = '';
        $exclude_items             = $excludeItems;
        $online_viewer             = 'google';
        $sticky_navbar             = true;
        $max_upload_size_bytes     = $GLOBALS['max_upload_size_bytes'];
        $upload_chunk_size_bytes   = $GLOBALS['upload_chunk_size_bytes'];
        $ip_ruleset                = 'OFF';
        $ip_silent                 = true;
        $ip_whitelist              = '';
        $ip_blacklist              = '';
        $session_cache_limiter     = 'nocache';

        if (!defined('FM_SELF_URL'))         define('FM_SELF_URL',         $selfUrl);
        if (!defined('FM_ROOT_PATH'))        define('FM_ROOT_PATH',        $rootPath);
        if (!defined('FM_ROOT_URL'))         define('FM_ROOT_URL',         '');
        if (!defined('FM_FILE_EXTENSION'))   define('FM_FILE_EXTENSION',   $allowedExt);
        if (!defined('FM_UPLOAD_EXTENSION')) define('FM_UPLOAD_EXTENSION', $allowedExt);
        if (!defined('FM_EXCLUDE_ITEMS'))    define('FM_EXCLUDE_ITEMS',    $excludeItems);

        // Bind TFM's top-level variables to $GLOBALS so class methods can read them
        global $external, $cfg, $lang, $lang_list, $report_errors, $show_hidden_files,
               $hide_Cols, $theme, $config_file, $editFile, $callback,
               $path, $fileinfo, $temp_file;

        ob_start();
        try {
            include($tinyFileManagerPath);
        } catch (Exception $e) {
            ob_end_clean();
            $this->getResponse()->setBody(
                '<div style="padding:20px;font-family:Arial,sans-serif;">' .
                '<h2 style="color:#d00;">Error Loading TinyFileManager</h2>' .
                '<p>' . htmlspecialchars($e->getMessage()) . '</p>' .
                '</div>'
            );
            return;
        } catch (Error $e) {
            ob_end_clean();
            $this->getResponse()->setBody(
                '<div style="padding:20px;font-family:Arial,sans-serif;">' .
                '<h2 style="color:#d00;">Fatal Error Loading TinyFileManager</h2>' .
                '<p>' . htmlspecialchars($e->getMessage()) . '</p>' .
                '</div>'
            );
            return;
        }
        $content = ob_get_clean();

        $hideLinkCss = '<style>.fa-link{display:none!important}a[title="Direct link"]{display:none!important}</style>';
        $xhrHook = '<script>
(function(){
    function bb_notify(){try{parent.postMessage({tfmModified:true},"*");}catch(e){}}
    var op=XMLHttpRequest.prototype.open, sn=XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open=function(m){this._bb_m=m;return op.apply(this,arguments);};
    XMLHttpRequest.prototype.send=function(){
        if(this._bb_m&&this._bb_m.toUpperCase()==="POST"){
            this.addEventListener("load",function(){
                if(this.status>=200&&this.status<400){bb_notify();}
            });
        }
        return sn.apply(this,arguments);
    };
    document.addEventListener("submit",function(e){
        var f=e.target;
        if(f&&f.method&&f.method.toUpperCase()==="POST"){bb_notify();}
    },true);
    var _write_params=["del","delete","rename","mkdir","copy","move","extract","chmod"];
    document.addEventListener("click",function(e){
        var a=e.target.closest("a");
        if(!a)return;
        var href=a.getAttribute("href")||"";
        for(var i=0;i<_write_params.length;i++){
            if(href.indexOf(_write_params[i]+"=")!==-1||href.indexOf("&"+_write_params[i]+"&")!==-1){
                bb_notify();break;
            }
        }
    },true);
})();
</script>';
        $content = str_replace('</head>', $hideLinkCss . $xhrHook . '</head>', $content);

        if (empty($content)) {
            $this->getResponse()->setBody(
                '<div style="padding:20px;font-family:Arial,sans-serif;">' .
                '<h2 style="color:#d00;">TinyFileManager returned empty content</h2>' .
                '<p>Check server error logs for details.</p>' .
                '</div>'
            );
            return;
        }

        $this->getResponse()->setBody($content);
    }

    /**
     * Bypass Magento form key validation for this controller.
     * TFM uses its own session-based CSRF token; access is already gated by
     * the admin session and _isAllowed() below.
     */
    protected function _validateFormKey()
    {
        return true;
    }

    /**
     * Check ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/convert');
    }
}
