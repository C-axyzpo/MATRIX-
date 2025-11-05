<?php
$file = 'uploads/latest.jpg';
file_put_contents($file, file_get_contents('php://input'));
echo shell_exec("python3 recognize.py $file");
?>
