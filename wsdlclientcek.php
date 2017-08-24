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
		$count = count($result['lifting']);
		
		//oracle database connection
		$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.17.1.15)(PORT = 1521)))(CONNECT_DATA=(SID=LIFTINGMCC)))"; 
		$conn = oci_connect('lifting', '1qaz2wsx', $db);
		if (!$conn) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

		//get data from database
		$tablename="T_LIFTING_SKK";
		$zdate = $_POST["zdate"];
		$zkkks = strtoupper($_POST["zkkks"]);
		$sql = "SELECT * FROM " . $tablename . " WHERE KKKS LIKE '%" . $zkkks . "%' AND LIFT_DATE='" . date_format(date_create($zdate),"d/M/Y") . "' ORDER BY KKKS, CTP, PRODUCT";
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		$nrows = oci_fetch_all($stid, $res);
		oci_free_statement($stid);

		oci_close($conn);
		
		echo "Data SKK Migas " . $count . " row(s) selected <br />\n";
		echo "Database $nrows row(s) selected<br>\n";	
		$i=$count-$nrows;
		echo "Data differences on " . date_format(date_create($zdate),"d/M/Y") . " " . $i . " row(s)";
		echo '</pre>';
	}
}
?>
</body>
</html>