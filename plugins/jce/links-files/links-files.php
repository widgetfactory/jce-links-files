<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_WF_EXT') or die('RESTRICTED');

wfimport('editor.libraries.classes.browser');

class WFLinkBrowser_Files
{
    protected $filetypes = 'doc,docx,ppt,pps,pptx,ppsx,xls,xlsx,gif,jpeg,jpg,png,pdf,zip,tar,gz,swf,rar,mov,mp4,qt,wmv,asx,asf,avi,wav,mp3,aiff,odt,odg,odp,ods,odf,rtf,txt,csv';

    private static $browser;

    public function __construct($options = array())
    {
        self::$browser = $this->getFileBrowser();
    }

    /**
     * Get the File Browser instance.
     *
     * @return object WFBrowserExtension
     */
    protected function getFileBrowser()
    {
        return new WFFileBrowser($this->getFileBrowserConfig());
    }

    private function getFileSystemName()
    {
        $wf = WFEditorPlugin::getInstance();

        $base_filesystem = $wf->getParam('editor.filesystem.name', '', '', 'string', false);

        $filesystem = $wf->getParam('links.files.filesystem.name', $base_filesystem);

        // if an object, get the name
        if (is_object($filesystem)) {
            $filesystem = isset($filesystem->name) ? $filesystem->name : 'joomla';
        }

        // if no value, default to "joomla"
        if (empty($filesystem)) {
            $filesystem = 'joomla';
        }

        return $filesystem;
    }

    protected function getFileBrowserConfig($config = array())
    {
        $wf = WFEditorPlugin::getInstance();

        $filetypes = $wf->getParam('links.files.extensions', $this->filetypes);

        $filter = (array) $wf->getParam('editor.dir_filter', array());

        // remove empty values
        $filter = array_filter($filter);

        // get directory from parameter
        $base_dir = $wf->getParam('editor.dir', '', '', 'string', false);

        $dir = $wf->getParam('links.files.dir', $base_dir);

        $base = array(
            'dir' => $dir,
            'filesystem' => $this->getFileSystemName(),
            'filetypes' => $filetypes,
            'filter' => $filter,
        );

        return $base;
    }

    public function display()
    {}

    public function isEnabled()
    {
        $wf = WFEditorPlugin::getInstance();
        return (bool) $wf->getParam('links.files.enable', 1);
    }

    public function getOption()
    {
        return array('com_wf_files');
    }

    public function getList()
    {
        return '<li data-id="option=com_wf_files" class="folder nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon"></span><span class="uk-tree-text">' . WFText::_('PLG_JCE_LINKS_FILES_FILES') . '</span></a></div></li>';
    }

    public function getLinks($args)
    {
        $links = array();

        if (!isset($args->id)) {
            $args->id = '';
        }

        $path = rawurldecode($args->id);

        // check file name
        WFUtility::checkPath($path);

        $items = self::$browser->getItems($path);

        foreach ($items['folders'] as $folder) {
            $links[] = array(
                'id' => 'option=com_wf_files&id=' . rawurlencode($folder['id']),
                'name' => $folder['name'],
                'class' => 'folder nolink',
            );
        }

        foreach ($items['files'] as $file) {
            $links[] = array(
                'url' => $file['url'],
                'id' => $file['id'],
                'name' => $file['name'],
                'class' => 'file',
                'icon' => 'pdf',
            );
        }

        return $links;
    }
}
