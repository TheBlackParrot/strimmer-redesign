<?php
$strimmerVersion = "0.17.1-2";
if (!@include(dirname(dirname(__FILE__)) . "/config.php"))
{
	include dirname(__FILE__) . "/setup-config.php";
	exit;
}
