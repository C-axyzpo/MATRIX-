<?php
$url = "http://localhost/rfid_project/rfid_api.php";
$data = array('uid' => '123456'); // Change UID to one you added in users

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Server Response: " . $result;
?>
