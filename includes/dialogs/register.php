<?php
	include dirname(dirname(__FILE__)) . "/session.php";
	include dirname(dirname(__FILE__)) . "/settings.php";
?>

<span class="header">Register for <?php echo $prog_title; ?></span>
<form action="includes/register.php" method="POST">
	<input name="username" type="text" placeholder="Username"/><br/>
	<input name="email" type="text" placeholder="Email"/><br/>
	<input name="password" type="password" placeholder="Password"/><br/>
	<input type="submit" value="Submit"/>
</form>