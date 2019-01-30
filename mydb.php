<?php
	
	$link = mysql_connect('localhost','mtgevent_user', 'passsql!')
	or die ( ' ERROR 1. MySQL no connect ');
	@mysql_query("SET NAMES 'utf8' ");
	mysql_select_db ('mtgevent_db', $link)
	or die ( ' ERROR 2. MySQL selestet db ');
	
?>