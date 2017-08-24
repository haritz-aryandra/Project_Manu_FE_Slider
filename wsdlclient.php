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

if ($client) {
	$zdate = $_POST["zdate"];
	$zdate2 = $_POST["zdate2"];	
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
		echo "<a href=\"javascript:history.go(-1)\">GO BACK</a> <br />\n";
		
		//oracle database connection
		$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.17.1.15)(PORT = 1521)))(CONNECT_DATA=(SID=LIFTINGMCC)))"; 
		$conn = oci_connect('lifting', '1qaz2wsx', $db);
		if (!$conn) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

		//check existing table
		$tablename="T_LIFTING_SKK";
		$sql ="select table_name from user_tables where table_name='" . $tablename . "'";
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		
		$nrows = oci_fetch_all($stid, $res);

		oci_free_statement($stid);
		
		if ($nrows==0)
		{
			$sql = "CREATE TABLE LIFTING." . $tablename . "
			(
			  KKKS        NVARCHAR2(200) NOT NULL,
			  LIFT_DATE   TIMESTAMP(6) NOT NULL,
			  CTP         NVARCHAR2(50) NOT NULL,
			  BUYER       NVARCHAR2(100) NOT NULL,
			  PRODUCT     NVARCHAR2(100) NOT NULL,
			  VOL_VALUE   FLOAT(126) DEFAULT 0,
			  VOL_UOM     NVARCHAR2(10),
			  ENERGY      FLOAT(126) DEFAULT 0,
			  ENERGY_UOM  NVARCHAR2(10),
			  ACQ_DATE    TIMESTAMP(6) DEFAULT SYSDATE
			)";
			echo $sql . "<br />\n";
		}
		
		$count = count($result['lifting'])-1;
		//looping for each date
		$startdate=strtotime($zdate);
		$enddate=strtotime($zdate2);
		$i=0;
		while ($startdate <= $enddate) {
			echo date("Y/m/d", $startdate),"<br>";			
			$result = "";
			$result = $client->call('getLiftingDataByDate', array('date' => date("Y/m/d", $startdate), 'kkks' => $zkkks), 'http://wsproxy.skkmigas.go.id/wsMigasESDM', '', false, true);
			$startdate = strtotime("+1 days", $startdate);
		
			$count = count($result['lifting'])-1;
			$i = $count;
			$sql = "DELETE FROM " . $tablename . " WHERE LIFT_DATE='" . date_format(date_create($result['lifting'][$count-1]['date']),"d/M/Y") . "'";
			echo $sql . "<br />\n";

			$stid = oci_parse($conn, $sql);
			oci_execute($stid);
			echo oci_num_rows($stid) . " row deleted.<br />\n";
			oci_free_statement($stid);
			
			for($i=0;$i<=$count;$i++){
				$sql = "INSERT INTO " . $tablename . " (KKKS, LIFT_DATE, CTP, BUYER, VOL_VALUE, VOL_UOM, PRODUCT, ENERGY, ENERGY_UOM, ACQ_DATE) VALUES ('" . $result['lifting'][$i]['kkks'] . "', '" . date_format(date_create($result['lifting'][$i]['date']),"d/M/Y") . "', '" . $result['lifting'][$i]['buyer'] . "', '" . $result['lifting'][$i]['ctp'] . "', " . floatval($result['lifting'][$i]['volumeValue']) . ", '" . $result['lifting'][$i]['volumeValueUom'] . "', '" . $result['lifting'][$i]['product'] . "', " . floatval($result['lifting'][$i]['energy']) . ", '" . $result['lifting'][$i]['energyUom'] . "', SYSDATE)";
				//echo $sql . "<br />\n";
				$stid = oci_parse($conn, $sql);
				oci_execute($stid);
				oci_free_statement($stid);
			}
			echo $i . " row inserted.<br />\n";
		} //end of loop
		oci_close($conn);
	}	
}
?>
</body>
</html>