<?php


echo "<h1>Hi there Wiktor!</h1>";


$path = trim($_SERVER["REQUEST_URL"], '/');
$path = parse_url($path, PHP_URL_PATH);

echo $path;
