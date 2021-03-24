<?php
error_log("WEBHOOK");

$input = file_get_contents('php://input');
$post = json_decode($input);
file_put_contents(time()."-req.txt", print_r($post,true));

?>

