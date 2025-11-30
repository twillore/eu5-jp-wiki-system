CKEDITOR.plugins.add( 'pukiplugin', {
	icons: 'pukiplugin',
	init: function( editor ) {
		editor.addCommand( 'insertPukiPlugin', new CKEDITOR.dialogCommand( 'pukipluginDialog' ) );
		editor.ui.addButton( 'PukiPlugin', {
			label: 'PukiPlugin',
			command: 'insertPukiPlugin',
			toolbar: "insert"
		});
	}
});
CKEDITOR.dialog.add( 'pukipluginDialog', function( editor ) {
	return {
		title: 'プラグイン',
		minWidth: 400,
		minHeight: 200,
		contents: [
			{
				id: 'plugin_input',
				label: 'プラグイン',
				elements: [
					{
						type: 'text',
						id: 'plugin_name',
						label: 'プラグイン名',
						validate: CKEDITOR.dialog.validate.notEmpty( "プラグイン名が空白です。(例:comment)" )
					},
					{
						type: 'text',
						id: 'plugin_args',
						label: '引数'
					},
					{
						type: 'radio',
						id: 'plugin_type',
						label: 'タイプ',
						items: [
							['ブロック', 'BLOCK'],
							['インライン', 'INLINE']
						],
						'default': 'BLOCK'
					}
				]
			}
		],
		onOk: function() {
			var dialog = this;
			$name = dialog.getValueOf('plugin_input', 'plugin_name');
			$args = dialog.getValueOf('plugin_input', 'plugin_args');
			$type = dialog.getValueOf('plugin_input', 'plugin_type');
			if ($args !== "") {
				$args = "(" + $args + ")";
			}
			$first = '#';
			$end = '';
			if ($type == "INLINE")
			{
				$first = '&';
				$end = ';';
			}
			editor.insertHtml('<span class="plugin" style="cursor:default">' + $first + $name + $args + $end + '</span>');
		}
	};
});
