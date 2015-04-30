
<?php

	# TODO: Sanitize user inputs 
// TODO: Fix all the hardcodes 

	$term_fields = array("Term", "TermID", "SourceTermID", "ScopeNote",
		"Longitude", "LongitudeNumber", "Latitude", "LatitudeNumber"); 

	if(isset($_POST['TermID'])) {
		update_db($term_fields); 
	}
	
	// Get info from post request and do basic error checking
	// INPUT contains obj_val and input_type
	$obj_val = $_POST['obj_val']; 
	$input_type = $_POST['input_type']; 			// Either ObjectID or ObjectNumber
	
	$tms_conn = open_db("TMS"); 
	$thes_conn = open_db("TMSThesaurus"); 

	// Check that this is a valid object id or object number
	$query = "SELECT ObjectID, ObjectNumber, Title FROM dbo.Objects WHERE " . $input_type . " = ?";  
	$params = array($obj_val); 

	// Check if the query completed successfully
	$result = sqlsrv_query($tms_conn, $query, $params);
	if ($result === false) {
		print_error("Query failed. Make sure the object ID/Number field is correct."); 
	}

	// Check if query returned any values. (succesful queries can still be empty)
	$object = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
	if (is_null($object)) {
		print_error($input_type . " " . $obj_val . " does not exist."); 
	}

	// Then find all associated term IDs for that object ID using the Thesxref table
	$query = "SELECT TermID FROM dbo.ThesXrefs WHERE ID = ?";
	$params = array($object["ObjectID"]); 
	
	// Check if query completed successfully 
	$result = sqlsrv_query($tms_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET)); 
	if ($result === false) {
		print_error("Query to find all term ids associated with ObjectID " . $object["ObjectID"] . " failed."); 
	}

	// Check if query returned any values 
	if(!sqlsrv_num_rows($result)) {
		print_error("No terms for object id " . $object["ObjectID"]); 	
	}

	// Get a list of all the associated term ids for an object 
	$term_ids = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_ids[] = $row['TermID'];
	}

	// Then we grab all the data for each term ID using the LIDOtermsAndGeo table
	// NOTE: WHEN ONE OF THESE FIELDS IS NOT HERE THE ENTIRE QUERY FAILS
	$term_str = "('" . implode("','", $term_ids) . "')";
	$query = "SELECT " . implode(",", $term_fields) . " FROM dbo.LIDOtermsAndGeo WHERE TermID IN " . $term_str; 
	$result = sqlsrv_query($thes_conn, $query);
	if ($result === false) {
		print_error("Query to get all term data for object id " . $object['ObjectID'] . "failed"); 
	}

	$term_info = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_info[] = $row; 
	}

	// Close the database connections 
	sqlsrv_close($thes_conn); 
	sqlsrv_close($tms_conn); 
	
	/***** 	helper functions *******/
	function open_db($db) {

		// Grab credentials from the ini file 
		$creds = parse_ini_file("config/app.ini");
		$server_name = $creds['server_name'];
		$user_name = $creds['user_name']; 
		$pw = $creds['pw']; 

		$conn_info = array("Database" => $db, "UID" => $user_name, "PWD" => $pw, "CharacterSet" => "UTF-8"); 
		$db_conn = sqlsrv_connect($server_name, $conn_info); 
		if (!$db_conn) {

			// Die on error and print out stack trace. 
			print_error("Connection to " . $db . " could not be established."); 
		}

		return $db_conn; 
	}	

	function update_db($term_fields) {

		// Connect to the correct database
		$thes_conn = open_db("TMSThesaurus"); 

		$query = "SELECT " . implode(",", $term_fields) . " FROM dbo.LIDOtermsAndGeo WHERE TermID = " . $_POST['TermID'];  
		$result = sqlsrv_query($thes_conn, $query); 
		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC); 

		foreach($term_fields as $field) {
			if (strcmp($row[$field], $_POST[$field]) !== 0) {
				$query = "UPDATE dbo.LIDOtermsAndGeo SET " . $field . " = ? WHERE TermID = ?"; 
				$params = array(&$_POST[$field], &$_POST['TermID']); 
				$stmt = sqlsrv_prepare($thes_conn, $query, $params); 
				if ($stmt === false) {
					print_error("Preparing update for term id " . $_POST['TermID'] . " failed"); 
				}

				// Error checking here
				$result = sqlsrv_execute($stmt); 
				if ($result === false) {
					print_error("Update for term id " . $_POST['TermID'] . " failed"); 
				} 
			}
		}
	}

	function print_error($msg) {
		echo "<h1>" . $msg . "</h1>"; 
		echo "<h2><a href='index.html'>Return to search</a></h2>"; 
		echo "<h2>Stack Trace:</h2>"; 
		die(var_dump(sqlsrv_errors())); 
	}

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

    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap core CSS -->
    <link href="http://getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="http://getbootstrap.com/dist/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="http://getbootstrap.com/examples/theme/theme.css" rel="stylesheet">

    <!-- My custom css --> 
    <link href="style.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]
    <script src="http://getbootstrap.com/assets/js/ie-emulation-modes-warning.js"></script>
  -->

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
	        <h2><?php echo $object['Title']; ?></h2>
	        <h4><?php echo $object['ObjectNumber']; ?></h3>
	      </div>

	      <div class="row">
	        <table class="table borderless">
		      <thead>
		        <tr>
		          <th class="col-md-1">TermID</th>
		          <th class="col-md-2">Term</th>
		          <th class="col-md-1">Source TermID</th>
		          <th class="col-md-3">Scope Note</th>
		          <th class="col-md-1">Long.</th>
		          <th class="col-md-1">Long. #</th>
		          <th class="col-md-1">Lat.</th>
		          <th class="col-md-1">Lat. #</th>
		          <th class="col-md-1"></th>
		        </tr>
		      </thead>
		    </table>
			
		    <?php foreach($term_info as $term) { ?>
			    <form action="terms.php" method="POST">
				    <table class="table">
				      	<tbody>
			      	
			            	<tr>
				            	<!-- Hidden inputs with post data to refresh page -->
				              	<input type="hidden" name="obj_val" value=<?php echo $obj_val; ?>>
				              	<input type="hidden" name="input_type" value=<?php echo $input_type; ?>>
				              	<input type="hidden" name="TermID" value=<?php echo $term['TermID']; ?>>

				              	<td class="col-md-1" id="t_id"><?php echo $term['TermID']; ?></td>
				              	<td class="col-md-2">
				              		<textarea class="form-control" rows="2" type="text" name="Term"><?php echo $term['Term']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<textarea class="form-control" rows="2" type="text" name="SourceTermID"><?php echo $term['SourceTermID']; ?></textarea>
				              	</td>
				              	<td class="col-md-3">
				              		<textarea class="form-control" rows="3" type="text" name="ScopeNote"><?php echo $term['ScopeNote']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<textarea class="form-control" rows="2" type="text" name="Longitude"><?php echo $term['Longitude']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<textarea class="form-control" rows="2" type="text" name="LongitudeNumber"><?php echo $term['LongitudeNumber']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<textarea class="form-control" rows="2" type="text" name="Latitude"><?php echo $term['Latitude']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<textarea class="form-control" rows="2" type="text" name="LatitudeNumber"><?php echo $term['LatitudeNumber']; ?></textarea>
				              	</td>
				              	<td class="col-md-1"><button type="submit" class="btn btn-success btn-sm">update</button></td>
			        		</tr>
			    	
				      	</tbody>
				    </table>
			    </form>
		   	<?php } ?>

	      </div>
	    </div> <!-- /container -->

		<script type="text/javascript">
		// $(document).ready(function() {
		// 	$('.filter').click(function() {
		// 		alert("filter was clicked"); 
		// 	}); 
		// }); 
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
