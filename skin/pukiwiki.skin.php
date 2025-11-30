<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// pukiwiki.skin.php
// Copyright
//   2002-2021 PukiWiki Development Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki default skin

// ------------------------------------------------------------
// Settings (define before here, if you want)

// Set site identities
$_IMAGE['skin']['logo']     = 'logo.png';
$_IMAGE['skin']['favicon']  = ''; // Sample: 'image/favicon.ico';

// SKIN_DEFAULT_DISABLE_TOPICPATH
//   1 = Show reload URL
//   0 = Show topicpath
if (! defined('SKIN_DEFAULT_DISABLE_TOPICPATH'))
	define('SKIN_DEFAULT_DISABLE_TOPICPATH', 1); // 1, 0

// Show / Hide navigation bar UI at your choice
// NOTE: This is not stop their functionalities!
if (! defined('PKWK_SKIN_SHOW_NAVBAR'))
	define('PKWK_SKIN_SHOW_NAVBAR', 1); // 1, 0

// Show / Hide toolbar UI at your choice
// NOTE: This is not stop their functionalities!
if (! defined('PKWK_SKIN_SHOW_TOOLBAR'))
	define('PKWK_SKIN_SHOW_TOOLBAR', 1); // 1, 0

// ------------------------------------------------------------
// Code start

// Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');
if (! isset($_LANG)) die('$_LANG is not set');
if (! defined('PKWK_READONLY')) die('PKWK_READONLY is not set');

$lang  = & $_LANG['skin'];
$link  = & $_LINK;
$image = & $_IMAGE['skin'];
$rw    = ! PKWK_READONLY;

// MenuBar
$menu = exist_plugin_convert('menu') ? do_plugin_convert('menu') : do_plugin_convert('menu');
// RightBar
$rightbar = FALSE;
if (arg_check('read') && exist_plugin_convert('rightbar')) {
	$rightbar = do_plugin_convert('rightbar');
}
// ------------------------------------------------------------
// Output

// HTTP headers
pkwk_common_headers();
header('Cache-control: no-cache');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=' . CONTENT_CHARSET);

?>
<!DOCTYPE html>
<html lang="<?php echo LANG ?>">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CONTENT_CHARSET ?>" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php if ($nofollow || ! $is_read)  { ?> <meta name="robots" content="NOINDEX,NOFOLLOW" /><?php } ?>
<?php if ($html_meta_referrer_policy) { ?> <meta name="referrer" content="<?php echo htmlsc(html_meta_referrer_policy) ?>" /><?php } ?>

 <title><?php echo $title ?> - <?php echo $page_title ?></title>

 <link rel="SHORTCUT ICON" href="<?php echo $image['favicon'] ?>" />
 <link rel="stylesheet" type="text/css" href="<?php echo SKIN_DIR ?>pukiwiki.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $link['rss'] ?>" /><?php // RSS auto-discovery ?>
 <script type="text/javascript" src="skin/main.js" defer></script>
 <script type="text/javascript" src="skin/search2.js" defer></script>




<?php echo $head_tag ?>
<?php
// =================================================================
// Google Analytics Tracking Code
// -----------------------------------------------------------------
// 目的: サイトのアクセス状況を解析し、グラフ化するために導入。
//       訪問者の行動を匿名で収集し、サイト改善のデータとします。
// 参照元: https://analytics.google.com/
// 担当者: ゆいかせ (2025-09-23)
// =================================================================
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QL8KNGMKME"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-QL8KNGMKME');
</script>

</head>
<body>
<div id="menu-overlay"></div> 
<?php echo $html_scripting_data ?>
<!-- [CUSTOM] Mobile Menu Button ゆいかせ2025/09/23 -->
<div id="menu-button">☰</div>

<div id="header">
 <a href="<?php echo $link['top'] ?>"><img id="logo" src="<?php echo IMAGE_DIR . $image['logo'] ?>" width="160" height="70" alt="EU5日本語wiki(" title="EU5日本語wiki" /></a>

 <h1 class="title"><?php echo $page ?></h1>

<?php if ($is_page) { ?>
 <?php if(SKIN_DEFAULT_DISABLE_TOPICPATH) { ?>
   <a href="<?php echo $link['canonical_url'] ?>"><span class="small"><?php echo $link['canonical_url'] ?></span></a>
 <?php } else { ?>
   <span class="small">
   <?php require_once(PLUGIN_DIR . 'topicpath.inc.php'); echo plugin_topicpath_inline(); ?>
   </span>
 <?php } ?>
<?php } ?>

</div>

