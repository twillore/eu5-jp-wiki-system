<?php
/**
 * PukiWiki notification for Discord
 * (Based on the PukiWiki Plus! notification for slack by karia and Sho Sawada)
 */

class discord {
  function notice($msg, $username = 'PukiWiki更新通知') {
    // ▼▼▼ ここにDiscordで取得したWebhook URLを貼り付け ▼▼▼
    $webhook_url = 'https://discord.com/api/webhooks/1434147904494370837/oKsLmW5aHbzcnTOGxJDeT5RtnljCfUpwyFqcYDk3axHp8qaWjvB2Gp8Y9sPXzdhGRfp3';
    // ▲▲▲ ここまで ▲▲▲

    // Discordに送信するデータを作成
    $payload = json_encode([
        'username' => $username, // 通知時に表示されるボット名
        'content'  => $msg,       // 通知メッセージ本文
        // 'avatar_url' => 'アイコン画像のURL', // 必要であれば指定
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 環境によって必要

    $response = curl_exec($ch);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 204) {
      // Discordへの送信が成功するとステータスコード204が返る
      error_log('Discord webhook error: ' . $response);
    }

    curl_close($ch);
  }
}
?>