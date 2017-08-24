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
	}
}
//echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>
