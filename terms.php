
<?php

	# TODO: Sanitize user inputs 


	if(isset($_POST['TermID'])) {
		update_db(); 
	}

	function update_db() {

		// Connect to the correct database
		$thes_conn = open_db("TMSThesaurus"); 

		// Figure out what changed 

		// Construct the query 
		$query = "UPDATE dbo.LIDOtermsAndGeo SET Term = ? WHERE TermID = ?"; 
		$params = array(&$_POST['Term'], &$_POST['TermID']); 
		$stmt = sqlsrv_prepare($thes_conn, $query, $params); 
		// Error checking here
		$result = sqlsrv_execute($stmt); 
		if ($result === false) {
			die("Problem with update"); 
		} 
		else {
			print ("success with udpate!"); 
		}
	}

	// TODO: Fix all the hardcodes 

	// Get info from post request and do basic error checking
	// INPUT contains obj_val and input_type
	$obj_val = $_POST['obj_val']; 
	$input_type = $_POST['input_type']; 			// Eitehr ObjectID or ObjectNumber
	
	$tms_conn = open_db("TMS"); 
	$thes_conn = open_db("TMSThesaurus"); 

	// TESTING STUFF
	// $query = "SELECT Term FROM dbo.LIDOtermsAndGeo WHERE TermID = ?";  
	// $params = array(106); 
	// $result = sqlsrv_query($thes_conn, $query, $params);
	// if ($result === false) {
	// 	die("Query failed"); 
	// }
	// $object = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
	// print_r($object); 
	// die() ;
	
	// Check that this is a valid object id or object number
	$query = "SELECT ObjectID, ObjectNumber, Title FROM dbo.Objects WHERE " . $input_type . " = ?";  
	$params = array($obj_val); 
	$result = sqlsrv_query($tms_conn, $query, $params);
	$object = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	// Check if query failed or didn't return anything. 
	// there are going to be three cases
	// 1. the query succeeds and returns data
	// 2. the query succeeds but returns nothing
	// 3. the query fails 
	if ($result === false) {
		die("Query failed"); 
	}
	if (is_null($object)) {
		die("Query returned nothing"); 
	}

	// Then find all associated term IDs for that object ID using the Thesxref table
	$query = "SELECT TermID FROM dbo.ThesXrefs WHERE ID = ?";
	$params = array($object["ObjectID"]); 
	$result = sqlsrv_query($tms_conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET)); 

	// Error checking 
	if ($result === false) {
		die("Query failed"); 
	}

	// Check that this returned something
	// if(!sqlsrv_num_rows($result)) {
	// 	die('No terms for object' . $object_id); 	
	// }

	// Get a list of all the associated term ids for an object 
	$term_ids = []; 
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		$term_ids[] = $row['TermID'];
	}

	// Then we grab all the data for each term ID using the LIDOtermsAndGeo table
	// NOTE: WHEN ONE OF THESE FIELDS IS NOT HERE THE ENTIRE QUERY FAILS
	$term_str = "('" . implode("','", $term_ids) . "')";
	$term_fields = array("Term", "TermID", "SourceTermID", "ScopeNote",
		"Longitude", "LongitudeNumber", "Latitude", "LatitudeNumber"); 
	$query = "SELECT " . implode(",", $term_fields) . " FROM dbo.LIDOtermsAndGeo WHERE TermID IN " . $term_str; 
	$result = sqlsrv_query($thes_conn, $query);

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
			print "Connection to " . $db . " could not be established";  
			die(print_r(sqlsrv_errors(), true)); 
		}

		return $db_conn; 
	}	



	// TESTING
	// if ($result === false) { print("false"); } 
	// else if (empty($result)) { print("empty"); }
	// else { print("true"); }
	 
?>

<?php 



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

  	<style>
    	.table thead>tr>th {border:none;}
	</style>

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
	        <h2 style="margin-bottom: 0px;"><?php echo $object['Title']; ?></h2>
	        <h4 style="margin-top: 10px;"><?php echo $object['ObjectNumber']; ?></h3>
	        <br>
	      </div>

	      <div class="row">
	        <table class="table borderless" style="margin-bottom:0px;">
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

<!-- 				              	<td class="col-md-1"><?php echo $term['TermID']; ?></td>
				              	<td class="col-md-2">
				              		<textarea class="form-control" type="text" name="Term"><?php echo $term['Term']; ?></textarea>
				              	</td>
				              	<td class="col-md-1"><?php echo $term['SourceTermID']; ?></td>
				              	<td class="col-md-4"><?php echo $term['ScopeNote']; ?></td>
				              	<td class="col-md-1"><?php echo $term['Longitude']; ?></td>
				              	<td class="col-md-1"><?php echo $term['LongitudeNumber']; ?></td>
				              	<td class="col-md-1"><?php echo $term['Latitude']; ?></td>
				              	<td class="col-md-1"><?php echo $term['LatitudeNumber']; ?></td> -->

				              	<!-- <td class="col-md-1">
				              		<input class="form-control" type="text" name="TermID" value=<?php echo $term['TermID']; ?>>
				              	</td> -->
				              	<td class="col-md-1" style="font-weight:bold;"><?php echo $term['TermID']; ?></td>
				              	<td class="col-md-2">
				              		<textarea class="form-control" rows="3" type="text" name="Term" style="resize:vertical;"><?php echo $term['Term']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<!-- <input class="form-control" type="text" name="SourceTermID" value=<?php echo $term['SourceTermID']; ?>> -->
				              		<textarea class="form-control" rows="1" type="text" name="SourceTermID" style="resize:none;"><?php echo $term['SourceTermID']; ?></textarea>
				              	</td>
				              	<td class="col-md-3">
				              		<textarea class="form-control" rows="3" type="text" name="ScopeNote" style="resize:vertical;"><?php echo $term['ScopeNote']; ?></textarea>
				              	</td>
				              	<td class="col-md-1">
				              		<input class="form-control" type="text" name="Longitude" value=<?php echo $term['Longitude']; ?>>
				              	</td>
				              	<td class="col-md-1">
				              		<input class="form-control" type="text" name="LongitudeNumber" value=<?php echo $term['LongitudeNumber']; ?>>
				              	</td>
				              	<td class="col-md-1">
				              		<input class="form-control" type="text" name="Latitude" value=<?php echo $term['Latitude']; ?>>
				              	</td>
				              	<td class="col-md-1">
				              		<input class="form-control" type="text" name="LatitudeNumber" value=<?php echo $term['LatitudeNumber']; ?>>
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
