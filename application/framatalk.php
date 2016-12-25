<?php

$channel = $_REQUEST["channel"];

ob_start();

readfile("https://framatalk.org/$channel");

$content = ob_get_clean();

$content = str_replace("<head>", "<head><base href='https://framatalk.org/$channel'>", $content);

echo $content;

?>