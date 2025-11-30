<?php

// guiedit.inc.php, v 1.63.2 2009/04/20 23:06:00 upk Exp $
// easyedit.inc.php,v 1.09 2021/01/29 22:48:00 pitqn(K) Exp $
// easyedit.inc.php,v 1.10 2021/12/30 00:00:00 haifun Exp $
// $Id: easyedit.inc.php,v 1.12 2022/03/12 14:27:00 pitqn Exp $
/**
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/easyedit.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GNU General Public License Version 2 or later (GPL)
*/

// v1.10 PHP8対応、細かいところを修正 byはいふん
// v1.11 KCFinderの組み込み、見出し編集追加、バグ修正 by K

// CKEditorが読み込むCSS
// define('PLUGIN_EASYEDIT_SKIN_FILE', SKIN_DIR . "xxx.css");
define('PLUGIN_EASYEDIT_SKIN_FILE', "./skin/pukiwiki.css");

// EasyEditのフォルダ
define('EASYEDIT_LIB_PATH', 'easyedit/');

// KCFinderの有効/無効 (こちらで無効にしてもアクセスは可能ですので注意)
define('PLUGIN_EASYEDIT_ENABLE_KCFINDER', true);

// 見出しGUI編集
define('PLUGIN_EASYEDIT_SECEDIT', true);

// 見出しGUI編集リンク (テキストver)
//define('PLUGIN_EASYEDIT_SECEDIT_LINK_STYLE', '$1<span style="float:right; font-size: small; font-weight: lighter; padding: 0px 0px 0px 1em; ">[<a href="$2" title="GuiEdit">' . 'guiedit</a>]</span>');

// 見出しGUI編集リンク (アイコンver)
define('PLUGIN_EASYEDIT_SECEDIT_LINK_STYLE', '$1<a class="anchor_super" href="$2" title="GuiEdit">' .
    ' <img src="image/paraguiedit.png" width="9" height="9" alt="GuiEdit" title="GuiEdit" /></a>');

define('PLUGIN_EASYEDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');
define('PREG_EASYEDIT_LIB_PATH', str_replace(EASYEDIT_LIB_PATH, "/", "\\/"));
define('CKEDITOR_PATH', EASYEDIT_LIB_PATH . "ckeditor.js");

function plugin_easyedit_action()
{
    global $vars, $_title_edit;
    if (PKWK_READONLY) {
        die_message('PKWK_READONLY prohibits editing');
    }

    // Create initial pages
    plugin_easyedit_setup_initial_pages();

    $page = isset($vars['page']) ? $vars['page'] : '';
    check_editable($page, true, true);
    check_readable($page, true, true);

    if (isset($vars['preview'])) {
        return plugin_easyedit_preview($vars['msg']);
    } elseif (isset($vars['template'])) {
        return plugin_easyedit_preview_with_template();
    } elseif (isset($vars['write'])) {
        return plugin_easyedit_write();
    } elseif (isset($vars['cancel'])) {
        return plugin_easyedit_cancel();
    }
    $postdata = @join('', get_source($page));
    if ($postdata === '') {
        $postdata = auto_template($page);
    }
    $postdata = remove_author_info($postdata);
    return array('msg'=>$_title_edit, 'body'=>easyedit_form($page, $postdata));
}

/**
 * Preview with template
 */
function plugin_easyedit_preview_with_template()
{
    global $vars;
    $msg = '';
    $page = isset($vars['page']) ? $vars['page'] : '';
    // Loading template
    $template_page = '';
    if (isset($vars['template_page']) && is_page($template_page = $vars['template_page'])) {
        if (is_page_readable($template_page)) {
            $msg = remove_author_info(get_source($vars['template_page'], true, true));
            // Cut fixed anchors
            $msg = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $msg);
        }
    }
    return plugin_easyedit_preview($msg);
}

