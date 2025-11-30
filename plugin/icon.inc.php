<?php
/*
 * &icon(key);
 * 共通素材ページの定義リスト (- key, file, size, option) を読み込む
 */

function plugin_icon_inline()
{
    $config_page = '共通素材'; // 定義ページ
    $default_size = '15x15'; 

    $args = func_get_args();
    $key  = isset($args[0]) ? trim($args[0]) : '';
    if ($key == '') return '';

    if (! is_page($config_page)) return '&icon(NoConfig);';
    $source = get_source($config_page);

    $target_file = '';
    $target_size = '';
    $target_opt  = 'nolink'; // ★デフォルトは「リンクなし」

    foreach ($source as $line) {
        // 正規表現を少し拡張：4つ目の要素(オプション)まで取る
        // 形式: - キー, ファイル, サイズ, オプション
        if (! preg_match('/^-\s*([^,]+),([^,]+)(?:,([^,]+))?(?:,([^,]+))?/', $line, $matches)) {
            continue;
        }

        if (trim($matches[1]) === $key) {
            $target_file = trim($matches[2]);
            $target_size = isset($matches[3]) && trim($matches[3]) !== '' ? trim($matches[3]) : $default_size;
            
            // ★4つ目に 'link' と書いてあったら nolink を解除する
            if (isset($matches[4]) && trim($matches[4]) == 'link') {
                $target_opt = ''; // 空文字にすればリンク有効になる
            }
            break;
        }
    }

    if ($target_file == '') return "&icon({$key}?);";

    if (! function_exists('plugin_ref_inline')) {
        require_once(PLUGIN_DIR . 'ref.inc.php');
    }
    
    // refに渡す引数を調整
    // サイズ指定がある場合、refの引数の順番に注意が必要ですが、
    // 一般的なref(ページ/画像, パラメータ, サイズ) の形式で渡します
    
    // パラメータ作成 (nolink または 空)
    $params = array();
    if ($target_opt !== '') $params[] = $target_opt;
    
    // ref(名前, nolink, サイズ) の形で渡す
    return plugin_ref_inline("$config_page/$target_file", $target_opt, $target_size);
}
?>