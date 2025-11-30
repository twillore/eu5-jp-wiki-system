<?php
// PukiWiki Redirect Plugin
// Usage: #jump(PageName)

function plugin_jump_convert()
{
    $args = func_get_args();
    if (empty($args)) return '';
    
    $page = trim($args[0]);
    $url  = get_page_uri($page);
    
    // ページが存在する場合のみ転送
    if (is_page($page)) {
        header("Location: $url");
        exit;
    }
    return "Jump to: [[$page]]";
}
?>