function plugin_easyedit_preview($msg)
{
    global $vars;
    global $_title_preview, $_msg_preview, $_msg_preview_delete;
    
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    //EasyEdit
    require_once(EASYEDIT_LIB_PATH . 'guiedit/htmlv2wiki.php');
    $msg = htmlv2wiki($msg);
    //--------
    $msg = preg_replace(PLUGIN_EASYEDIT_FREEZE_REGEX, '', $msg);
    $postdata = $msg;

    if (isset($vars['add']) && $vars['add']) {
        if (isset($vars['add_top']) && $vars['add_top']) {
            $postdata  = $postdata . "\n\n" . @join('', get_source($page));
        } else {
            $postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
        }
    }

    $body = $_msg_preview . '<br />' . "\n";
    if ($postdata === '') {
        $body .= '<strong>' . $_msg_preview_delete . '</strong>';
    }
    $body .= '<br />' . "\n";

    if ($postdata) {
        $postdata = make_str_rules($postdata);
        $postdata = explode("\n", $postdata);
        $postdata = drop_submit(convert_html($postdata));
        $body .= '<div id="preview">' . $postdata . '</div>' . "\n";
    }
    
    $body .= easyedit_form($page, $msg, $vars['digest'], false);

    return array('msg'=>$_title_preview, 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
function plugin_easyedit_inline()
{
    static $usage = '&easyedit(pagename#anchor[[,noicon],nolabel])[{label}];';

    global $vars, $fixed_heading_anchor_edit;

    if (PKWK_READONLY) {
        return '';
    } // Show nothing

    // Arguments
    $args = func_get_args();

    // {label}. Strip anchor tags only
    $s_label = strip_htmltag(array_pop($args), false);

    $page = array_shift($args);
    if ($page === null) {
        $page = '';
    }
    $_noicon = $_nolabel = false;
    foreach ($args as $arg) {
        switch (strtolower($arg)) {
        case '':                   break;
        case 'nolabel': $_nolabel = true; break;
        case 'noicon': $_noicon  = true; break;
        default: return $usage;
        }
    }

    // Separate a page-name and a fixed anchor
    list($s_page, $id, $editable) = anchor_explode($page, true);

    // Default: This one
    if ($s_page == '') {
        $s_page = isset($vars['page']) ? $vars['page'] : '';
    }

    // $s_page fixed
    $isfreeze = is_freeze($s_page);
    $ispage   = is_page($s_page);

    // Paragraph edit enabled or not
    $short = htmlsc('Edit');
    if ($fixed_heading_anchor_edit && $editable && $ispage && ! $isfreeze) {
        // Paragraph editing
        $id    = rawurlencode($id);
        $title = htmlsc(sprintf('Edit %s', $page));
        $icon = '<img src="' . IMAGE_DIR . 'paraedit.png' .
            '" width="9" height="9" alt="' .
            $short . '" title="' . $title . '" /> ';
        $class = ' class="anchor_super"';
    } else {
        // Normal editing / unfreeze
        $id    = '';
        if ($isfreeze) {
            $title = 'Unfreeze %s';
            $icon  = 'unfreeze.png';
        } else {
            $title = 'EasyEdit %s';
            $icon  = 'edit.png';
        }
        $title = htmlsc(sprintf($title, $s_page));
        $icon = '<img src="' . IMAGE_DIR . $icon .
            '" width="20" height="20" alt="' .
            $short . '" title="' . $title . '" />';
        $class = '';
    }
    if ($_noicon) {
        $icon = '';
    } // No more icon
    if ($_nolabel) {
        if (!$_noicon) {
            $s_label = '';     // No label with an icon
        } else {
            $s_label = $short; // Short label without an icon
        }
    } else {
        if ($s_label == '') {
            $s_label = $title;
        } // Rich label with an icon
    }

    // URL
    $script = get_base_uri();
    if ($isfreeze) {
        $url   = $script . '?cmd=unfreeze&amp;page=' . rawurlencode($s_page);
    } else {
        $s_id = ($id == '') ? '' : '&amp;id=' . $id;
        $url  = $script . '?cmd=easyedit&amp;page=' . rawurlencode($s_page) . $s_id;
    }
    $atag  = '<a' . $class . ' href="' . $url . '" title="' . $title . '">';
    static $atags = '</a>';

    if ($ispage) {
        // Normal edit link
        return $atag . $icon . $s_label . $atags;
    } else {
        // Dangling edit link
        return '<span class="noexists">' . $atag . $icon . $atags .
            $s_label . $atag . '?' . $atags . '</span>';
    }
}

// Write, add, or insert new comment
function plugin_easyedit_write()
{
    global $vars;
    global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
    global $notimeupdate, $_msg_invalidpass, $do_update_diff_table;

    $page   = isset($vars['page'])   ? $vars['page']   : '';
    $add    = isset($vars['add'])    ? $vars['add']    : '';
    $digest = isset($vars['digest']) ? $vars['digest'] : '';
    //EasyEdit
    require_once(EASYEDIT_LIB_PATH . 'guiedit/htmlv2wiki.php');
    $vars['msg'] = htmlv2wiki($vars['msg']);
    //--------
    $vars['msg'] = preg_replace(PLUGIN_EASYEDIT_FREEZE_REGEX, '', $vars['msg']);
    $msg = & $vars['msg']; // Reference

    $retvars = array();

    // Collision Detection
    $oldpagesrc = join('', get_source($page));
    $oldpagemd5 = md5($oldpagesrc);
    if ($digest !== $oldpagemd5) {
        $vars['digest'] = $oldpagemd5; // Reset

        $original = isset($vars['original']) ? $vars['original'] : '';
        $old_body = remove_author_info($oldpagesrc);
        list($postdata_input, $auto) = do_update_diff($old_body, $msg, $original);

        $retvars['msg' ] = $_title_collided;
        $retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided) . "\n";
        $retvars['body'] .= $do_update_diff_table;
        $retvars['body'] .= easyedit_form($page, $postdata_input, $oldpagemd5, false);
        return $retvars;
    }

    // Action?
    if ($add) {
        // Add
        if (isset($vars['add_top']) && $vars['add_top']) {
            $postdata  = $msg . "\n\n" . @join('', get_source($page));
        } else {
            $postdata  = @join('', get_source($page)) . "\n\n" . $msg;
        }
    } else {
        // Edit or Remove
        $postdata = & $msg; // Reference
    }

    // NULL POSTING, OR removing existing page
    if ($postdata === '') {
        page_write($page, $postdata);
        $retvars['msg' ] = $_title_deleted;
        $retvars['body'] = str_replace('$1', htmlsc($page), $_title_deleted);
        return $retvars;
    }

    // $notimeupdate: Checkbox 'Do not change timestamp'
    $notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
    if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
        // Enable only administrator & password error
        $retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
        $retvars['body'] .= easyedit_form($page, $msg, $digest, false);
        return $retvars;
    }

    page_write($page, $postdata, $notimeupdate != 0 && $notimestamp);
    pkwk_headers_sent();
    header('Location: ' . get_page_uri($page, PKWK_URI_ROOT));
    exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_easyedit_cancel()
{
    global $vars;
    pkwk_headers_sent();
    header('Location: ' . get_page_uri($vars['page'], PKWK_URI_ROOT));
    exit;
}

/**
 * Setup initial pages
 */
function plugin_easyedit_setup_initial_pages()
{
    global $autoalias;

    // Related: Rename plugin
    if (exist_plugin('rename') && function_exists('plugin_rename_setup_initial_pages')) {
        plugin_rename_setup_initial_pages();
    }
    // AutoTicketLinkName page
    init_autoticketlink_def_page();
    // AutoAliasName page
    if ($autoalias) {
        init_autoalias_def_page();
    }
}
function easyedit_form($page, $postdata, $digest = false, $b_template = true)
{
    global $vars, $rows, $cols;
    global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
    global $_btn_template, $_btn_load, $load_template_func;
    global $notimeupdate;
    global $_msg_edit_cancel_confirm, $_msg_edit_unloadbefore_message;
    global $rule_page;
    //CKEditor & EasyEdit
    //$EASYEDIT_LIB_PATH = EASYEDIT_LIB_PATH;
    $PLUGIN_EASYEDIT_SKIN_FILE = PLUGIN_EASYEDIT_SKIN_FILE;
    $upload_dir_url = get_script_uri() . EASYEDIT_LIB_PATH . 'kcfinder/upload/';
    $ckeditor_path = CKEDITOR_PATH;
    $kcfinder = var_export(PLUGIN_EASYEDIT_ENABLE_KCFINDER, true);

    $paraedit_script = '';
    if (isset($vars['id']) && $vars['id'] != 0 && PLUGIN_EASYEDIT_SECEDIT) {
        global $jquery, $head_tags;
        if (!isset($jquery)) {
            $jquery = true;
            $head_tags[] = '<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>';
        }
        $id = (int) $vars['id'] - 1;
        $paraedit_script = <<<EOD
        <script>
            window.addEventListener('load', (e) => {
                let interval = setInterval(function() {
                    var frames = document.querySelectorAll(".cke_wysiwyg_frame");
                    if (typeof frames[0] != "undefined") {
                        var frameDoc = frames[0].contentDocument || frames[0].contentWindow.document;
                        var headingTags = frameDoc.querySelectorAll('h1, h2, h3, h4, h5, h6');
                        headingTags[{$id}].scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                          });
                        clearInterval(interval);
                    }
                }, 150);
            });
        </script>
EOD;
    }

    $ckeditor = <<<EOD
    <div id="editor"></div>
    <script>var guiedit_skin_dir = "{$PLUGIN_EASYEDIT_SKIN_FILE}";var kcfinder = {$kcfinder};var upload_dir_url = '{$upload_dir_url}'</script>
    <script src="{$ckeditor_path}"></script>
    {$paraedit_script}
