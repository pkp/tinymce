<?php

/**
 * @file plugins/generic/tinymce/TinyMCEPlugin.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TinyMCEPlugin
 * @ingroup plugins_generic_tinymce
 *
 * @brief TinyMCE WYSIWYG plugin for textareas - to allow cross-browser HTML editing
 */

namespace APP\plugins\generic\tinymce;

use PKP\plugins\GenericPlugin;
use PKP\plugins\HookRegistry;
use PKP\core\PKPApplication;
use PKP\core\Registry;
use PKP\config\Config;
use APP\i18n\AppLocale;
use APP\core\Application;

class TinyMCEPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                HookRegistry::register('TemplateManager::display', [&$this, 'registerJS']);
            }
            return true;
        }
        return false;
    }

    /**
     * @copydoc Plugin::getContextSpecificPluginSettingsFile()
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * @copydoc Plugin::getInstallSitePluginSettingsFile()
     */
    public function getInstallSitePluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Determine whether the plugin can be disabled.
     *
     * @return boolean
     */
    public function getCanDisable()
    {
        return false;
    }

    /**
     * Register the TinyMCE JavaScript file
     *
     * Hooked to the the `display` callback in TemplateManager
     *
     * @param $hookName string
     * @param $args array
     *
     * @return boolean
     */
    public function registerJS($hookName, $args)
    {
        $request = & Registry::get('request');
        $templateManager = & $args[0];

        // Load the TinyMCE JavaScript file
        $min = Config::getVar('general', 'enable_minified') ? '.min' : '';
        $templateManager->addJavaScript(
            'tinymce',
            $request->getBaseUrl() . '/lib/pkp/lib/vendor/tinymce/tinymce/tinymce' . $min . '.js',
            [
                'contexts' => 'backend',
            ]
        );

        // Load the script data used by the JS library
        $data = [];
        $localeKey = substr(AppLocale::getLocale(), 0, 2);
        $localePath = $request->getBaseUrl() . '/plugins/generic/tinymce/langs/' . $localeKey . '.js';
        if (file_exists($localePath)) {
            $data['tinymceParams'] = [
                'language' => $localeKey,
                'language_url' => $localePath,
            ];
        }
        $context = $request->getContext();
        if ($context) {
            $data['uploadUrl'] = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $context->getPath(), '_uploadPublicFile');
        }
        $templateManager->addJavaScript(
            'tinymceData',
            '$.pkp.plugins.generic = $.pkp.plugins.generic || {};' .
                '$.pkp.plugins.generic.' . strtolower(get_class($this)) . ' = ' . json_encode($data) . ';',
            [
                'inline' => true,
                'contexts' => 'backend',
            ]
        );


        return false;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.generic.tinymce.name');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.generic.tinymce.description');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\tinymce\TinyMCEPlugin', '\TinyMCEPlugin');
}
