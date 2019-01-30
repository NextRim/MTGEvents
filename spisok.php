<?php

	include "../mydb.php";
	
	if( isset($_POST['type']) && $_POST['type'] == 'addmembers' && isset($_POST['addmembers'])  ){
		$arrmember = split("\n", $_POST['addmembers'] );
		foreach ($arrmember as $memb) {
			$m = split("#", $memb );
			$name = $m[0];
				
			$query = "INSERT INTO `members` VALUES (NULL,'".str_replace('\'','\"',$name)."','0','0','1')";
			$result = mysql_query ($query)
				or die ("Error DB: ".mysql_error());
			
			$id_memb = mysql_insert_id();
			
			$point = '0';
			if ( isset( $m[1] ) ) $point = (int)trim($m[1]);
			$order = '0';
			if ( isset( $m[2] ) ) $order = (int)trim($m[2]);

			$query = "INSERT INTO `event` VALUES (NULL,'".$id_memb."','0','0','$point','$order')";
			$result = mysql_query ($query)
				or die ("Error DB: ".mysql_error());

		}
		
		
	}
	if( isset($_POST['type']) && $_POST['type'] == 'allclear' ){
		$query = "DELETE FROM `members`";
		$result = mysql_query ($query)
			or die ("Error DB: ".mysql_error());
		
		$query = "DELETE FROM `event`";
		$result = mysql_query ($query)
			or die ("Error DB: ".mysql_error());
	}
	
	if( isset($_POST['type']) && $_POST['type'] == 'remmember' && isset($_POST['member-id']) ){
		$query = "UPDATE `members` SET `status` = '0' WHERE member_id = '". (int)$_POST['member-id'] ."' ";
		$result = mysql_query ($query)
			or die ("Error DB: ".mysql_error());
	}
	
	function getmemgers() {
		$arrmembers = [];
		$query = "SELECT * FROM `members`";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
 		while ($member = mysql_fetch_array($result, MYSQL_ASSOC)){
			$arrmembers[] = array(
				'id'	 	=> $member['member_id'],
				'name'	=> $member['name'],
				'points'	=> $member['points'],
				'order'	=> $member['order'],
				'status'	=> $member['status'],
			);
		}
		
		return $arrmembers;
	}
	
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<title>Панель управления</title>

<link href="../css/bootstrap.min.css" rel="stylesheet">
<link href="../css/jquery-ui.css" rel="stylesheet">
<link href="../css/font-awesome.css" rel="stylesheet">
<link href="../css/template.css?v=27" rel="stylesheet">


<script src="../js/jquery-1.10.2.min.js"></script>
<script src="../js/jquery-ui.min.js"></script>


</head>
<body>
<div class="container">
<div class="masthead">
<h3 class="text-muted">Турнирная сетка <span class="admin">ADMIN</span></h3>
<ul class="nav nav-justified">
<li ><a href="./index.php">Столы</a></li>
<li ><a href="./standings.php">Стендинги</a></li>
<li class="active"><a href="./spisok.php">Список игроков</a></li>
<li><a href="#">Результаты</a></li>

</ul>
</div>

<div class="row">
	<div class="col-md-12">
	<div class="header">Список участников:</div>
	</div>
	
	
	<div class="col-md-6">
	<table class="table"><tbody>
		<?php 
		$members = getmemgers();
		if ( count($members) > 0 ){
			foreach( $members as $key => $member ){
				echo '<tr>';
				echo '<td>'. (1+$key) .'</td>';
				if ( $member['status'] != 0 ){
					echo '<td>'. $member['name'] .'</td>';
					echo '<td m_id="'. $member['id'] .'" class="butt"><i class="fa fa-2x fa-window-close"></i></td>';	
				} else {
					echo '<td class="status0">'. $member['name'] .'</td>';
					echo '<td></td>';
				}
				
				echo '</tr>';
			}
		}
		?>
		</tbody></table>
	</div>
	<div class="col-md-6">
	
		<form action="./spisok.php" method="post" onsubmit="return confirm('Очистить весь список участников и их результаты?');">
			<input type="hidden" name="type" value="allclear">
			<button type="submit" class="btn btn-primary">Очистить список</button>
		</form>
		<br>
		<form action="./index.php" method="post" onsubmit="return confirm('Сформировать сетку первого тура?');">
			<input type="hidden" name="type" value="calculation-step">
			<input type="hidden" name="step" value="1">
			<button type="submit" class="btn btn-primary">Сформировать сетку</button>
		</form>
		
	</div>
</div>
<div class="row" style="margin-top: 20px;">
	<div class="col-md-6">
		<form action="./spisok.php" method="post">
			<div class="form-group">
				<label for="exampleTextarea">Добавить участников</label>
				<textarea class="form-control" name="addmembers" rows="10"></textarea>
			</div>
			<input type="hidden" name="type" value="addmembers">
			<button type="submit" class="btn btn-primary">Добвить</button>
		</form>
	</div>

</div> 

<div style="height: 50px;"></div>

<form id="formsend" style="display:none;" method="POST" action="./spisok.php">
</form>
<script>
$('body').on('click', '.butt i', function(){
	$('#formsend').html('<input name="type" type="hidden" value="remmember"><input name="member-id" type="hidden" value="'+ $(this).parent().attr('m_id') +'">');
	$('#formsend').submit();
})	
	

</script>
</body>
</html>