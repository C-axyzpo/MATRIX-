<?php
$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) die("DB Failed");

$laptop_no = $_REQUEST['laptop_no'] ?? 0;
$uid = $_REQUEST['uid'] ?? '';

file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Laptop: [$laptop_no], UID: [$uid]\n", FILE_APPEND);

if(!$laptop_no || !$uid) die("Missing data");

// Lookup user name from users table
$user = $conn->query("SELECT name FROM users WHERE uid='$uid' LIMIT 1");
if(!$user || $user->num_rows==0) die("User not found");
$name = $user->fetch_assoc()['name'];

// Check laptop status
$res = $conn->query("SELECT status FROM laptops WHERE laptop_no=$laptop_no LIMIT 1");
if($res && $row=$res->fetch_assoc()){
    if($row['status']=='Available'){
        $conn->query("UPDATE laptops 
                      SET status='Borrowed', borrower_name='$name', borrow_time=NOW() 
                      WHERE laptop_no=$laptop_no");

        // Line1 = Name, Line2 = Borrowed Laptop #
        echo $name . "\nBorrowed L" . $laptop_no;
    } else {
        $conn->query("UPDATE laptops 
                      SET status='Available', borrower_name=NULL, borrow_time=NULL 
                      WHERE laptop_no=$laptop_no");

        // Line1 = Name, Line2 = Returned Laptop #
        echo $name . "\nReturned L" . $laptop_no;
    }
} else {
    echo "Laptop not found.";
}
?>
