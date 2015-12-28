<?php
$alllines=explode(PHP_EOL,$_POST["inputtext"]);
$allsplitlines=array();
$connected=array();
$disconnected=array();
$connections=array();

function searchForClient($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['client'] === $id) {
           return $key;
       }
   }
   return null;
}


foreach ($alllines as $line) {
	$allsplitlines[]=explode("\t",$line,4);
}
$allsplitlines=array_filter($allsplitlines, function($var){ if (mb_substr($var[1], 0, 13)=="VirtualServer"){if (mb_substr($var[1], 0, 17)=="VirtualServerBase"){return true;} return false;} return false;});
//var_dump($allsplitlines);

foreach ($allsplitlines as $key => $line) {
	if (mb_substr($line[3],0,8)=="client c"){
		$connected[]=array("time"=>$allsplitlines[$key][0],"client"=>explode("'",mb_substr($allsplitlines[$key][3],18))[0]);
	}elseif(mb_substr($line[3],0,8)=="client d"){
		$disconnected[]=array("time"=>$allsplitlines[$key][0],"client"=>explode("'",mb_substr($allsplitlines[$key][3],21))[0]);
	}
}
if (!empty($connected) && !empty($disconnected)){
	if (!(strtotime($connected[0][0]) < strtotime($disconnected[0][0]))){
		unset($disconnected[0]);
	}
}

foreach ($connected as $key => $connectedUser) {
	//search
	$found=searchForClient($connectedUser["client"], $disconnected);
	//verknüpfen
	if($found !=null){
		$connections[]=array("client"=>$connectedUser["client"],"connected"=>$connectedUser["time"],"disconnected"=>$disconnected[$found]["time"]);
		unset($disconnected[$found]);
	}
	
	//unset found
}


//var_dump($connected);
//var_dump($disconnected);
//var_dump($connections);
$conncounter=0;



//output: http://timeglider.com/widget/index.php?p=install
?>
<html>
<head>
<title>Your TS Analysis</title>
<script src="vis.min.js"></script>
<link href="vis.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="visualization"></div>

<script type="text/javascript">
  // DOM element where the Timeline will be attached
  var container = document.getElementById('visualization');

  // Create a DataSet (allows two way data-binding)
  var items = new vis.DataSet([
<?php
foreach ($connections as $key => $thing) {
	print("{id: ".$conncounter.", content: '".$thing["client"]."', subgroup: '".$thing["client"]."', title: '".$thing["client"].": ".$thing["connected"]." - ".$thing["disconnected"]."', start: '".date(DATE_ISO8601,strtotime($thing["connected"]))."', end: '".date(DATE_ISO8601,strtotime($thing["disconnected"]))."'}");

	$conncounter++;
	if ($conncounter<count($connections)){
		print(",");
	}
}
?>
  ]);

  // Configuration for the Timeline
  var options = {};

  // Create a Timeline
  var timeline = new vis.Timeline(container, items, options);
</script>
</body>
</html>