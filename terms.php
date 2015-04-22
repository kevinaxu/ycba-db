<?php

	// TODO: Fix all the hardcodes 

	// Get info from post request and do basic error checking
	$obj_val = $_POST['obj_val']; 
	$input_type = "ObjectID"; 
	if ($_POST['input_type'] !== "id") {
		$input_type = "ObjectNum"; 
	}
	if (!$obj_val) {	
		echo "there's nothing in object value"; 
	}
	
	// Grab credentials from the ini file 
	$creds = parse_ini_file("config/app.ini");
	$server_name = $creds['server_name'];
	$user_name = $creds['user_name'];
	$pw = $creds['pw']; 
	
	// Table name => Database name 
	/*
	$db_tables = array("dbo.LIDOtermsLocalTGNonly" => "TMSThesaurus", 
						"dbo.Objects" => "TMS", 
						"dbo.ThesXrefs" => "TMS")
	*/
	
	// Connect to TMSThesaurus
	$thes_conn_info = array("Database" => "TMSThesaurus", "UID" => $user_name, "PWD" => $pw); 
	$thes_conn = sqlsrv_connect($server_name, $thes_conn_info); 
	if (!$thes_conn) {
		echo "Connection to TMSThesaurus could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// Connect to TMS
	$tms_conn_info = array("Database" => "TMS", "UID" => $user_name, "PWD" => $pw); 
	$tms_conn = sqlsrv_connect($server_name, $tms_conn_info); 
	if (!$tms_conn) {
		echo "Connection to TMS could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}

	// If the input type is object number, then we need to find its object ID using the Objects table
	$object_id = $obj_val; 
	if ($input_type == "ObjectNum") {
		$query = "SELECT ObjectID FROM dbo.Objects WHERE ObjectNumber = ?"; 
		$params = array($obj_val); 
		$result = sqlsrv_query($tms_conn, $query, $params); 
		if ($result === false) {
			die(print_r(sqlsrv_errors(), true)); 
		}
		// See if there are is any information actually returned from the database
		// TODO: Fix case if there is no data returned. 
		else if (sqlsrv_num_rows($result) === 0) {
			die("No data returned from the search query. Try again");
		}
		else {
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
			$object_id = $row['ObjectID'];
		}
	}
	print_r($object_id);
	
	// Then find all associated term IDs for that object ID using the Thesxref table
	
	// Then we grab all the data for each term ID using the LIDOtermsLocalTGNonly table
	
	// Pretty print the data 
	
	// Close the database connections 
	sqlsrv_close($thes_conn); 
	sqlsrv_close($tms_conn); 

	
?>
