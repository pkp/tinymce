<?php

/**
 * @file plugins/generic/tinymce/TinyMCEPlugin.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TinyMCEPlugin
 * @ingroup plugins_generic_tinymce
 *
 * @brief TinyMCE WYSIWYG plugin for textareas - to allow cross-browser HTML editing
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class TinyMCEPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				HookRegistry::register('TemplateManager::display',array(&$this, 'registerJS'));
				HookRegistry::register('TemplateManager::registerJSLibraryData',array(&$this, 'registerJSData'));
			}
			return true;
		}
		return false;
	}

	/**
	 * @copydoc Plugin::getContextSpecificPluginSettingsFile()
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * @copydoc Plugin::getInstallSitePluginSettingsFile()
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Register the TinyMCE JavaScript file
	 *
	 * Hooked to the the `display` callback in TemplateManager
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function registerJS($hookName, $args) {
		$request =& Registry::get('request');
		$templateManager =& $args[0];

		// Load the TinyMCE JavaScript file
		$min = Config::getVar('general', 'enable_minified') ? '.min' : '';
		$templateManager->addJavaScript(
			'tinymce',
			$request->getBaseUrl() . '/lib/pkp/lib/vendor/tinymce/tinymce/tinymce' . $min . '.js',
			array(
				'contexts' => 'backend',
			)
		);

		return false;
	}

	/**
	 * Register script data required by the JS library
	 *
	 * Hooked to the the `registerJSLibraryData` callback in TemplateManager.
	 * This data is used to initialize the TinyMCE component.
	 * @param $hookName string
	 * @param $args array $args[0] is an array of plugin data.
	 * @return boolean
	 */
	function registerJSData($hookName, $args) {
		$request =& Registry::get('request');

		$tinymceParams = array();

		$localeKey = substr(AppLocale::getLocale(), 0, 2);
		$localePath = $request->getBaseUrl() . '/plugins/generic/tinymce/langs/' . $localeKey . '.js';

		if (file_exists($localePath)) {
			$tinymceParams['language']     = $localeKey;
			$tinymceParams['language_url'] = $localePath;
		}

		$args[0][$this->getJavascriptNameSpace()] = array( 'tinymceParams' => json_encode($tinymceParams) . ';');

		return false;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.tinymce.name');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.tinymce.description');
	}
}

