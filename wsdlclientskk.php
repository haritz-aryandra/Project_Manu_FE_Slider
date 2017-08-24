<!DOCTYPE html>
<html>
<head>
<style>
table#t01 tr:nth-child(even) {
    background-color: #eee;
}
table#t01 tr:nth-child(odd) {
   background-color:#fff;
}
table#t01 th	{
    background-color: black;
    color: white;
}
</style>
</head>
<body>
<?php
/*
 *	$Id: wsdlclient1.php,v 1.3 2007/11/06 14:48:48 snichol Exp $
 *
 *	WSDL client sample.
 *
 *	Service: WSDL
 *	Payload: document/literal
 *	Transport: http
 *	Authentication: none
 */

require_once('lib/nusoap.php');
$client = new nusoap_client('http://wsproxy.skkmigas.go.id/wsMigasESDM?wsdl', 'wsdl');
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

//Use basic authentication method
$client->setCredentials('esdm', 'skkmigas1234*', 'basic');
$result = "";

$datez=date('Y/M/d',strtotime("-2 days")); //Karena H-1 masih sering nambah datanya
//$datez='31/Jun/2015';
if ($client) {
	$zdate = $_POST["zdate"];
	$zkkks = strtoupper($_POST["zkkks"]);
	$result = $client->call('getLiftingDataByDate', array('date' => $zdate, 'kkks' => $zkkks), 'http://wsproxy.skkmigas.go.id/wsMigasESDM', '', false, true);
}

// Check for a fault
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	// Check for errors
	$err = $client->getError();
	if ($err) {
		// Display the error
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		// Display the result
		echo '<h2>Result</h2><pre>';
//		print_r($result);
		echo "<a href=\"javascript:history.go(-1)\">GO BACK</a> <br />\n";
		$count = count($result['lifting'])-1;
		echo $count+1 . " row(s) selected <p>";
		
		// Pretty-print the results
		/*echo "<table border='1'>\n";
		foreach ($res as $col) {
			echo "<td>\n";
			foreach ($col as $item) {
				echo "  <br>".($item !== null ? htmlentities($item, ENT_QUOTES) : "</br>")."  \n";
			}
			echo "</td>\n";
		}
		echo "</table>\n";*/

		//display data original by zen
		?><table id="t01">
		<tr>
                <th>NO</th>
				<th>KKKS</th>
                <th>LIFTING DATE</th>
                <th>BUYER</th>
                <th>CTP</th>
                <th>LIFTING VOL.</th>
                <th>PRODUCT</th>
                <th>LIFTING ENERGY</th>
		</tr>
		<?php
		for($i=0;$i<=$count;$i++){
			?>
			<tr>
			<td><?php echo $i+1;?></td>
			<td><?php echo $result['lifting'][$i]['kkks'];?></td>
			<td><?php echo $result['lifting'][$i]['date'];?></td>
			<td><?php echo $result['lifting'][$i]['buyer'];?></td>
			<td><?php echo $result['lifting'][$i]['ctp'];?></td>
			<td><?php echo $result['lifting'][$i]['volumeValue'] . " ";
			echo $result['lifting'][$i]['volumeValueUom'];?></td>
			<td><?php echo $result['lifting'][$i]['product'];?></td>
			<td><?php echo $result['lifting'][$i]['energy'] . " ";
			echo $result['lifting'][$i]['energyUom'];?></td></tr>
			<?php
		}
		echo '</table>';
		echo '</pre>';		
	}	
}
?>
</body>
</html>