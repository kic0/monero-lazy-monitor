<?php
$result="";
/*
if(isset($_POST["ip"]))
{
	if(($_POST["id"]=="") || ($_POST["ip"]=="") || ($_POST["port"]=="") || ($_POST["port"]=="")) { $result = "Erro há campos em branco\n"; }
	else{
		$file = fopen("workers.txt","a");
		$linha = $_POST["id"] . ":" . $_POST["ip"] . ":" . $_POST["port"] . ":" . $_POST["soft"] . PHP_EOL;
		fputs($file, $linha) ;
		$result = "adicionado";
	}
	//die(print_r($_POST));
}
*/
$workers = array();
$jsVar=array();


$strJSON = file_get_contents("config.json");
$configData = json_decode($strJSON, true);

$workers = $configData["workers"];
$refresh = $configData["refresh"] * 1000;

foreach($workers as $tempW)
{
	array_push($jsVar, "['" .$tempW['id']. "','" .$tempW['ip']. "','" .$tempW['port'] . "','" . $tempW['soft']. "','" . $tempW['alert'] . "']");
}
$tempStr=implode(",", $jsVar);
$jsVarWorkers="[" . $tempStr . "]";
/*
$file = fopen("workers.txt","r");
while ($tempStr=fgets($file)){
	$tempStr=rtrim($tempStr);
	$tempW = explode(":", $tempStr);
	array_push($workers, array( "ID" => $tempW[0], "IP" => $tempW[1], "PORT" => $tempW[2], "SOFT" => $tempW[3]));
	array_push($jsVar, "['" .$tempW[0]. "','" .$tempW[1]. "','" .$tempW[2] . "','" . $tempW[3]. "']");
};

fclose($file);
*/
?>
<!DOCTYPE html>
<html>
<head>
<title>Monero Lazy Monitor</title>
<link rel='stylesheet' href='style.css' />
<meta name='viewport' content='width=device-width' />
<script>
function HATTORI(item)
{
	var xmlhttp = new XMLHttpRequest();
	document.getElementById("rd" + item[0]).innerHTML = "<td class='threadData'><span class='threadData'>...loading...</span></td>"
	//document.getElementById("S" + item[0]).innerHTML = "<span>" +item[0] + "&nbsp;(" + item[3] + ")</span>&nbsp;|&nbsp;<span class='highestHashrate'>...loading...</span>"; 
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var bb = this.responseText;
			if((item[3]=="xmrig")||(item[3]=="stak")){
				var myObj = JSON.parse(bb);
				var rtHTML = " "; //rowTitle
				var rdHTML = " "; //roeData
				rtHTML += "<td class='workerID' id='S"+ item[0]+"'>";
				rtHTML += "<span>" +item[0] + "&nbsp;(" + item[3] + ")&nbsp;";
				rtHTML += "<span class='totalHashrate'>" + myObj.hashrate["total"][2] + "</span>";
				rtHTML += "<span class='highestHashrate'>(" + myObj.hashrate["highest"] + ")</span>";
				rtHTML += "</td>";
				rdHTML += "<td class='threadData'><div>";
				var threads = myObj.hashrate["threads"];
				if(threads)	threads.forEach(function(threadItem) {
						var red="";
						if((threadItem[2]<=item[4]) || (myObj.hashrate["highest"]=="ERRO!")) red=" red";
						rdHTML += "<span class='thread" + red  + "'>" + threadItem[2] + "</span>";
					});
				rdHTML += "</div></td>"; 
			}else{
				iHTML = bb;
			}
			document.getElementById("rt" + item[0]).innerHTML = rtHTML; 
			document.getElementById("rd" + item[0]).innerHTML = rdHTML; 
		}
	};
	urlStr = "semi-proxy.php?id="+ item[0] + "&ip=" + item[1] + "&port=" +  item[2] + "&soft=" + item[3];
	xmlhttp.open("GET", urlStr, true);
	xmlhttp.send();
}

function NINJA()
{
	var workers = <?php echo $jsVarWorkers; ?> ;
	workers.forEach(HATTORI);
}

function refresh() {
	NINJA();
	setTimeout(refresh, <?php echo $refresh; ?>);
}
setTimeout(refresh, <?php echo $refresh; ?>);

</script>
</head>
<body onload="NINJA()">
<?php /*
<h3>Monitor para pregui&ccedil;osos</h3>
<?php
if($result==""){
?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	ID: <input type="text" name="id">&nbsp;&nbsp;&nbsp;
	IP: <input type="text" name="ip">&nbsp;&nbsp;&nbsp;
	Port: <input type="text" name="port">&nbsp;&nbsp;&nbsp;
	<select name="soft">
		<option value="xmrig">xmrig</option>
		<option value="stak">stak</option>
	</select>
	<input type="submit" name="submit" value="Adicionar">
	</form>
<?php
} else { echo $result; }
?>
<hr>
*/
?>
<div class='mainwrapper'>
<table>
	<tr class="rowTitle">
		<th ><span>WorkerID (soft)</span><span class="totalHashrate">Total</span><span class="highestHashrate">(Highest)</span></th>
	</tr>
<?php 
foreach($workers as $worker) {
	echo "<tr class='rowTitle' id='rt" . $worker["id"] . "'>";
	echo "<td class='workerID' id='S" . $worker["id"] . "'>";
	echo "<span>" . $worker["id"] . "&nbsp;(". $worker["soft"]. ")</span>";
	echo "<span class='totalHashrate'>&nbsp0&nbsp</span>";
	echo "<span class='highestHashrate'>(&nbsp0&nbsp)</span>";
	echo "</td>";
	echo "</tr>";
	echo "<tr class='rowData' id='rd" . $worker["id"]. "'>";
	//echo "<td class='totalHashrate'>&nbsp;0&nbsp;</td>";
	echo "<td class='threadData'><div>&nbsp;</div></td>";
	echo "</tr>";
}
?>
</table>
</div>
</body>
</html>
