<?php
include dirname(__FILE__) . "/session.php";

echo ($_SESSION['login'] == TRUE) ? 1 : 0;