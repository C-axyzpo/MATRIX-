<?php
$uid = $_POST['uid'] ?? '';
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB error");

// hanapin student record
$sql = "SELECT name, CONCAT(grade,' ',section) AS section, image_filename 
        FROM users 
        WHERE uid='$uid' LIMIT 1";
$res = $conn->query($sql);

// default response
$data = [
    "name" => "Unknown",
    "section" => "",
    "image_url" => ""
];

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $data["name"] = $row["name"];
    $data["section"] = $row["section"];

    // kung may naka-save na image filename, gawin siyang full URL
    if (!empty($row["image_filename"])) {
        $data["image_url"] = "http://192.168.1.87/rfid_project/uploads/" . $row["image_filename"];
    }
}

// send as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
