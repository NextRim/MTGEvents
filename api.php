<?php

	include "../mydb.php";
	
	echo 'START <br>';
	
	if( isset($_POST['type']) && $_POST['type'] == 'savechange' &&  isset($_POST['m_id']) &&  isset($_POST['who']) && in_array($_POST['who'], ['points','order']) &&  isset($_POST['step'])  &&  isset($_POST['val'])  ){
		
		$query = "UPDATE `event` SET `". $_POST['who'] ."` = '". (int)$_POST['val'] ."' WHERE member_id = '". (int)$_POST['m_id'] ."' and step = '". (int)$_POST['step'] ."' ";
		$result = mysql_query ($query)
			or die ("Error DB: ".mysql_error());
		
		echo "UPDATE `event` SET `". $_POST['who'] ."` = '". (int)$_POST['val'] ."' WHERE member_id = '". (int)$_POST['m_id'] ."' and step = '". (int)$_POST['step'] ."' ";
		echo '<br>OK <br>';
	}
	
	echo 'END <br>';
	
?>