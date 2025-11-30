<?php
// PukiWiki - Yet another WikiWikiWeb clone
//
// safehtml.inc.php - Allow only whitelisted HTML tags with URL validation (v2)
// License: GPL v2 or (at your option) any later version

function plugin_safehtml_convert()
{
    if (func_num_args() == 0) {
        return ''; // No content
    }

    $args = func_get_args();
    $body = array_pop($args);

    // --- ▼▼▼ ここで許可するドメインを指定します ▼▼▼ ---
    $allowed_domain = 'https://lookerstudio.google.com/';
    // --- ▲▲▲ ここまで ▲▲▲ ---

    // PukiWikiによって変換されたHTMLエンティティを元に戻す
    $body = htmlspecialchars_decode($body, ENT_QUOTES);

    // 許可する基本タグ（iframe以外）
    $allowed_tags = '<div><span><p><b><i><u><strong><em>';

    // まず、iframe以外の不要なタグを除去
    $body = strip_tags($body, $allowed_tags . '<iframe>');

    // 次に、iframeタグのsrc属性を検証する
    $sanitized_body = preg_replace_callback(
        '/<iframe(.*?)><\/iframe>/is',
        function ($matches) use ($allowed_domain) {
            $iframe_tag = $matches[0];
            $attributes = $matches[1];

            if (preg_match('/src\s*=\s*["\'](.+?)["\']/i', $attributes, $src_match)) {
                $url = $src_match[1];

                if (strpos($url, $allowed_domain) === 0) {
                    // 許可されたドメインなら、iframeタグをそのまま返す（HTMLとして実行される）
                    return $iframe_tag;
                }
            }
            
            return '<div style="border:2px solid red; padding:10px; background-color:#ffe0e0;"><strong>[エラー]</strong> 許可されていない埋め込みコンテンツです。</div>';
        },
        $body
    );

    return $sanitized_body;
}
?>