<div id="navigator">
<?php if(PKWK_SKIN_SHOW_NAVBAR) { ?>
<?php
function _navigator($key, $value = '', $javascript = ''){
	$lang = & $GLOBALS['_LANG']['skin'];
	$link = & $GLOBALS['_LINK'];
	if (! isset($lang[$key])) { echo 'LANG NOT FOUND'; return FALSE; }
	if (! isset($link[$key])) { echo 'LINK NOT FOUND'; return FALSE; }

	echo '<a href="' . $link[$key] . '" ' . $javascript . '>' .
		(($value === '') ? $lang[$key] : $value) .
		'</a>';

	return TRUE;
}
?>
 [ <?php _navigator('top') ?> ] &nbsp;

<?php if ($is_page) { ?>
 [
 <?php if ($rw) { ?>
	<?php _navigator('edit') ?> |
	<?php if (exist_plugin("easyedit")) { echo '<a href="./?cmd=easyedit&page=' . $_page . '">編集〔GUI〕</a>'; } ?> |
	<?php if ($is_read && $function_freeze) { ?>
		<?php (! $is_freeze) ? _navigator('freeze') : _navigator('unfreeze') ?> |
	<?php } ?>
 <?php } ?>

 <?php _navigator('diff') ?>
 <?php if ($do_backup) { ?>
	| <?php _navigator('backup') ?>
 <?php } ?>
 <?php if ($rw && (bool)ini_get('file_uploads')) { ?>
	| <?php _navigator('upload') ?>
 <?php } ?>
 | <?php _navigator('reload') ?>
 ] &nbsp;
<?php } ?>

 [
 <?php if ($rw) { ?>
	<?php _navigator('new') ?> |
 <?php } ?>
   <?php _navigator('list') ?>
 <?php if (arg_check('list')) { ?>
	| <?php _navigator('filelist') ?>
 <?php } ?>
 | <?php _navigator('search') ?>
 | <?php _navigator('recent') ?>
 | <?php _navigator('help')   ?>
 <?php if ($enable_login) { ?>
 | <?php _navigator('login') ?>
 <?php } ?>
 <?php if ($enable_logout) { ?>
 | <?php _navigator('logout') ?>
 <?php } ?>
 ]
<?php } // PKWK_SKIN_SHOW_NAVBAR ?>
</div>

<?php echo $hr ?>



<?php
// --- 目次抽出処理 ---
$toc_html = ''; // 目次を格納する変数を初期化
$toc_exists = false; // 目次の存在フラグ

// 本文($body)に目次(<div class="contents">)が含まれているかチェック
if (preg_match('/<div class="contents">.*?<\/div>/s', $body, $matches)) {
    $toc_exists = true;
    $toc_html = $matches[0]; // マッチした目次全体を$toc_htmlに保存
    $body = str_replace($toc_html, '', $body); // 本文($body)から目次を削除

    // Pukiwikiの目次タイトル(h2)を、CSSで扱いやすいdivに変更
    $toc_html = preg_replace('/<h2 class="contents_header">(.*?)<\/h2>/s', '<div class="toc-title">$1</div>', $toc_html);
    
    // 不要なアンカーを削除
    $toc_html = preg_replace('/<a id="_contents_"><\/a>/s', '', $toc_html);
    $toc_html = preg_replace('/<#_contents_>/s', '', $toc_html);
}
?>

<?php if ($menu !== FALSE) { ?>
<!-- ボディ部の構成を変更 2019/05/19 -->
<div id="contents">
    <!-- メニューページ部 2019/07/12 -->
    <div id="menubar">
        <?php echo $menu ?>
    </div>
    <!-- コンテンツページ部 2019/07/12 -->
    <div id="body">
        <?php echo $body; // 目次が除去された本文 ?>
        <!-- コンテンツページに「注釈」を移動 2019/07/12 -->
        <?php if ($notes != '') { ?>
            <div id="note"><?php echo $notes ?></div>
        <?php } ?>
    </div>

    <?php
    // --- 目次が存在する場合のみ、右側目次エリアを表示 ---
    if ($toc_exists) {
        echo '<div id="toc-right">';
        echo '  <div class="toc-inner">';
        echo $toc_html; // 抜き出した目次を表示
        echo '  </div>';
        echo '</div>';
    }
    ?>
</div>
<?php } else { ?>
<div id="body"><?php echo $body ?></div>
<?php } ?>




<?php if ($attaches != '') { ?>
<div id="attach">
<?php echo $hr ?>
<?php echo $attaches ?>
</div>
<?php } ?>

<?php echo $hr ?>

