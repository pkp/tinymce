/**
 * @file plugins/pkpTags/plugin.js
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief TinyMCE PKP tags plugin
 */
tinymce.PluginManager.add('pkpTags', function(editor, url) {
	editor.on('init', function() {
		var cssURL = url + '/styles/editor.css';
		if(document.createStyleSheet){
			document.createStyleSheet(cssURL);
		} else {
			cssLink = editor.dom.create('link', {
				rel: 'stylesheet',
				href: cssURL
			});
			document.getElementsByTagName('head')[0].
			appendChild(cssLink);
		}
	});

	editor.ui.registry.addMenuButton('pkpTags', {
		icon: 'non-breaking',
		tooltip: 'Insert Tag',
		fetch: function(callback) {
			var variableMap = $.pkp.classes.TinyMCEHelper.prototype.getVariableMap('#' + editor.id),
					items = [];
			if (variableMap.length === 0) {
				items.push({
					type: 'menuitem',
					text: 'No tags are available.',
					disabled: true,
					onAction: function() {}
				});
			}
			$.each(variableMap, function(variable, value) {
				items.push({
					type: 'menuitem',
					text: value,
					onAction: function() {
						editor.insertContent(
							$.pkp.classes.TinyMCEHelper.prototype.getVariableElement(variable, value).html());
					}
				});
			});
			callback(items);
		}
	});
});
