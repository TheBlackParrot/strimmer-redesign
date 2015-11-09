<?php
$strimmerVersion = "0.14.7-6";
if (!@include(dirname(dirname(__FILE__)) . "/config.php"))
{
	include dirname(__FILE__) . "/setup-config.php";
	exit;
}
