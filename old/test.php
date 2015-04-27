<?php

	// Get info from post request and do basic error checking
	$obj_val = $_POST['obj_val']; 
	$input_type = "ObjectID"; 
	if ($_POST['input_type'] !== "id") {
		$input_type = "ObjectNum"; 
	}
	
	if (!$obj_val) {	echo "there's nothing in object value"; }

	// Connect to the sql server database
	$creds = parse_ini_file("config/app.ini");
	$server_name = $creds['server_name'];
	$user_name = $creds['user_name'];
	$pw = $creds['pw']; 
	$db_name = "TMS"; 
	
	$connection_info = array("Database" => $db_name, "UID" => $user_name, "PWD" => $pw); 
	$db_conn = sqlsrv_connect($server_name, $connection_info); 
	
	if (!$db_conn) {
		echo "Connection could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// Construct the query for the Objects table
	$table_name = "dbo.Objects"; 
	$table_cols = array("ObjectID", "ObjectNumber", "Description", "Title", "Dimensions", "CreditLine", "Inscribed", "Markings", "Signed", "Medium");
	$constituent_cols = array("DisplayDate", "DisplayName"); 
	$query = "SELECT " . implode(", ", $table_cols) . " FROM dbo.Objects WHERE " . $input_type . " = ?"; 
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
		echo "<h2>" . $key . "</h2><a href='./edit_file.php'>Edit</a>"; 
		// echo "<a href='./edit_file.php'>Edit</a>"; 
		echo "<p>" . $val . "</p>"; 
	}
	
	// print_r($row); 
	
	/*
	for ($i = 0; $i < 5; $i++) {
		print_r($row[$i]); 
	}
	
	
	// Print the table data
	for ($i = 0; $i < count($table_cols); $i++) {
		echo "<h2>" . $table_cols[$i] . "</h2>"; 
		echo "<p>" . $row[$table_cols[$i]] . "</p>"; 
	}
	*/


		/*
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		foreach($table_cols as $col_name) {
			
		}
	
		echo "<tr>"; 
		foreach($row as $r) {
			echo "<td>" . $r . "</td>"; 
		}
		echo "</tr>"; 
	}
	*/
	
	sqlsrv_close($db_conn); 

	
?>
