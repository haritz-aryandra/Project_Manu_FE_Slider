<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">

  <title>Lifting Acquisition from SKK Migas</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="/resources/demos/style.css">

  <script>

  $(function() {

	var sat = new Date();
	sat.setDate(sat.getDate() - 2);

    $( "#datepicker" ).datepicker({dateFormat: 'yy-M-dd',
        defaultDate: sat
	});
	
	var sat = new Date();
	sat.setDate(sat.getDate());

    $( "#datepicker2" ).datepicker({dateFormat: 'yy-M-dd',
        defaultDate: sat
	});

  });

  </script>

</head>

 <body>
<pre>
<form method="post" name="form">
<h2>ENTER CRITERIA</h2>
<table>
  <tr> 
	<td>Date: </td>
	<td><input type="text" name="zdate" id="datepicker"> To <input type="text" name="zdate2" id="datepicker2"> YYYY-MMM-DD</td>
  </tr>
  <tr> 
	<td>KKKS: </td>
	<td align="left"><!--input type="text" name="zkkks"-->
	<select name="zkkks">
		<option value="" selected>-------- KONTRAKTOR KONTRAK KERJA SAMA (KKKS) --------</option>
		<?php
			include 'connect.php';
			//get KKKS list
			$tablename="T_LIFTING_SKK";
			$sql ="select distinct kkks from " . $tablename . " order by kkks";
			$stid = oci_parse($conn, $sql);
			oci_execute($stid);
			//$nrows = oci_fetch_all($stid, $res);
			while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach ($row as $item) {
					echo "<option value='" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "'>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</option>";
				}
			}
			oci_free_statement($stid);
		?>
	</select>
	</td>
  </tr>
  <tr> 
	<td></td>
	<td><input type="submit" value="Execute" onclick="javascript: form.action='wsdlclient.php';"/>  <input type="submit" value="Check Data" onclick="javascript: form.action='wsdlclientcek.php';"/><p><input type="submit" value="View Report" onclick="javascript: form.action='wsdlclientreport.php';"/>  <input type="submit" value="View Data SKK" onclick="javascript: form.action='wsdlclientskk.php';"/></td>
 </tr>
</table>

 </form>
</pre>
 </body>
 </html> 