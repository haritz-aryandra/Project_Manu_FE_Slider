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
$client = new nusoap_client('http://wsproxy.skkmigas.go.id/wsServiceMigasESDM?wsdl', 'wsdl');
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
// Doc/lit parameters get wrapped
/*$param = array('token' => '8hNRJq3QWd1qVHuK4v9jfcen2FgMMQHF23Md3XMHeNTWMe1QuiXg3oJT1d7WAMUMy6NcaRl8b85HI9bmNehBYfqQoeZwUt8NKERAbgUzQVKY0oJEfB2sqaJ03yfIrPxE','id' => 'soa1','password' => 'skkmigas1234*');*/
$result = $client->call('serviceMigasESDM', '', '', '', false, true);
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
		
		//by zen
		?><table border=1>
		<tr>
                <th>KKKS</th>
                <th>LIFTING DATE</th>
                <th>CTP</th>
                <th>LIFTING VOL.</th>
                <th>PRODUCT</th>
                <th>LIFTING ENERGY</th>
		</tr>
		<?php
		$count = count($result);
		for($i=0;$i<=$count;$i++){
			?>
			<tr>
			<td><?php echo $result['lifting'][$i]['kkks'];?></td>
			<td><?php echo $result['lifting'][$i]['date'];?></td>
			<td><?php echo $result['lifting'][$i]['ctp'];?></td>
			<td><?php echo $result['lifting'][$i]['volumeValue'] . " ";
			echo $result['lifting'][$i]['volumeUom'];?></td>
			<td><?php echo $result['lifting'][$i]['product'];?></td>
			<td><?php echo $result['lifting'][$i]['energy'] . " ";
			echo $result['lifting'][$i]['energyUom'];?></td></tr>
			<?php
		}
		
		echo '</pre>';
		
		//oracle database connection
		$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.17.1.15)(PORT = 1521)))(CONNECT_DATA=(SID=LIFTINGMCC)))"; 
		$conn = oci_connect('lifting', '1qaz2wsx', $db);
		if (!$conn) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

		//check existing table
		//$tablename="T_SKK" . date("Y");
		$tablename="T_LIFTINGSKK";
		$sql ="select table_name from user_tables where table_name='" . $tablename . "'";
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		
		$nrows = oci_fetch_all($stid, $res);
		//echo "$nrows rows fetched<br>\n";	
		//var_dump($res);
		oci_free_statement($stid);	
		
		if ($nrows==0)
		{
			$sql = "CREATE TABLE LIFTING." . $tablename . "
			(
			  KKKS        NVARCHAR2(200) NOT NULL,
			  LIFT_DATE   TIMESTAMP(6) NOT NULL,
			  CTP         NVARCHAR2(50) NOT NULL,
			  PRODUCT     NVARCHAR2(100) NOT NULL,
			  VOL_VALUE   FLOAT(126) DEFAULT 0,
			  VOL_UOM     NVARCHAR2(10),
			  ENERGY      FLOAT(126) DEFAULT 0,
			  ENERGY_UOM  NVARCHAR2(10),
			  ACQ_DATE    TIMESTAMP(6) DEFAULT SYSDATE
			)";
		}
		else
		{
			$count = count($result);
			for($i=0;$i<=$count;$i++){			
				$sql = "INSERT INTO " . $tablename . " (KKKS, LIFT_DATE, CTP, VOL_VALUE, VOL_UOM, PRODUCT, ENERGY, ENERGY_UOM, ACQ_DATE) VALUES ('" . $result['lifting'][$i]['kkks'] . "', '" . date_format(date_create($result['lifting'][$i]['date']),"d/M/Y") . "', '" . $result['lifting'][$i]['ctp'] . "', " . intval($result['lifting'][$i]['volumeValue']) . ", '" . $result['lifting'][$i]['volumeUom'] . "', '" . $result['lifting'][$i]['product'] . "', " . intval($result['lifting'][$i]['energy']) . ", '" . $result['lifting'][$i]['energyUom'] . "', SYSDATE)";
				echo $sql . "<br />\n";

				$stid = oci_parse($conn, $sql);
				oci_execute($stid);
				//echo oci_num_rows($stid) . " row inserted.<br />\n";
				oci_free_statement($stid);	
			}
			echo $i . " row inserted.<br />\n";
			$sql = "SELECT * FROM " . $tablename;
		}
		echo $sql . "<br />\n";

		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
		
		$nrows = oci_fetch_all($stid, $res);
		echo "$nrows rows fetched<br>\n";	
		//var_dump($res);
		oci_free_statement($stid);	

		// Pretty-print the results
		echo "<table border='1'>\n";
		foreach ($res as $col) {
			echo "<td>\n";
			foreach ($col as $item) {
				echo "  ".($item !== null ? htmlentities($item, ENT_QUOTES) : "")."  \n";
			}
			echo "</td>\n";
		}
		echo "</table>\n";

		oci_close($conn);
	
	}
	
}
//echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

/*	$servername = "192.168.1.122";
	$username = "skkmigas";
	$password = "skkmigas";
	$dbname = "liftingskk";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	echo "Connected successfully";
	
	// sql to create table
	/*$sql = "CREATE TABLE t_liftingskk (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	KKKS VARCHAR(100) NOT NULL,
	LIFT_DATE DATETIME NOT NULL,
	CTP VARCHAR(50) NOT NULL,
	VOL_VALUE INT(6), 
	VOL_UOM VARCHAR(10),
	PRODUCT VARCHAR(50),
	ENERGY INT(6), 
	ENERGY_UOM VARCHAR(10),
	ACQ_DATE TIMESTAMP
	)";

	if ($conn->query($sql) === TRUE) {
		echo "<BR> Table t_liftingskk created successfully";
	} else {
		echo "<BR> Error creating table: " . $conn->error;
	}*/
	
	/*for($i=0;$i<=$count;$i++){
		$sql = "INSERT INTO t_liftingskk (KKKS, LIFT_DATE, CTP, VOL_VALUE, VOL_UOM, PRODUCT, ENERGY, ENERGY_UOM) VALUES ('" . $result['lifting'][$i]['kkks'] . "', '" . date_format(date_create($result['lifting'][$i]['date']),"Y/m/d H:i:s") . "', '" . $result['lifting'][$i]['ctp'] . "', " . intval($result['lifting'][$i]['volumeValue']) . ", '" . $result['lifting'][$i]['volumeUom'] . "', '" . $result['lifting'][$i]['product'] . "', " . intval($result['lifting'][$i]['energy']) . ", '" . $result['lifting'][$i]['energyUom'] . "')";
		if ($conn->query($sql) === TRUE) {
			echo "New record " . $i . " created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		//echo date_format(date_create($result['lifting'][$i]['date']),"Y/m/d H:i:s");
	}

	$conn->close();*/
	

?>