<?php if (PKWK_SKIN_SHOW_TOOLBAR) { ?>
<!-- Toolbar -->
<div id="toolbar">
<?php

// Set toolbar-specific images
$_IMAGE['skin']['reload']   = 'reload.png';
$_IMAGE['skin']['new']      = 'new.png';
$_IMAGE['skin']['edit']     = 'edit.png';
$_IMAGE['skin']['freeze']   = 'freeze.png';
$_IMAGE['skin']['unfreeze'] = 'unfreeze.png';
$_IMAGE['skin']['diff']     = 'diff.png';
$_IMAGE['skin']['upload']   = 'file.png';
$_IMAGE['skin']['copy']     = 'copy.png';
$_IMAGE['skin']['rename']   = 'rename.png';
$_IMAGE['skin']['top']      = 'top.png';
$_IMAGE['skin']['list']     = 'list.png';
$_IMAGE['skin']['search']   = 'search.png';
$_IMAGE['skin']['recent']   = 'recentchanges.png';
$_IMAGE['skin']['backup']   = 'backup.png';
$_IMAGE['skin']['help']     = 'help.png';
$_IMAGE['skin']['rss']      = 'rss.png';
$_IMAGE['skin']['rss10']    = & $_IMAGE['skin']['rss'];
$_IMAGE['skin']['rss20']    = 'rss20.png';
$_IMAGE['skin']['rdf']      = 'rdf.png';

function _toolbar($key, $x = 20, $y = 20){
	$lang  = & $GLOBALS['_LANG']['skin'];
	$link  = & $GLOBALS['_LINK'];
	$image = & $GLOBALS['_IMAGE']['skin'];
	if (! isset($lang[$key]) ) { echo 'LANG NOT FOUND';  return FALSE; }
	if (! isset($link[$key]) ) { echo 'LINK NOT FOUND';  return FALSE; }
	if (! isset($image[$key])) { echo 'IMAGE NOT FOUND'; return FALSE; }

	echo '<a href="' . $link[$key] . '">' .
		'<img src="' . IMAGE_DIR . $image[$key] . '" width="' . $x . '" height="' . $y . '" ' .
			'alt="' . $lang[$key] . '" title="' . $lang[$key] . '" />' .
		'</a>';
	return TRUE;
}
?>
 <?php _toolbar('top') ?>

<?php if ($is_page) { ?>
 &nbsp;
 <?php if ($rw) { ?>
	<?php _toolbar('edit') ?>
	<?php if ($is_read && $function_freeze) { ?>
		<?php if (! $is_freeze) { _toolbar('freeze'); } else { _toolbar('unfreeze'); } ?>
	<?php } ?>
 <?php } ?>
 <?php _toolbar('diff') ?>
<?php if ($do_backup) { ?>
	<?php _toolbar('backup') ?>
<?php } ?>
<?php if ($rw) { ?>
	<?php if ((bool)ini_get('file_uploads')) { ?>
		<?php _toolbar('upload') ?>
	<?php } ?>
	<?php _toolbar('copy') ?>
	<?php _toolbar('rename') ?>
<?php } ?>
 <?php _toolbar('reload') ?>
<?php } ?>
 &nbsp;
<?php if ($rw) { ?>
	<?php _toolbar('new') ?>
<?php } ?>
 <?php _toolbar('list')   ?>
 <?php _toolbar('search') ?>
 <?php _toolbar('recent') ?>
 &nbsp; <?php _toolbar('help') ?>
 &nbsp; <?php _toolbar('rss10', 36, 14) ?>
</div>
<?php } // PKWK_SKIN_SHOW_TOOLBAR ?>

<?php if ($lastmodified != '') { ?>
<div id="lastmodified">Last-modified: <?php echo $lastmodified ?></div>
<?php } ?>

<?php if ($related != '') { ?>
<div id="related">Link: <?php echo $related ?></div>
<?php } ?>

<div id="footer">
 Site admin: <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a>
 <p>
 <?php echo S_COPYRIGHT ?>.
 Powered by PHP <?php echo PHP_VERSION ?>. HTML convert time: <?php echo elapsedtime() ?> sec.
 </p>
</div>





<?php
// --- 目次が存在する場合のみ、表示切り替えボタンを生成 ---
if ($toc_exists) {
    echo '<div id="toc-toggle-button"><span class="icon">≣</span><span class="label">目次</span></div>';
}
?>

<script>
    (function() {
        const menuButton = document.getElementById('menu-button');
        const tocButton = document.getElementById('toc-toggle-button');
        const overlay = document.getElementById('menu-overlay');
        const body = document.body;

        if (menuButton) {
            menuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                body.classList.toggle('menu-open');
                body.classList.remove('toc-open');
            });
        }

        if (tocButton) {
            tocButton.addEventListener('click', function(e) {
                e.stopPropagation();

                // ★変更点：画面幅をチェックして動作を切り替える
                if (window.innerWidth <= 767) {
                    // 767px以下（モバイル）なら、これまで通りオーバーレイ
                    body.classList.toggle('toc-open');
                    body.classList.remove('menu-open');
                } else {
                    // 768px以上（タブレット）なら、左右を入れ替えるクラスをトグル
                    body.classList.toggle('toc-swapped');
                }
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                body.classList.remove('menu-open');
                body.classList.remove('toc-open');
            });
        }
    })();
</script>






</body>
</html>
