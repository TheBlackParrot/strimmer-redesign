<?php
$strimmerVersion = "0.14.8-1";
if (!@include(dirname(dirname(__FILE__)) . "/config.php"))
{
	include dirname(__FILE__) . "/setup-config.php";
	exit;
}
