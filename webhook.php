<?php
// webhook.php
$VERIFY_TOKEN = "EAAV3Q1XhQtABPuAjnhxvoeJRxafKwRvPYP37tRxEUHjoqJ8PShoj0csokj6ifk70YsbE3Kunqgk9drXN9w3IbhIiRUrNILKGwwTnEKEBSBZCERS7sHEms6l1NxZC3XbyWZBGVtMz2kC1Fn2NAlBtrHz9qkWJF3qBjJDrCwlWST24ZC03MSQHTzemF4BAgEKcivGHRW0lDgZDZD";      // set the same string in Meta dashboard
$PAGE_ACCESS_TOKEN = "matrix123"; // from Meta developer > Messenger > Access Tokens
$ADMIN_PSID = ""; // your personal PSID to receive admin alerts (optional)

require 'send_message_helper.php';

// 1) Verification (GET from Meta)
if ($_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe') {

    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($token === $VERIFY_TOKEN) {
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        echo "Invalid verify token";
        exit;
    }
}

// 2) POST handler - incoming messages
$input = file_get_contents('php://input');
file_put_contents("webhook_log.txt", date("Y-m-d H:i:s") . " " . $input . PHP_EOL, FILE_APPEND);

$payload = json_decode($input, true);
if (empty($payload)) {
    http_response_code(200);
    exit;
}

// handle messaging events
if (!empty($payload['entry'][0]['messaging'])) {
    foreach ($payload['entry'][0]['messaging'] as $event) {
        $sender = $event['sender']['id'] ?? null;

        // handle text messages
        if (!empty($event['message']['text'])) {
            $text = trim($event['message']['text']);

            // REGISTER flow: "REGISTER <UID>"
            if (stripos($text, 'REGISTER') === 0) {
                $parts = preg_split('/\s+/', $text);
                if (isset($parts[1])) {
                    $uid = $parts[1];

                    // save to DB
                    $conn = new mysqli("localhost","root","","attendance");
                    if (!$conn->connect_error) {
                        $stmt = $conn->prepare("UPDATE students SET parent_psid = ? WHERE uid = ?");
                        $stmt->bind_param("ss", $sender, $uid);
                        $stmt->execute();
                        if ($stmt->affected_rows > 0) {
                            sendMessageToPSID($sender, "✅ Registration complete: you will get notifications for UID $uid.", $PAGE_ACCESS_TOKEN);
                            // optional admin notify
                            if (!empty($ADMIN_PSID)) {
                                sendMessageToPSID($ADMIN_PSID, "Parent PSID $sender registered for UID $uid", $PAGE_ACCESS_TOKEN);
                            }
                        } else {
                            sendMessageToPSID($sender, "Could not find UID $uid. Check and try: REGISTER <UID>", $PAGE_ACCESS_TOKEN);
                        }
                        $stmt->close();
                        $conn->close();
                    } else {
                        sendMessageToPSID($sender, "Server DB error.", $PAGE_ACCESS_TOKEN);
                    }
                } else {
                    sendMessageToPSID($sender, "Usage: REGISTER <UID> (e.g. REGISTER 7EE22E02)", $PAGE_ACCESS_TOKEN);
                }
            } else {
                // generic reply + forward to admin
                sendMessageToPSID($sender, "Thanks — to receive notifications send: REGISTER <UID>", $PAGE_ACCESS_TOKEN);
                if (!empty($ADMIN_PSID)) {
                    $forward = "Msg from parent PSID {$sender}: {$text}";
                    sendMessageToPSID($ADMIN_PSID, $forward, $PAGE_ACCESS_TOKEN);
                }
            }
        } // end message text
    } // end foreach
}

// always return 200
http_response_code(200);
echo "EVENT_RECEIVED";
