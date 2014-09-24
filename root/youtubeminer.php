<?php

define('IN_MYBB', 1);
require_once "./global.php";

add_breadcrumb("Youtube miner", "youtubeminer.php");

$data = $plugins->run_hooks('youtubeminer_start');
$previousLink = $data['previousLink'];
$nextLink = $data['nextLink'];
$youtubeGallery = $data['youtubeGallery'];
eval("\$youtubeminer = \"".$templates->get("youtubeminer")."\";");
output_page($youtubeminer);
?>