EOD;
    //--------
    $script = get_base_uri();
    if ($digest === false) {
        $digest = md5(join('', get_source($page)));
    }
    $refer = $template = '';
    $addtag = $add_top = '';
    if (isset($vars['add'])) {
        global $_btn_addtop;
        $addtag  = '<input type="hidden" name="add"    value="true" />';
        $add_top = isset($vars['add_top']) ? ' checked="checked"' : '';
        $add_top = '<input type="checkbox" name="add_top" ' .
            'id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
            '  <label for="_edit_form_add_top">' .
                '<span class="small">' . $_btn_addtop . '</span>' .
            '</label>';
    }
    if ($load_template_func && $b_template) {
        $template_page_list = get_template_page_list();
        $tpages = array(); // Template pages
        foreach ($template_page_list as $p) {
            $ps = htmlsc($p);
            $tpages[] = '   <option value="' . $ps . '">' . $ps . '</option>';
        }
        if (count($template_page_list) > 0) {
            $s_tpages = join("\n", $tpages);
        } else {
            $s_tpages = '   <option value="">(no template pages)</option>';
        }
        $template = <<<EOD
		<select name="template_page">
		<option value="">-- {$_btn_template} --</option>
		{$s_tpages}
		</select>
	   <input type="submit" name="template" value="$_btn_load" accesskey="r" />
	   <br />
EOD;

        if (isset($vars['refer']) && $vars['refer'] != '') {
            $refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";
        }
    }

    $r_page      = rawurlencode($page);
    $s_page      = htmlsc($page);
    $s_digest    = htmlsc($digest);
    //$s_postdata  = htmlsc($refer . $postdata); → 不具合の原因
    $s_postdata  = $refer . $postdata;

    $s_original  = isset($vars['original']) ? htmlsc($vars['original']) : $s_postdata;
    $b_preview   = isset($vars['preview']); // TRUE when preview
    $btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;
    require_once(EASYEDIT_LIB_PATH . 'guiedit/wiki2htmlv.php');

    $s_postdata = guiedit_convert_html($s_postdata);

    $add_notimestamp = '';
    if ($notimeupdate != 0) {
        global $_btn_notchangetimestamp;
        $checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';
        if ($notimeupdate == 2) {
            $add_notimestamp = '   ' .
                '<input type="password" name="pass" size="12" />' . "\n";
        }
        $add_notimestamp = '<input type="checkbox" name="notimestamp" ' .
            'id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' . "\n" .
            '   ' . '<label for="_edit_form_notimestamp"><span class="small">' .
            $_btn_notchangetimestamp . '</span></label>' . "\n" .
            $add_notimestamp .
            '&nbsp;';
    }
    $h_msg_edit_cancel_confirm = htmlsc($_msg_edit_cancel_confirm);
    $h_msg_edit_unloadbefore_message = htmlsc($_msg_edit_unloadbefore_message);
    $body = <<<EOD
	<div class="edit_form">
	<form action="$script" method="post" class="_plugin_easyedit_easyedit_form" style="margin-bottom:0;">
	$template
	$addtag
	<input type="hidden" name="cmd"    value="easyedit" />
	<input type="hidden" name="page"   value="$s_page" />
	<input type="hidden" name="digest" value="$s_digest" />
	<input type="hidden" id="_msg_edit_cancel_confirm" value="$h_msg_edit_cancel_confirm" />
	<input type="hidden" id="_msg_edit_unloadbefore_message" value="$h_msg_edit_unloadbefore_message" />
	<textarea id="editor" class="ckeditor" name="msg" rows="$rows" cols="$cols">$s_postdata</textarea>
	<br />
	<div style="float:left;">
	 <input type="submit" name="preview" value="$btn_preview" accesskey="p" />
	 <input type="submit" name="write"   value="$_btn_update" accesskey="s" />
	 $add_top
	 $add_notimestamp
	</div>
	<textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
   </form>
   <form action="$script" method="post" class="_plugin_edit_cancel" style="margin-top:0;">
	<input type="hidden" name="cmd"    value="edit" />
	<input type="hidden" name="page"   value="$s_page" />
	<input type="submit" name="cancel" value="$_btn_cancel" accesskey="c" />
   </form>
   </div>
   {$ckeditor}
