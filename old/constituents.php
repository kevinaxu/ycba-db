<?php

	// Get info from post request and do basic error checking
	$obj_val = $_POST['obj_val']; 
	$input_type = "ObjectID"; 
	if ($_POST['input_type'] !== "id") {
		$input_type = "ObjectNum"; 
	}
	
	if (!$obj_val) {	echo "there's nothing in object value"; }

	// Connect to the sql server database
	$server_name = "bac5-dev"; 
	$user_name = "dcp23"; 
	$pw = "export23"; 
	$db_name = "TMS"; 
	
	$connection_info = array("Database" => $db_name, "UID" => $user_name, "PWD" => $pw); 
	$db_conn = sqlsrv_connect($server_name, $connection_info); 
	
	if (!$db_conn) {
		echo "Connection could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// Construct the query for the Objects table
	// $table_name = "dbo.Objects"; 
	$table_name = "dbo.Constituents"; 
	// $table_cols = array("ObjectID", "ObjectNumber", "Description", "Title", "Dimensions", "CreditLine", "Inscribed", "Markings", "Signed", "Medium");
	$constituent_cols = array("DisplayDate", "DisplayName"); 
	$query = "SELECT " . implode(", ", $table_cols) . " FROM dbo.Constituents WHERE " . $input_type . " = ?"; 
	$params = array($obj_val); 

	// Query and check if successful
	$result = sqlsrv_query($db_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET)); 
	if ($result === false) {
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// See if there are is any information actually returned from the database
	$num_rows = sqlsrv_num_rows($result);
	if ($num_rows === 0) {
		echo "No data returned from the search query. Try again"; 
	}
	
	// Print the page heading
	echo "<h1>" . $obj_val . "</h1>"; 
	
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC); 
	foreach($row as $key => $val) {
		echo "<h2>" . $key . "</h2>"; 
		echo "<p>" . $val . "</p>"; 
	}
	
	
	sqlsrv_close($db_conn); 

	
?>