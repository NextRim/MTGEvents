<?php

	include "../mydb.php";
	
	$standings = [];
	
	function getstandings( $step ) {
		global $standings;
		$standings = [];
	//	$query = "SELECT e.*, m.name FROM `event` e INNER JOIN `members` m on e.member_id = m.member_id WHERE e.step = ".(int)$step." ORDER BY `e`.`points` DESC, `e`.`order` DESC ";
		$query = "SELECT e.*, m.name, m.status, SUM(e.points) as sum_p, SUM(e.order) as sum_o FROM `event` e INNER JOIN `members` m on e.member_id = m.member_id WHERE e.step <= ".(int)$step." GROUP BY e.member_id ORDER BY `sum_p` DESC, `sum_o` DESC  ";
		$result = mysql_query ($query)
			or die ("Error select TAB_TICKETS: ".mysql_error());
 		while ($a = mysql_fetch_array($result, MYSQL_ASSOC)){
			$standings[ $a['member_id'] ] = array(
				'id'		=> $a['member_id'],
				'name'		=> $a['name'],
				'step'		=> $a['step'],
				'points'	=> $a['sum_p'],
				'order'		=> $a['sum_o'],
				'status'		=> $a['status']
			);
			
			$order[ $a['sum_p'] ][] = $a['member_id'];
		}
		
		$sotid = "";
		foreach( $order as $o){
			//echo implode(',', $o)."<br>";
			usort($o, "sortsteck");
			$sotid .= implode(',', $o).",";
			//echo implode(',', $o)."<br>";
		}
		$sotid = split(',',substr($sotid,0,-1));
		
		foreach( $sotid as $o){
			$sotedarr[] = $standings[$o];
		}
		
		return $sotedarr;
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

</head>
<body>
<div class="container">
<div class="masthead">
<h3 class="text-muted">Турнирная сетка <span class="admin">ADMIN</span></h3>
<ul class="nav nav-justified">
<li><a href="./index.php">Столы</a></li>
<li class="active"><a href="./standings.php">Стендинги</a></li>
<li><a href="./spisok.php">Список игроков</a></li>
<li><a href="#">Результаты</a></li>

</ul>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="header">Турнирные стендинги</div>
	</div>
	<div class="col-md-12 steps">
		<?php for($i=1; $i<=$maxstep; $i++){ ?>
		<div class="header"><a href="./standings.php?step=<?php echo $i;?>" class="<?php if ($i == $step) echo 'activ';?>" >Раунд №<?php echo $i;?></a></div>
		<?php } ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
	<div class="tabsstandings">
			<div class="tabsheader">
			<span>ФИО</span><span>Очки</span><span>Выбытие</span>
			</div>
		<table class="table  table-bordered"><tbody>
		<?php
		$stends = getstandings( $step );
		if(count( $stends ) != 0) {  
			foreach($stends as $s){
				echo '<tr>';
				echo '<td class="name ';
				if ( $s['status'] == 0 ) echo 'status0 '; 
				echo '">'.$s['name'].'</td><td class="stat autosave"  onChange="savechange( '.$s['id'].', \'points\', '.$step.', this)">'.$s['points'].'</td><td class="outs autosave"  onChange="savechange( '.$s['id'].', \'order\', '.$step.', this)">'.$s['order'].'</td>';
				echo '</tr>';
			}	
		}
		?>
		</tbody></table>
	</div>
	</div>
	<div class="col-md-6">
		<form action="./index.php" method="post" onsubmit="return confirm('Сформировать сетку второго тура?');">
			<input type="hidden" name="type" value="calculation-step">
			<input type="hidden" name="step" value="<?php echo 1+$step; ?>">
			<button type="submit" class="btn btn-primary">Сформировать тур №<?php echo 1+$step; ?></button>
		</form>
	</div>
</div>

<div style="height: 50px;"></div>

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
