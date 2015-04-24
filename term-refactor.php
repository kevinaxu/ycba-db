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
	
	// Table name => Database name 
	// $db_tables = array("dbo.LIDOtermsLocalTGNonly" => "TMSThesaurus", 
	// 					"dbo.Objects" => "TMS", 
	// 					"dbo.ThesXrefs" => "TMS")
	




	// TODO: Put this into a function by itself. 
	/*
	// Connect to TMS
	$tms_conn_info = array("Database" => "TMS", "UID" => $user_name, "PWD" => $pw); 
	$tms_conn = sqlsrv_connect($server_name, $tms_conn_info); 
	if (!$tms_conn) {
		echo "Connection to TMS could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// Connect to TMSThesaurus
	$thes_conn_info = array("Database" => "TMSThesaurus", "UID" => $user_name, "PWD" => $pw); 
	$thes_conn = sqlsrv_connect($server_name, $thes_conn_info); 
	if (!$thes_conn) {
		echo "Connection to TMSThesaurus could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	*/

	// Connect to TMSThesaurus
	// $thes_conn = open_db("TMSThesaurus"); 

	// Connect to TMS
	$tms_conn = open_db("TMS"); 

	/*
	$query = "SELECT ObjectID, ObjectNumber from dbo.Objects LIMIT 5"; 
	$result = sqlsrv_query($tms_conn, $query);
	if(!sqlsrv_num_rows($result)) {
		die('No data for term ' . $term_id); 	
	}
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		print_r($row); 
		print("</br>");
	}


	
	// Check that this is a valid object id or object number
	// TODO: Combine these two error checks 
	if ($input_type == "ObjectID") {
		if (!check_obj_id($obj_val, $tms_conn)) {
			// die('Not a valid ObjectID'); 
		}
	}
	if ($input_type == "ObjectNum") {
		if (!check_obj_num($obj_val, $tms_conn)) {
			// die('Not a valid ObjectNumber');
		}			
	}

	// If the input type is object number, then we need to find its object ID using the Objects table
	$object_id = $obj_val; 
	if ($input_type == "ObjectNum") {
		$object_id = get_id_from_num($obj_val, $tms_conn); 
	}

	// Then find all associated term IDs for that object ID using the Thesxref table
	$query = "SELECT TermID FROM dbo.ThesXrefs WHERE ID = ? AND TableID = ?";
	$params = array($object_id, "108"); 
	$result = sqlsrv_query($tms_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET)); 
	
	// Check that this returned something
	if(!sqlsrv_num_rows($result)) {
		die('No terms for object' . $object_id); 	
	}

	// Print out the term IDs for each
	// while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
	// 	print_r($row['TermID']); 
	// 	print("</br>");
	// }
	
	// Then we grab all the data for each term ID using the LIDOtermsLocalTGNonly table
	$query = "SELECT Term, TermID FROM dbo.LIDOtermsLocalTGNonly LIMIT 10"; 
	$result = sqlsrv_query($thes_conn, $query);
	if(!sqlsrv_num_rows($result)) {
		die('No data for term ' . $term_id); 	
	}
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		print_r($row); 
		print("</br>");
	}


	// $term_id = 2037642; 
	
	// $params = array($term_id); 
	// $result = sqlsrv_query($thes_conn, $query); 
	// // $result = sqlsrv_query($thes_conn, $query, $params); 
	// if(!sqlsrv_num_rows($result)) {
	// 	die('No data for term ' . $term_id); 	
	// }
	// while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
	// 	print_r($row); 
	// 	print("</br>");
	// }
	// $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
	// print_r($row);





	// Pretty print the data 
	
	// Close the database connections 
	sqlsrv_close($thes_conn); 
	sqlsrv_close($tms_conn); 
	*/

	/***** 	helper functions *******/

	function get_id_from_num($obj_num, $conn) {
		$query = "SELECT ObjectID FROM dbo.Objects WHERE ObjectNumber = ?"; 
		$params = array($obj_num); 
		$result = sqlsrv_query($conn, $query, $params); 
		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
		$object_id = $row['ObjectID'];
		return $object_id; 
	}



	function open_db($db_name) {

		// Grab credentials from the ini file 
		$creds = parse_ini_file("config/app.ini");
		$server_name = $creds['server_name'];
		$user_name = $creds['user_name'];
		$pw = $creds['pw']; 
		// print($server_name); 
		// print($user_name); 
		// print($pw);

		$conn_info = array("Database" => "TMS", "UID" => $user_name, "PWD" => $pw); 
		$db_conn = sqlsrv_connect($server_name, $conn_info); 
		if (!$db_conn) {
			print "Connection to " . $db_name . " could not be established";  
			die(print_r(sqlsrv_errors(), true)); 
		}

		$query = "SELECT ObjectID, ObjectNumber from dbo.Objects LIMIT ?"; 
		$params = array(5);
		$result = sqlsrv_query($db_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		if(!sqlsrv_num_rows($result)) {
			die('you fucked up');	
		}
		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			print_r($row); 
			print("</br>");
		}


		// return $db_conn; 

	}	


	function check_obj_id($obj_val, $conn) {
		$query = "SELECT ObjectNumber, ObjectID FROM dbo.Objects WHERE ObjectID = ?"; 
		$params = array($obj_val);
		$result = sqlsrv_query($conn, $query, $params); 
		if(!sqlsrv_num_rows($result)) {
			return false; 
		}
		else {
			return true; 
		}
	}
	
	function check_obj_num($obj_val, $conn) {
		$query = "SELECT ObjectNumber, ObjectID FROM dbo.Objects WHERE ObjectNumber = '" . $obj_val . "'";
		$params = array($obj_val);
		$result = sqlsrv_query($conn, $query, $params); 
		if(!sqlsrv_num_rows($result)) {
			return false; 
		}
		else {
			return true; 
		}
	}

	
?>
