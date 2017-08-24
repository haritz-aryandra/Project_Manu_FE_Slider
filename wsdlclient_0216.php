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
$datez2=date('Y/M/d');
$zdate = $_POST["zdate"];
$zdate2 = $_POST["zdate2"];


if ($client) {
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
		//print_r($result);

		$startdate=strtotime($zdate);
		$enddate=strtotime($zdate2);=
		while ($startdate <= $enddate) {
			echo date("Y/m/d", $startdate),"<br>";
			$startdate = strtotime("+1 days", $startdate);
		} 

		echo "<a href=\"javascript:history.go(-1)\">GO BACK</a> <br />\n";
		$count = count($result['lifting'])-1;
		//access database connection
/*		$dbName = $_SERVER["DOCUMENT_ROOT"] . "/phpws/LiftingSKK.mdb";
		if (!file_exists($dbName)) {
			die("Could not find database file.");
		}
		//$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");	 
		
		$user=""; 
		$password="";
		$conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$dbName", $user, $password);		
		if(!$conn)
              exit("Connection Failed: " . $conn);

		$tablename="T_LIFTING_SKK";		  
		for($i=0;$i<=$count;$i++){
			$sql = "INSERT INTO " . $tablename . " (KKKS, LIFT_DATE, CTP, BUYER, VOL_VALUE, VOL_UOM, PRODUCT, ENERGY, ENERGY_UOM, ACQ_DATE) VALUES ('" . $result['lifting'][$i]['kkks'] . "', '" . date_format(date_create($result['lifting'][$i]['date']),"d/M/Y") . "', '" . $result['lifting'][$i]['buyer'] . "', '" . $result['lifting'][$i]['ctp'] . "', " . intval($result['lifting'][$i]['volumeValue']) . ", '" . $result['lifting'][$i]['volumeValueUom'] . "', '" . $result['lifting'][$i]['product'] . "', " . intval($result['lifting'][$i]['energy']) . ", '" . $result['lifting'][$i]['energyUom'] . "', NOW())";

			$rs = odbc_exec($conn, $sql);			
			odbc_free_result( $rs );  
		}
		echo $i . " row inserted.<br />\n";
		
		$sql  = "SELECT * FROM " . $tablename . " WHERE LIFT_DATE=#" . $datez . "#";
		echo $sql . "<br />\n";
        $rs = odbc_exec($conn, $sql);
        if(!$rs)
              exit("Error in SQL");
        echo "<table><tr>";
        echo "<th>kkks</th>";
        echo "<th>lift_date</th>";
        echo "<th>vol_value</th></tr>";

        while(odbc_fetch_row($rs)){
          $kkks=odbc_result($rs,"kkks");
          $date=odbc_result($rs,"lift_date");
          $vol=odbc_result($rs,"vol_value");
          echo "<tr><td>$kkks</td>";
          echo "<td>$date</td>";
          echo "<td>$vol</td></tr>";
        }
		
		odbc_free_result( $rs );  
        odbc_close($conn);
		//end of access
		exit("End of Access");
*/		
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
		
		$i = $count;
		$sql = "DELETE FROM " . $tablename . " WHERE LIFT_DATE='" . date_format(date_create($result['lifting'][$i-1]['date']),"d/M/Y") . "'";
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
		$sql = "SELECT * FROM " . $tablename . " WHERE LIFT_DATE='" . date_format(date_create($result['lifting'][$i-1]['date']),"d/M/Y") . "'";
		echo $sql . "<br />\n";

		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		
		$nrows = oci_fetch_all($stid, $res);
		echo "$nrows rows fetched<br>\n";

		oci_free_statement($stid);

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

		oci_close($conn);

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