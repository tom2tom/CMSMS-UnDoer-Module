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
use UnDoer\CleanOldTask;
use UnDoer\Utils;

class UnDoer extends CMSModule
{
    const TYPE_CONTENT = 1;
//  const $TYPE_HTMLBLOB = 2;
    const TYPE_STYLESHEET = 3;
    const TYPE_TEMPLATE = 4;

    public $TYPE_NAMES;

    public function __construct()
    {
        parent::__construct();
        $this->TYPE_NAMES = [
            self::TYPE_CONTENT => $this->Lang('content'),
//          self::TYPE_HTMLBLOB => $this->Lang('htmlblob'),
            self::TYPE_STYLESHEET => $this->Lang('stylesheet'),
            self::TYPE_TEMPLATE => $this->Lang('template')
        ];
        spl_autoload_register([$this, 'AutoLoader']);
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
    public function GetVersion() { return '0.6'; }
    public function HandlesEvents() { return true; }
    public function HasAdmin() { return true; }
    public function InstallPostMessage() { return $this->Lang('postinstall'); }
//  public function IsPluginModule() { return false; } default
//  public function LazyLoadAdmin() { return false; }
//  public function LazyLoadFrontend() { return false; }
    public function MinimumCMSVersion() { return '2.2'; }
    public function UninstallPostMessage() { return $this->Lang('postuninstall'); }
    public function VisibleToAdminUser() { return $this->CheckPermission('Manage Restores'); }

    public function GetHeaderHTML()
    {
        $baseurl = $this->GetModuleURLPath();
        return "<link rel=\"stylesheet\" href=\"{$baseurl}/styles/module.css\">\n"; // or .min for production
    }

    public function HasCapability($capability, $params = [])
    {
        switch ($capability) {
            case CmsCoreCapabilities::TASKS:
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

/*  public function DoAction($action, $id, $params, $returnid = '')
    {
        $this->TYPE_NAMES = [
            self::TYPE_CONTENT => $this->Lang('content'),
//          self::TYPE_HTMLBLOB => $this->Lang('htmlblob'),
            self::TYPE_STYLESHEET => $this->Lang('stylesheet'),
            self::TYPE_TEMPLATE => $this->Lang('template')
        ];
        $this->SetupAdminNav($action, $id, $params, $returnid);
        return parent::DoAction($action, $id, $params, $returnid);
    }
*/
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
                case 'ContentEditPost':
                    $content = $params['content'];
                    Utils::ArchiveObject($content, self::TYPE_CONTENT, true);
                    break;
                case 'ContentEditPre':
                    $content = $params['content'];
                    if ($content->Id() != -1) {
                        Utils::ArchiveObject($content, self::TYPE_CONTENT);
                    }
                    break;
                case 'AddTemplatePost':
                    $template = $params['CmsLayoutTemplate'];
                    Utils::ArchiveObject($template, self::TYPE_TEMPLATE);
                    break;
                case 'EditTemplatePre':
                    $template = $params['CmsLayoutTemplate'];
                    Utils::ArchiveObject($template, self::TYPE_TEMPLATE);
                    break;
                case 'EditStylesheetPre':
                    $stylesheet = $params['CmsLayoutStylesheet'];
                    Utils::ArchiveObject($stylesheet, self::TYPE_STYLESHEET);
                    break;
                case 'AddStylesheetPost':
                    $stylesheet = $params['CmsLayoutStylesheet'];
                    Utils::ArchiveObject($stylesheet, self::TYPE_STYLESHEET);
                    break;
/*             case 'EditGlobalContentPre':
                    $global_content = $params['global_content'];
                    Utils::ArchiveObject($global_content, self::TYPE_HTMLBLOB);
                    break;
                case 'AddGlobalContentPost':
                    $global_content = $params['global_content'];
                    Utils::ArchiveObject($global_content, self::TYPE_HTMLBLOB);
                    break;
*/
            }
        }
    }
/*
    protected function SetupAdminNav($action, $id, $params, $returnid)
    {
        $theme = cms_utils::get_theme_object();
        $smarty = cmsms()->GetSmarty();
        $content = '';
        if ($action != 'simplelist') {
            $content =
            $this->CreateLink($id, 'simplelist', $returnid,
                '<img src="'.$this->GetModuleURLPath().'/images/simplelist.png" class="systemicon" style="padding-left:.25em;padding-right:.25em" alt="'. //TODO class also supporting "padding-left:.25em;padding-right:.25em;"
                $this->Lang('simplelist').'" title="'.$this->Lang('simplelist').'">',
                []
            ) .
            $this->CreateLink($id, 'simplelist', $returnid, $this->Lang('simplelist'), []);
        }
        if ($action != 'fulllist') {
            if ($content) {
                $content .= ' : ';
            }
            $content .=
            $this->CreateLink($id, 'fulllist', $returnid,
                '<img src="'.$this->GetModuleURLPath().'/images/fulllist.png" class="systemicon" style="padding-left:.25em;padding-right:.25em" alt="'. //TODO class also supporting "padding-left:.25em;padding-right:.25em;"
                $this->Lang('fulllist').'" title="'.$this->Lang('fulllist').'">',
                []
            ) .
            $this->CreateLink($id, 'fulllist', $returnid, $this->Lang('fulllist'), []);
        }
/*system-wide snapshots not implemented
        if ($action != 'snapshots' && $this->CheckPermission('TODO') {
            if ($content) {
                $content .= ' : ';
            }
            $content .=
            $this->CreateLink($id, 'listsnapshots', $returnid,
                '<img src="'.$this->GetModuleURLPath().'/images/snapshot.png" class="systemicon" style="padding-left:.25em;padding-right:.25em" alt="'. //TODO class also supporting "padding-left:.25em;padding-right:.25em;"
                $this->Lang('listsnapshots').'" title="'.$this->Lang('listsnapshots').'">',
                []
            ) .
            $this->CreateLink($id, 'snapshots', $returnid, $this->Lang('listsnapshots'), []);
* /
        if ($action != 'settings' && $this->CheckPermission('Modify Site Preferences')) {
            if ($content) {
                $content .= ' : ';
            }
            $content .=
            $this->CreateLink($id, 'settings', $returnid, $theme->DisplayImage(
                'icons/topfiles/siteprefs.gif',
                $this->Lang('adminprefs'),
                '',
                '',
                'systemicon' //TODO class also supporting "padding-left:.25em;padding-right:.25em;"
            ), []) .
            $this->CreateLink($id, 'settings', $returnid, $this->Lang('adminprefs'), []);
        }
        $smarty->assign('admin_nav', $content);
    }
*/
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
