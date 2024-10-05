<?php
/*
CMS Made Simple module: UnDoer, for logging and reinstating prior
versions of in-console-edited page-content and other presentation-related data

Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Derived somewhat from 2005-2012 UnDoer module by Ted Kulp, Samuel Goldstein, Eric Pesser
and from 2012 Revisions module by Lukas Blatter.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of that License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
Or read it online at: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/*use CMSMS\Stylesheet as CmsLayoutStylesheet;
use CMSMS\Template as CmsLayoutTemplate;
*/

//CMSMS3 uses
//use CMSMS\CapabilityType as CmsCoreCapabilities;
//use CMSMS\Template as CmsLayoutTemplate;
//use CMSMS\Stylesheet as CmsLayoutStylesheet;
use UnDoer\CleanOldTask;
use UnDoer\Utils;

class UnDoer extends CMSModule
{
    const TYPE_CONTENT = 1; // 2 was redundant TYPE_HTMLBLOB
    const TYPE_STYLESHEET = 3;
    const TYPE_TEMPLATE = 4;

    public $TYPE_NAMES; // translated types

    public function __construct()
    {
        parent::__construct();
        $this->TYPE_NAMES = [
            self::TYPE_CONTENT => $this->Lang('content'),
            self::TYPE_STYLESHEET => $this->Lang('stylesheet'),
            self::TYPE_TEMPLATE => $this->Lang('template')
        ];
        spl_autoload_register([$this, 'AutoLoader']); //prob. redundant on CMSMS3
    }

    public function GetAdminDescription() { return $this->Lang('moddescription'); }
    public function GetAdminSection() { return 'content'; }
    public function GetAuthor() { return ''; }
    public function GetAuthorEmail() { return ''; }
    public function GetChangeLog() { return file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'changelog.htm'); }
    public function GetDependencies() { return []; }
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetHelp() { return $this->Lang('help'); }
    public function GetName() { return 'UnDoer'; }
    public function GetVersion() { return '0.7'; }
    public function HandlesEvents() { return true; }
    public function HasAdmin() { return true; }
    public function InstallPostMessage() { return $this->Lang('postinstall'); }
