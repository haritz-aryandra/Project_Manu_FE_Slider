<?php
	$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.17.1.15)(PORT = 1521)))(CONNECT_DATA=(SID=LIFTINGMCC)))"; 
	$user = "lifting";
	$pass = "1qaz2wsx";
	$conn = oci_connect($user, $pass, $db);
	if (!$conn) {
		$e = oci_error();
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
?>