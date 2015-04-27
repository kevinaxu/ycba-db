
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
	
	$tms_conn = open_db("TMS"); 
	$thes_conn = open_db("TMSThesaurus"); 

	// Grab credentials from the ini file 
	$creds = parse_ini_file("config/app.ini");
	$server_name = $creds['server_name'];
	$user_name = $creds['user_name'];
	$pw = $creds['pw']; 
	
	// TODO: Put this into a function by itself. 
	// Connect to TMS
	// $tms_conn_info = array("Database" => "TMS", "UID" => $user_name, 
	// 	"PWD" => $pw, "CharacterSet" => "UTF-8"); 
	// $tms_conn = sqlsrv_connect($server_name, $tms_conn_info); 
	if (!$tms_conn) {
		echo "Connection to TMS could not be established <br />"; 
		die(print_r(sqlsrv_errors(), true)); 
	}
	
	// Connect to TMSThesaurus
	// $thes_conn_info = array("Database" => "TMSThesaurus", "UID" => $user_name, 
	// 	"PWD" => $pw, "CharacterSet" => "UTF-8"); 
	// $thes_conn = sqlsrv_connect($server_name, $thes_conn_info); 
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
	print_r($object_id); 
	
	// Get the title of this object for printing 
	$query = "SELECT Title, ObjectNumber FROM dbo.Objects WHERE ObjectID = ?"; 
	$params = array($object_id); 
	$result = sqlsrv_query($tms_conn, $query, $params); 
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
	$object_title = $row['Title'];
	$object_number = $row['ObjectNumber'];

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

	
	// Then we grab all the data for each term ID using the LIDOtermsAndGeo table
	$term_str = "('" . implode("','", $term_ids) . "')";
	$term_fields = array("Term", "TermID", "SourceTermID", "ScopeNote", 
		"Longitude", "LongitudeNumber", "Latitude", "LatitudeNumber"); 
	$query = "SELECT " . implode(",", $term_fields) . " FROM dbo.LIDOtermsAndGeo WHERE TermID IN " . $term_str; 
	$result = sqlsrv_query($thes_conn, $query);

	$term_info = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_info[] = $row; 
	}
	// var_dump($term_info); 
	// foreach($term_info as $term) {
	// 	var_dump($term); 
	// }
	
	// Close the database connections 
	sqlsrv_close($thes_conn); 
	sqlsrv_close($tms_conn); 
	
	/***** 	helper functions *******/
	function open_db($db_name) {

		// Grab credentials from the ini file 
		$creds = parse_ini_file("config/app.ini");
		$server_name = $creds['server_name'];
		$user_name = $creds['user_name'];
		$pw = $creds['pw']; 

		$conn_info = array("Database" => $db_name, "UID" => $user_name, "PWD" => $pw, "CharacterSet" => "UTF-8"); 
		$db_conn = sqlsrv_connect($server_name, $conn_info); 
		if (!$db_conn) {
			print "Connection to " . $db_name . " could not be established";  
			die(print_r(sqlsrv_errors(), true)); 
		}

		/*
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
		*/

		return $db_conn; 

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


	// TESTING
	// if ($result === false) { print("false"); } 
	// else if (empty($result)) { print("empty"); }
	// else { print("true"); }
	 
?>

<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Term lookup using Object IDs">
    <meta name="author" content="Kevin Xu">
    <title>Terms Lookup</title>

    <!-- Bootstrap core CSS -->
    <link href="http://getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="http://getbootstrap.com/dist/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="http://getbootstrap.com/examples/theme/theme.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]
    <script src="http://getbootstrap.com/assets/js/ie-emulation-modes-warning.js"></script>
  -->

    <link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
	<body role="document">

	    <!-- Fixed navbar -->
	    <nav class="navbar navbar-inverse navbar-fixed-top">
	      <div class="container">
	        <div class="navbar-header">
	          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
	            <span class="sr-only">Toggle navigation</span>
	          </button>
	          <a class="navbar-brand" href="index.html">Term Lookup</a>
	        </div>
	      </div>
	    </nav>

	    <div class="container theme-showcase" role="main">

	      <div class="object-header">
	        <h2 style="margin-bottom: 0px;"><?php echo $object_title; ?></h2>
	        <h4 style="margin-top: 10px;"><?php echo $object_number; ?></h3>
	        <br>
	      </div>

	      <div class="row">
	      	<form method="POST" action="edit.php">
		        <table class="table">
			      <thead>
			        <tr>
			          <th>TermID</th>
			          <th>Term</th>
			          <th class="filter">Source TermID</th>
			          <th>Scope Note</th>
			          <th>Longitude</th>
			          <th>Longitude Number</th>
			          <th>Latitude</th>
			          <th>Latitude Number</th>
			        </tr>
			      </thead>
			      <tbody>
			      	<?php foreach($term_info as $term) { ?>
			            <tr>
			              <td><input type="text" name="tid" value=<?php echo $term['TermID']; ?>></td>
			              <!-- <td><?php echo $term['TermID']; ?></td> -->
			              <td><?php echo $term['Term']; ?></td>
			              <td><?php echo $term['SourceTermID']; ?></td>
			              <td><?php echo $term['ScopeNote']; ?></td>
			              <td><?php echo $term['Longitude']; ?></td>
			              <td><?php echo $term['LongitudeNumber']; ?></td>
			              <td><?php echo $term['Latitude']; ?></td>
			              <td><?php echo $term['LatitudeNumber']; ?></td>

			              <!-- source term ID, scope notes -->
			        	</tr>
			        <?php } ?>
			      </tbody>
			    </table>
	      </div>
	    </div> <!-- /container -->

		<script type="text/javascript">
		$(document).ready(function() {
			$('.filter').click(function() {
				// alert("filter was clicked"); 
			}); 
			// $('.table > tr').click(function() {
			// 	alert("row was clicked"); 
			// }); 
		}); 
		</script>
	    
        <!-- Bootstrap core JavaScript
	    ================================================== -->
	    <!-- Placed at the end of the document so the pages load faster -->
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	    <script src="http://getbootstrap.com/dist/js/bootstrap.min.js"></script>
	    <script src="http://getbootstrap.com/assets/js/docs.min.js"></script>
	    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	    <script src="http://getbootstrap.com/assets/js/ie10-viewport-bug-workaround.js"></script>


	</body>
</html>


