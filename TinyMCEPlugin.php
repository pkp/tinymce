<?php

/**
 * @file plugins/generic/tinymce/TinyMCEPlugin.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TinyMCEPlugin
 *
 * @ingroup plugins_generic_tinymce
 *
 * @brief TinyMCE WYSIWYG plugin for textareas - to allow cross-browser HTML editing
 */

namespace APP\plugins\generic\tinymce;

use APP\core\Application;
use PKP\config\Config;
use PKP\core\Registry;
use PKP\facades\Locale;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class TinyMCEPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                Hook::add('TemplateManager::display', $this->registerJS(...));
            }
            return true;
        }
        return false;
    }

    /**
     * @copydoc Plugin::getContextSpecificPluginSettingsFile()
     */
    public function getContextSpecificPluginSettingsFile(): string
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * @copydoc Plugin::getInstallSitePluginSettingsFile()
     */
    public function getInstallSitePluginSettingsFile(): string
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Determine whether the plugin can be disabled.
     */
    public function getCanDisable(): bool
    {
        return false;
    }

    /**
     * Register the TinyMCE JavaScript file
     *
     * Hooked to the the `display` callback in TemplateManager
     */
    public function registerJS(string $hookName, array $args): bool
    {
        $request = & Registry::get('request');
        $templateManager = &$args[0];

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
        $localeKey = $this->getTinyMCELocale(Locale::getLocale());
        if ($localeKey && $localeKey !== 'en_US') {
            $data['tinymceParams'] = [
                'language' => $localeKey,
                'language_url' => $request->getBaseUrl() . '/plugins/generic/tinymce/langs/' . $localeKey . '.js',
            ];
        }
        $context = $request->getContext();
        $contextPath = $context?->getPath() ?? Application::SITE_CONTEXT_PATH;
        $data['uploadUrl'] = $request->getDispatcher()->url($request, Application::ROUTE_API, $contextPath, '_uploadPublicFile');
        $templateManager->addJavaScript(
            'tinymceData',
            '$.pkp.plugins.generic = $.pkp.plugins.generic || {};' .
                '$.pkp.plugins.generic.tinymceplugin = ' . json_encode($data) . ';',
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


    /**
     * Find the best match for an existing TinyMCE locale.
     *
     * @param $locale Weblate locale.
     *
     * @return string A language code that's available in the TinyMCE 'langs' folder.
     */
    public function getTinyMCELocale(string $locale): ?string
    {
        $prefix = $this->getPluginPath() . '/langs/';
        $suffix = '.js';
        $preferences = [
            'de' => 'de_DE',
            'en' => 'en_US',
            'es' => 'es_ES',
            'fr' => 'fr_FR',
            'pt' => 'pt_PT',
        ];

        $language = \Locale::getPrimaryLanguage($locale);
        // Get a list of available options from the filesystem.
        $availableLocaleFiles = glob("{$prefix}*{$suffix}");

        // 1. Look for an exact match and return it.
        if (in_array("{$prefix}{$locale}{$suffix}", $availableLocaleFiles)) {
            return $locale;
        }
        // 2. Look in the preference list for a preferred fallback. -- No preferences defined so no need to do this step
        if ($preference = $preferences[$locale] ?? false) {
            return $preference;
        }
        // 3. Find the first match by language.
        foreach ($availableLocaleFiles as $filename) {
            if (strpos($filename, "{$prefix}{$language}{$prefix}") === 0 || strpos($filename, "{$prefix}{$language}_") === 0) {
                $substring = substr($filename, strlen($prefix), -strlen($suffix));
                return substr($filename, strlen($prefix), -strlen($suffix));
            }
        }
        return null;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\tinymce\TinyMCEPlugin', '\TinyMCEPlugin');
}