//  public function IsPluginModule() { return false; } default
//  public function LazyLoadAdmin() { return false; }
//  public function LazyLoadFrontend() { return false; }
    public function MinimumCMSVersion() { return '2.2.21F2'; }
    public function UninstallPostMessage() { return $this->Lang('postuninstall'); }
    public function VisibleToAdminUser() { return $this->CheckPermission('Manage Restores') || $this->CheckPermission('Delete Restores'); }

    public function GetHeaderHTML()
    {
        $baseurl = $this->GetModuleURLPath();
        return "<link rel=\"stylesheet\" href=\"{$baseurl}/styles/module.css\">\n"; // or .min for production or -rtl as appropriate
    }

    public function HasCapability($capability, $params = [])
    {
        switch ($capability) {
            case CmsCoreCapabilities::TASKS:
            case CmsCoreCapabilities::ADMINSEARCH:
                return true;
            default:
                return false;
        }
    }

    public function get_tasks()
    {
        $out = [new CleanOldTask()];
        return $out;
    }

    public function get_adminsearch_slaves()
    {
        return ['\UnDoer\RevisionSearch_slave'];
    }

    public function CheckAccess($perm = 'Manage Restores')
    {
        if (!$this->CheckPermission($perm)) {
            Utils::DisplayErrorPage($this->Lang('accessdenied'));
            return false;
        }
        return true;
    }

    public function DoEvent($originator, $eventname, &$params)
    {
        if ($originator == 'Core') {
            switch ($eventname) {
                case 'ContentEditPre':
                    $content = $params['content']; // this is the post-edit, pre-save object
                    // grab the pre-edit properties, if any
                    $elmid = $content->Id();
                    if ($elmid > 0) { // not a new-page object
                        $ops = cmsms()->GetContentOperations();
                        $current = $ops->LoadContentFromId($elmid, true, true); //force-load deep
                        if ($current) {
                            Utils::ArchiveObject($current, self::TYPE_CONTENT);
                        } else {
                            //TODO handle error
                            //e.g. Utils::DisplayErrorPage($this->Lang('error_X')); return;
                        }
                    }
                    break;
/*              case 'ContentEditPost':
//                    $content = $params['content'];
//$elmid = $content->Id();
//                    Utils::ArchiveObject($content, self::TYPE_CONTENT, true);
// TODO new-page processing here ? nothing here ? if so, abandon this handler
                    $here = 1;
                    break;
*/
                case 'EditTemplatePre':
                    $template = $params['CmsLayoutTemplate']; // the post-edit, pre-save object TODO CMSMS3 equivalent
                    // grab the pre-edit properties, if any
                    $elmid = $template->get_id();
                    if ($elmid > 0) {
                        $current = CmsLayoutTemplate::load($elmid, true); //force-load
                        if ($current) {
                            Utils::ArchiveObject($current, self::TYPE_TEMPLATE);
                        } else {
                            //TODO handle error
                            //e.g. Utils::DisplayErrorPage($this->Lang('error_X')); return;
                        }
                    }
                    break;
/*              case 'AddTemplatePost':
                    $template = $params['CmsLayoutTemplate'];
                    // nothing here ?
//                  $elmid = $template->Id(); //TODO use it
//                  Utils::ArchiveObject($template, self::TYPE_TEMPLATE);
                    break;
*/
                case 'EditStylesheetPre':
                    $stylesheet = $params['CmsLayoutStylesheet']; // the post-edit, pre-save object TODO CMSMS3 equivalent
                    // grab the pre-edit properties, if any
                    $elmid = $stylesheet->get_id();
                    if ($elmid > 0) {
                        $current = CmsLayoutStylesheet::load($elmid, true); //force-load
                        if ($current) {
                            Utils::ArchiveObject($current, self::TYPE_STYLESHEET);
                        } else {
                            //TODO handle error
                            //e.g. Utils::DisplayErrorPage($this->Lang('error_X')); return;
                        }
                    }
                    break;
/*              case 'AddStylesheetPost':
                    $stylesheet = $params['CmsLayoutStylesheet'];
                    // nothing here ?
//                  $elmid = $stylesheet->Id(); //TODO use it
//                  Utils::ArchiveObject($stylesheet, self::TYPE_STYLESHEET);
                    break;
*/
            }
        }
    }

    /**
     * Tailor the contents of $original for use as redirection parameters
     *
     * @param array $original normally the $params array supplied to an
     *  action of this module
     * @return array
     */
    public function FilterParms(array $original)//: array
    {
        $wanted = array_intersect_key(
        [
         CMS_SECURE_PARAM_NAME => '',
//       'item_id' => -1,
         'message' => '',
         'start' => 0,
         'sort_order' => 'name',
         'sort_dir' => 'ASC',
         'type_filter' => -1
        ], $original);
        if (empty($original['message'])) {
            unset($wanted['message']);
        }
        if (empty($original['CMS_SECURE_PARAM_NAME'])) {
            unset($wanted['CMS_SECURE_PARAM_NAME']);
        }
        return $wanted;
    }

    private function AutoLoader($classname)
    {
        if (($p = strpos($classname, 'UnDoer\\')) === 0 || ($p == 1 && $classname[0] == '\\')) {
            $fp = __DIR__.DIRECTORY_SEPARATOR.'lib';
            if ($p == 0) {
                $fp .= DIRECTORY_SEPARATOR;
            }
            $sp = substr($classname, $p+5);
            $fp .= strtr($sp, '\\', DIRECTORY_SEPARATOR);
            $base = basename($fp);
            $fp = dirname($fp) . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'class.' . $base . '.php';
            if (@file_exists($fp)) {
                require_once $fp;
            }
        }
    }
}
