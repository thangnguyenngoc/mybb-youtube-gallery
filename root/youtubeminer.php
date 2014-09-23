<?php

define('IN_MYBB', 1);
require_once "./global.php";

add_breadcrumb("Youtube miner", "youtubeminer.php");

eval("\$youtubeminer = \"".$templates->get("youtubeminer")."\";");
output_page($youtubeminer);
?>