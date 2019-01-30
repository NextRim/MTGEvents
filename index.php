<?php

	include "../mydb.php";
	
	$step = '1';
	
	if( isset($_POST['type']) && $_POST['type'] == 'calculation-step' && isset($_POST['step'])){
		$step = $_POST['step'];
		
		if( $step == 1){
			$query = "DELETE FROM `event`";
			$result = mysql_query ($query)
				or die ("Error DB: ".mysql_error());
			$query = "UPDATE `members` SET `points` = '0', `order` = '0'";
			$result = mysql_query ($query)
				or die ("Error DB: ".mysql_error());
			
			$members = getmemgers();
			shuffle($members);
			$loop = 1;
			$table = 1;
			$tabcount = floor( count($members) / 4 );
			
			foreach ( $members as $index => $member ){
				
				$query = "INSERT INTO `event` VALUES (NULL,'".$member['id']."','$table','$step',NULL,NULL)";
				if ( $table == 0 ) $query = "INSERT INTO `event` VALUES (NULL,'".$member['id']."','$table','$step','1','4')";
				$result = mysql_query ($query)
					or die ("Error DB: ".mysql_error());
				
				$loop++;
				if ( $table != 0 && $loop > 4 ){
					$loop = 1;
					$table++;
				}
				if ( $table > $tabcount ) $table=0;
				
			}	
		}
		if ( $step > 1 ){
			$standings = [];
			$query = "DELETE FROM `event` WHERE step > ".( $step - 1 )." ";
			$result = mysql_query ($query)
				or die ("Error DB: ".mysql_error());
			
			$query = "SELECT e.*, m.name, m.status, SUM(e.points) as sum_p, SUM(e.order) as sum_o FROM `event` e INNER JOIN `members` m on e.member_id = m.member_id WHERE e.step <= ".(int)$step." GROUP BY e.member_id ORDER BY `sum_p` DESC, `sum_o` DESC  ";
			$result = mysql_query ($query)
				or die ("Error select TAB_TICKETS: ".mysql_error());
			while ($a = mysql_fetch_array($result, MYSQL_ASSOC)){
				$standings[ $a['member_id'] ] = array(
					'id'			=> $a['member_id'],
					'name'		=> $a['name'],
					'step'		=> $a['step'],
					'points'		=> $a['sum_p'],
					'order'		=> $a['sum_o'],
					'status'		=> $a['status']
				);
				if ( $a['status'] != 0 )	$order[ $a['sum_p'] ][] = $a['member_id'];
			}
			
			$sotid = "";
			foreach( $order as $o){
				shuffle($o);
				usort($o, "sortsteck");
				$sotid .= implode(',', $o).",";
			}
			$sotid = split(',',substr($sotid,0,-1));
			
			foreach( $sotid as $o){
				$sotedarr[] = $standings[$o];
			}
		
			$loop = 1;
			$table = 1;
			$tabcount = floor( count( getmemgers() ) / 4 );
			
			
			foreach ( $sotedarr as $member ){
						$query = "INSERT INTO `event` VALUES (NULL,'".$member['id']."','$table','$step',NULL,NULL)";
						if ( $table == 0 ) $query = "INSERT INTO `event` VALUES (NULL,'".$member['id']."','$table','$step','1','4')";
						$result = mysql_query ($query)
							or die ("Error DB: ".mysql_error());
						$loop++;
						if ( $table != 0 && $loop > 4 ){
							$loop = 1;
							$table++;
						}
						if ( $table > $tabcount ) $table=0;
			}
		}
	}
	
	function sortsteck($a, $b) {
			global $step;
			global $standings;

			$query = "SELECT *, GROUP_CONCAT(`member_id` SEPARATOR ',') as mach, GROUP_CONCAT(`points` SEPARATOR ',') as scop, GROUP_CONCAT(`order` SEPARATOR ',') as ord FROM `event` where `member_id` in ($a,$b) and step <= ".(int)$step." GROUP BY `step`,`table` HAVING COUNT(`step`) >= 2 AND COUNT(`table`) >= 2";
			$result = mysql_query ($query)
				or die ("Error select TAB_TICKETS: ".mysql_error());
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)){
				$t1 = split(',', $line['mach']);
				$t2 = split(',', $line['scop']);
				$t3 = split(',', $line['ord']);
					foreach( $t1 as $k => $t11){
						$order2[ $t11 ]['scop'] = $order2[ $t11 ]['scop'] + $t2[$k];
						$order2[ $t11 ]['ord'] = $order2[ $t11 ]['ord'] + $t3[$k];
					}
					
			}
			if ( $order2[$a]['scop'] == $order2[$b]['scop'] ) {
				if ( $order2[$a]['ord'] == $order2[$b]['ord'] ) {
					if( $standings[ $a ]['order'] == $standings[ $b ]['order']){
						//echo "$a(".$standings[ $a ]['name'].") равен order $b(".$standings[ $b ]['name'].") <br>";
						return 0;
					} else if($standings[ $a ]['order'] > $standings[ $b ]['order']){
						//echo "$a(".$standings[ $a ]['name'].") больше по очкамы выбытия $b(".$standings[ $b ]['name'].") <br>";
						return -1;	
					} else {
						return 1;
					}
				
				} else if ( $order2[$a]['ord'] > $order2[$b]['ord'] ) {
					return -1;
				} else {
					return 1;
				}
			}else	if ( $order2[$a]['scop'] > $order2[$b]['scop'] ) {
				return -1;
			}else {
				return 1;
			}
		
	}	
		
	function getmemgers() {
		$arrmembers = [];
		$query = "SELECT * FROM `members` WHERE status != '0' ";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
 		while ($member = mysql_fetch_array($result, MYSQL_ASSOC)){
			$arrmembers[ $member['member_id'] ] = array(
				'id'	 	=> $member['member_id'],
				'name'	=> $member['name'],
				'points'	=> $member['points'],
				'order'	=> $member['order'],
				'status'	=> $member['status']
			);
		}
		
		return $arrmembers;
	}
	
	function gettables( $step ) {
		$table = [];
			
		$query = "SELECT e.*, m.name, m.status FROM `event` e INNER JOIN `members` m on e.member_id = m.member_id WHERE e.step = ".(int)$step." ORDER BY `e`.`order` DESC ";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
 		while ($member = mysql_fetch_array($result, MYSQL_ASSOC)){
			
			$table[ $member['table'] ][] = array(
				'id'		=> $member['member_id'],
				'name'		=> $member['name'],
				'step'		=> $member['step'],
				'points'	=> $member['points'],
				'order'		=> $member['order'],
				'status'		=> $member['status']
				
			);
			
		}
		
		if ( count($table) == 0 ){
			for ($i = 0; $i < 5; $i++) { 
				$table[$i][0]['name' ] = 'ФИО_1';
				$table[$i][1]['name' ] = 'ФИО_2';
				$table[$i][2]['name' ] = 'ФИО_3';
				$table[$i][3]['name' ] = 'ФИО_4';
			}
			
		}
		return $table;
		
	}
	function getsteps( ) {
		$query = "SELECT MAX(step) as maxstep FROM event ";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
			
		$count = mysql_fetch_array ($result, MYSQL_ASSOC);
		
		return $count['maxstep'];
	}
	
	function getpoints( ) {
		$query = "SELECT member_id,SUM(points) as sumpoints FROM `event` GROUP BY member_id ORDER BY `sumpoints`  DESC";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
 		while ($member = mysql_fetch_array($result, MYSQL_ASSOC)){
			
			$points[ $member['member_id'] ] = $member['sumpoints'];
			
		}
	
		return $points;
	}
	
	
	
	$maxstep = getsteps();
	$step = $maxstep;
	if( isset($_GET['step']) && (int)$_GET['step'] <= $maxstep){
		$step = (int)$_GET['step'];	
		
	}
	
	$members = gettables( $step ); 
	$poins = getpoints();
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<title>Турнирная сетка Раунд №<?php echo $step;?></title>

