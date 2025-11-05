<?php
// send_message_helper.php
function sendMessageToPSID($psid, $text, $pageAccessToken) {
    $url = "https://graph.facebook.com/v21.0/me/messages?access_token=" . urlencode($pageAccessToken);
    $payload = [
        'recipient' => ['id' => $psid],
        'message'   => ['text' => $text]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['ok'=>false,'error'=>$err];
    return ['ok'=>true,'result'=>$result];
}
