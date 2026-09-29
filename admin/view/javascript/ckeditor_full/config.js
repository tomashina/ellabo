/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	ck_clicker_on_config();

	config.font_names = 'Open Sans/Open Sans;Open Sans Condensed/Open Sans Condensed;' + config.font_names;

	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	var user_token = '';
	if (typeof getURLVar == 'function') {
		var var_token = getURLVar('token');
		if (var_token) {
			var_token = '&token=' + var_token;
		} else {
			var_token = '&user_token=' + getURLVar('user_token');
		}

		if (var_token) {
			user_token = var_token;
		}
	}

	config.filebrowserBrowseUrl = 'index.php?route=common/filemanager&iframe=1' + user_token;
	//config.filebrowserImageBrowseUrl = 'index.php?route=common/filemanager';
	//config.filebrowserFlashBrowseUrl = 'index.php?route=common/filemanager';
	//config.filebrowserUploadUrl = 'index.php?route=common/filemanager';
	//config.filebrowserImageUploadUrl = 'index.php?route=common/filemanager';
	//config.filebrowserFlashUploadUrl = 'index.php?route=common/filemanager';

	//config.filebrowserWindowWidth = '960';
	//config.filebrowserWindowHeight = '580';

	//config.enterMode = CKEDITOR.ENTER_BR;
	//config.shiftEnterMode = CKEDITOR.ENTER_P;

	config.extraPlugins = 'codemirror,imagecaptioned';
	config.removePlugins = 'wsc,scayt,preview,newpage,save,print,language,bt_table';
	//config.removeButtons = 'searchCode,autoFormat,CommentSelectedRange,UncommentSelectedRange,AutoComplete';

	config.forcePastePopup = true;

	// All content will be pasted as plain text.
	//config.forcePasteAsPlainText = true;
	// Only Microsoft Word content formatting will be preserved.
	//config.forcePasteAsPlainText = 'allow-word';

	config.skin = 'moono-lisa';

	config.htmlEncodeOutput = false;
	config.entities = false;
	config.startupOutlineBlocks = true;
	config.extraAllowedContent = '*{*}';
	config.allowedContent = true;

	config.embed_provider = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}'; // for ckeditor 4.7

	config.contentsLangDirection = $('html').attr('dir');
	//config.contentsLangDirection = 'ltr';
	//config.contentsLangDirection = 'rtl';
	//config.skin = 'moono';
	//config.toolbar = 'full';
	//config.toolbar = 'Custom';

	/*config.toolbarGroups: [
		{ name: 'document',	   groups: [ 'mode', 'document' ] },
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'links' }
	];*/

	// FontAwesome configs
	config.fontawesomePath = 'view/javascript/font-awesome/css/font-awesome.min.css';
	/*$.ajax({
		url: config.fontawesomePath,
		type: 'HEAD',
		async: false,
		cache: true,
		error: function() {
			config.fontawesomePath = '';
		},
		success: function() {
		}
	});*/

	var spec_chars = ['&plusmn;', '&Omega;'];

	for (var i = 0; i < spec_chars.length; i++) {
		if (!config.specialChars.includes(spec_chars[i])) {
			config.specialChars.push(spec_chars[i]);
		}
	}

	CKEDITOR.dtd.$removeEmpty['span'] = false;
	CKEDITOR.dtd.$removeEmpty['i'] = false;
	/*config.toolbar = [
		{ name: 'insert', items: [ 'FontAwesome', 'Source' ] }
	];*/
	//config.startupMode = 'source';
};

function ck_clicker_on_config() {
	setTimeout(function() {
		var cssId = 'ck_css';
		if (!document.getElementById(cssId)) {
			var head  = document.getElementsByTagName('head')[0];
			var link  = document.createElement('link');
			link.id   = cssId;
			link.rel  = 'stylesheet';
			link.type = 'text/css';
			link.href = 'view/javascript/ckeditor_full/ck_clicker.css';
			link.media = 'all';
			head.appendChild(link);
		}
	}, 10);
}