<link href="../css/bootstrap.min.css" rel="stylesheet">
<link href="../css/jquery-ui.css" rel="stylesheet">
<link href="../css/font-awesome.css" rel="stylesheet">
<link href="../css/template.css?v=27" rel="stylesheet">


<script src="../js/jquery-1.10.2.min.js"></script>

<script src="../js/jquery-ui.min.js"></script>
<script src="../js/jquery.ui.touch-punch.min.js"></script>
<style>
@media print {
	@page {size:landscape;
	}
}
</style>
</head>
<body>
<div class="container">
<div class="masthead">
<h3 class="text-muted">Турнирная сетка <span class="admin">ADMIN</span></h3>
<ul class="nav nav-justified">
<li class="active"><a href="./index.php">Столы</a></li>
<li><a href="./standings.php">Стендинги</a></li>
<li><a href="./spisok.php">Список игроков</a></li>
<li><a href="#">Результаты</a></li>

</ul>
</div>

<div class="row">
	<div class="col-md-12 steps">
	<?php for($i=1; $i<=$maxstep; $i++){ ?>
	<div class="header"><a href="./index.php?step=<?php echo $i;?>" class="<?php if ($i == $step) echo 'activ';?>" >Раунд №<?php echo $i;?></a></div>
	<?php } ?>
	</div>
