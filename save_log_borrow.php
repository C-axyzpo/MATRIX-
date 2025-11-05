<?php
$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) die("DB Failed");

$laptop_no = $_POST['laptop_no'] ?? 0;
$uid = $_POST['uid'] ?? '';

if(!$laptop_no || !$uid) die("Missing data");

// Lookup user name from users table
$user = $conn->query("SELECT name FROM users WHERE uid='$uid' LIMIT 1");
if(!$user || $user->num_rows==0) die("User not found");
$name = $user->fetch_assoc()['name'];

// Check laptop
$res = $conn->query("SELECT status FROM laptops WHERE laptop_no=$laptop_no LIMIT 1");
if($res && $row=$res->fetch_assoc()){
    if($row['status']=='Available'){
        $conn->query("UPDATE laptops SET status='Borrowed', borrower_name='$name', borrow_time=NOW() WHERE laptop_no=$laptop_no");
        echo "Laptop $laptop_no borrowed by $name";
    } else {
        $conn->query("UPDATE laptops SET status='Available', borrower_name=NULL, borrow_time=NULL WHERE laptop_no=$laptop_no");
        echo "Laptop $laptop_no returned by $name";
    }
}
?>
