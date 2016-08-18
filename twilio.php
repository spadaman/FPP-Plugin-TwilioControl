#!/usr/bin/php
<?php
$fileName1="/tmp/file.txt";
$fileName2="/tmp/file2.txt";
file_put_contents($fileName1, serialize($_POST));
$post = file_get_contents('php://input');
file_put_contents($fileName2, $post);
?>