</div>
	
	<?php $cont_tables = max(array_keys($members)) +1;
	for( $i = 1; $i < $cont_tables ; $i++ ){ ?>
	<?php
	if( ($i+1) % 2 == 0) {
		echo '<div class="row">';
	}
	?>
	<div class="col-md-6">
		<div id="stol_<?php echo $i; ?>" class="stol">
			<div class="header"> Стол №<?php echo $i; ?></div>
			<div class="tabsheader">
			<span>ФИО</span><span>Очки</span><span>Выбытие</span>
			</div>
			<div class="tabsstat">
				<table class="table table-bordered"><tbody>
					<?php 
						foreach($members[$i] as $member){
							echo '<tr>';
							echo '<td class="name ';
							if ( $member['status'] == 0 ) echo 'status0 '; 
							echo '">'.$member['name'].'</td><td class="stat autosave" contenteditable  onChange="savechange( '.$member['id'].', \'points\', '.$step.', this)">'.$member['points'].'</td><td class="outs autosave" contenteditable onChange="savechange( '.$member['id'].', \'order\', '.$step.', this)">'.$member['order'].'</td>';
							echo '</tr>';
						}	
					?>
				</tbody></table>
			</div>
		</div>
	</div>
	<?php
	
	if( $i == ($cont_tables - 1) ||  ($i+1) % 2 != 0) {
		echo '</div>';
	}
	?>
	<?php	}  ?>


<div class="row">
	<div class="col-md-6">
	<?php if(count($members[0]) != 0) {  ?>
		<div id="stol_4" class="stol">
			<div class="header"> Бай</div>
			
			<div class="tabsheader">
			<span>ФИО</span><span>Очки</span><span>Выбытие</span>
			</div>
			<div class="tabsstat">
				<table class="table  table-bordered"><tbody>
					<?php 
							foreach($members[0] as $member){
								echo '<tr>';
								echo '<td class="name ';
								if ( $member['status'] == 0 ) echo 'status0 '; 
								echo '">'.$member['name'].'</td><td class="stat" >'.$member['points'].'</td><td class="outs">'.$member['order'].'</td>';
								echo '</tr>';
							}	
					?>
				</tbody></table>
			</div>
		</div>
	<?php } ?>
	</div>
</div>

</div>


</div> 

<div class="bootom"></div>

<script>
(function ($) { $.fn.wysiwygEvt = function () { return this.each(function () { var $this = $(this); var htmlOld = $this.html(); $this.bind('blur keyup paste copy cut mouseup', function () { var htmlNew = $this.html(); if (htmlOld !== htmlNew) { $this.trigger('change'); htmlOld = htmlNew; } }) }) } })(jQuery); 

$('.autosave').wysiwygEvt();
function savechange(m_id, wh, step, e) {
	console.log( $(e).text() );
	$(e).text( $(e).text().replace(/\D+/g,"") );

	$.ajax({
		type: "POST",
		url: "/admin/api.php",
		data: { 
			'm_id'	:	m_id,
			'who'	:	wh,
			'val'		:	$(e).text(),
			'step'	:	step,
			'type'	:	'savechange'
		},
	})
	.done(function( ResultData ) {
		
		})
    .fail(function( jqXHR, textStatus ) {
		alert ('Что то не так');
	});
	
}

  </script>
</body>
</html>
