/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

 var url_path = "." + (document.currentScript ? document.currentScript.src : document.getElementsByTagName('script')[document.getElementsByTagName('script').length - 1].src).replace(new RegExp('^' + location.origin), '').replace(/[^\/]+$/, '')+"plugins/";
 CKEDITOR.editorConfig = function( config )
{
	config.toolbar = [
		{ name: 'clipboard', items: ["Copy","Paste","Cut","Undo","Redo"] },
		{ name: 'document', items: ["Source","Scayt",'Maximize'] },
		{ name: 'about', items: ["About"] },
		'/',
		{ name: 'text', items: ["Bold", "Italic", "Strike","Underline","TextColor","BGColor","Subscript","Superscript","FontSize"] },
		{ name: 'other', items: ["Format","Link","Unlink", "Anchor", "Image", "Table", "HorizontalRule","SpecialChar",'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote', 'Comment', 'Note', 'PukiPlugin'] }
	];
	config.allowedContent = true;
	config.height = 250;
	config.extraPlugins = 'sourcearea,colorbutton,panelbutton,floatpanel,panel,button,richcombo,font,comment,note,pukiplugin,entities';
	config.removePlugins = 'elementspath';
	config.format_tags = 'p;h2;h3;h4;pre';
	config.removeDialogTabs = 'image:advanced;link:advanced';
	config.startupShowBorders = false;
	config.htmlEncodeOutput = false;
	config.entities = false;
	config.contentsCss = [guiedit_skin_dir];
	$easyedit_path = url_path + "../";
	if (typeof kcfinder == 'undefined') {
		kcfinder = false;
	}
	if (kcfinder) {
		config.filebrowserImageBrowseUrl = $easyedit_path + 'kcfinder/browse.php?type=images' + '&upload_dir_url=' + upload_dir_url;
		config.filebrowserImageUploadUrl = $easyedit_path + 'kcfinder/upload.php?type=images' + '&upload_dir_url=' + upload_dir_url; 
		config.filebrowserBrowseUrl = $easyedit_path + 'kcfinder/browse.php?type=files' + '&upload_dir_url=' + upload_dir_url;
		config.filebrowserUploadUrl = $easyedit_path + 'kcfinder/upload.php?type=files' + '&upload_dir_url=' + upload_dir_url;
	}
	config.resize_enabled = true;
	config.image_previewText = "ここに画像のプレビューを表示します"; 
};