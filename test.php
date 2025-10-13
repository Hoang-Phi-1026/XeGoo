<?php
$url = 'https://www.google.com/recaptcha/api/siteverify';
$result = file_get_contents($url);
var_dump($result);
?>