EOD;

    $body .= '<ul><li><a href="' .
        get_page_uri($rule_page) .
        '" target="_blank">' . $_msg_help . '</a></li></ul>';
    return $body;
}

// from secedit.inc.php (GPLv2 2009-02-06 13:11:32Z lunt)
// seceditの関数をEasyEdit用へ少し改造したものです。

// 見出し編集用 (seceditやparaeditと併用可能)
function plugin_easyedit_heading_add_link(&$string, &$id)
{
    if (!PLUGIN_EASYEDIT_SECEDIT) {
        return $string;
    }
    global $vars;

    $page = isset($vars['page']) ? $vars['page'] : '';
    list($dummy, $callcount, $secid) = explode('_', $id);
    unset($dummy);
    if (!plugin_easyedit_heading_should_display_editlink($page, (int)$callcount)) {
        return $string;
    }

    $secid = '&amp;id=' . strval($secid + 1);
    if ($callcount > 1) {
        return $string;
    }

    $link  = get_script_uri() . '?cmd=easyedit&amp;page=' . rawurlencode($page) . $secid;

    return str_replace(
        array('$1', '$2'),
        array($string, $link),
        PLUGIN_EASYEDIT_SECEDIT_LINK_STYLE
    );
}

function plugin_easyedit_heading_should_display_editlink($page, $callcount)
{
    global $vars, $retvars;
    static $is_editable;

    if (PKWK_READONLY) {
        return false;
    }

    if (! (isset($vars['cmd']) && $vars['cmd'] === 'read' && ! $retvars['body'])) {
        return false;
    }

    if ($callcount > 1 && function_exists('plugin_menu_convert')) {
        return false;
    }

    if (! isset($is_editable[$page])) {
        $is_editable[$page] = check_editable($page, false, false);
    }
    if (! $is_editable[$page]) {
        return false;
    }

    if ($callcount === 1 || $vars['page'] !== $page) {
        return true;
    }
    return false;
}
