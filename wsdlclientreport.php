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
	// Display the result
	echo '<h2>Result</h2><pre>';
	//print_r($result);
	echo "<a href=\"javascript:history.go(-1)\">GO BACK</a> <br />\n";
	
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
	$zdate2 = $_POST["zdate2"];
	$zkkks = strtoupper($_POST["zkkks"]);
	
	//get summary
	if ($zkkks != "") {
		$sql = "SELECT KKKS, PRODUCT, SUM(VOL_VALUE) SUM_OF_VOL_VALUE, VOL_UOM, SUM(ENERGY) SUM_OF_ENERGY, ENERGY_UOM  FROM " . $tablename . " WHERE KKKS LIKE '%" . $zkkks . "%' AND LIFT_DATE BETWEEN '" . date_format(date_create($zdate),"d/M/Y") . "' AND '" . date_format(date_create($zdate2),"d/M/Y") . "' GROUP BY KKKS, PRODUCT, VOL_UOM, ENERGY_UOM ORDER BY KKKS, PRODUCT";
		//echo $sql . "<br />\n";
		
		echo "KKKS " . $zkkks . "<br />\n";
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);	
		while(OCIFetch($stid)){
			$f2 = OCIResult($stid, "PRODUCT");
			$f1 = OCIResult($stid, "SUM_OF_VOL_VALUE");
			$f11 = OCIResult($stid, "VOL_UOM");
			$f3 = OCIResult($stid, "SUM_OF_ENERGY");
			$f31 = OCIResult($stid, "ENERGY_UOM");
			
			echo "PRODUCT " . $f2 . "<br /> ";
			echo "SUM OF LIFTING " . number_format($f1) . " " . $f11 . "<br /> ";
			echo "SUM OF ENERGY " . $f3 . " " . $f31 . "<br />\n";
		}
		oci_free_statement($stid);
	}
	
	//view data base on criteria
	$sql = "SELECT KKKS, TO_CHAR(LIFT_DATE, 'DD-MM-YYYY') LIFT_DATE, CTP, BUYER, PRODUCT, VOL_VALUE, VOL_UOM, ENERGY, ENERGY_UOM FROM " . $tablename . " WHERE KKKS LIKE '%" . $zkkks . "%' AND LIFT_DATE BETWEEN '" . date_format(date_create($zdate),"d/M/Y") . "' AND '" . date_format(date_create($zdate2),"d/M/Y") . "' ORDER BY KKKS, LIFT_DATE, CTP, PRODUCT";
	//echo $sql . "<br />\n";
	
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);
	
	// Pretty-print the results
		//display data original by zen
		?>
		<table id="t01">
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
		$i=0;
		while(OCIFetch($stid)){
			$f0 = OCIResult($stid, "KKKS");
			$f1 = OCIResult($stid, "LIFT_DATE");
			$f2 = OCIResult($stid, "BUYER");
			$f3 = OCIResult($stid, "CTP");
			$f4 = OCIResult($stid, "VOL_VALUE");
			$f41 = OCIResult($stid, "VOL_UOM");
			$f5 = OCIResult($stid, "PRODUCT");
			$f6 = OCIResult($stid, "ENERGY");
			$f61 = OCIResult($stid, "ENERGY_UOM");
			$i=$i+1;
			?>
			<tr>
			<td><?php echo $i;?></td>
			<td><?php echo $f0;?></td>
			<td><?php echo $f1;?></td>
			<td><?php echo $f2;?></td>
			<td><?php echo $f3;?></td>
			<td><?php echo $f4 . " " . $f41;?></td>
			<td><?php echo $f5;?></td>
			<td><?php echo $f6 . " " . $f61;?></td>
			<?php
		}
		echo '</table>';

	oci_free_statement($stid);

	oci_close($conn);

	echo '</pre>';
?>
</body>
</html>