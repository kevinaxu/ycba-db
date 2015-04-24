<html>
<head>
</head>
<body>
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
	
	// TODO: Put this into a function by itself. 
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
	
	// Check that this is a valid object id or object number
	// TODO: Combine these two error checks 
	// if ($input_type == "ObjectID") {
	// 	if (!check_obj_id($obj_val, $tms_conn)) {
	// 		die('Not a valid ObjectID'); 
	// 	}
	// }
	// if ($input_type == "ObjectNum") {
	// 	if (!check_obj_num($obj_val, $tms_conn)) {
	// 		die('Not a valid ObjectNumber');
	// 	}			
	// }


	// If the input type is object number, then we need to find its object ID using the Objects table
	$object_id = $obj_val; 
	if ($input_type == "ObjectNum") {
		$query = "SELECT ObjectID FROM dbo.Objects WHERE ObjectNumber = ?"; 
		$params = array($obj_val); 
		$result = sqlsrv_query($tms_conn, $query, $params); 
		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
		$object_id = $row['ObjectID'];
	}
	// print_r($object_id); 
	
	// Then find all associated term IDs for that object ID using the Thesxref table
	$query = "SELECT TermID FROM dbo.ThesXrefs WHERE ID = ?";
	$params = array($object_id); 
	$result = sqlsrv_query($tms_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET)); 

	// Check that this returned something
	if(!sqlsrv_num_rows($result)) {
		die('No terms for object' . $object_id); 	
	}

	// Get a list of all the associated term ids for an object 
	$term_ids = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_ids[] = $row['TermID'];
	}
	// foreach($term_ids as $term) {
	// 	print_r($term); 
	// 	print("</br>");
	// }
	
	// Then we grab all the data for each term ID using the LIDOtermsAndGeo table
	$term_str = "('" . implode("','", $term_ids) . "')";
	$term_fields = array("Term", "Longitude", "LongitudeNumber", "Latitude", "LatitudeNumber", "TermID"); 
	$query = "SELECT " . implode(",", $term_fields) . " FROM dbo.LIDOtermsAndGeo WHERE TermID IN " . $term_str; 
	$result = sqlsrv_query($thes_conn, $query);

	$term_info = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_info[] = $row; 
	}
/*	foreach($term_info as $term) {
		print_r($term); 
	}*/
	
	// Close the database connections 
	sqlsrv_close($thes_conn); 
	sqlsrv_close($tms_conn); 
	
	/***** 	helper functions *******/
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


	// TESTING
	// if ($result === false) { print("false"); } 
	// else if (empty($result)) { print("empty"); }
	// else { print("true"); }
	
?>

<h1>what up</h1>
<?php foreach($term_info as $term) { ?>
	<h3><?php echo $term['Term']; ?></h3>
<?php } ?>
</body>
</html>
