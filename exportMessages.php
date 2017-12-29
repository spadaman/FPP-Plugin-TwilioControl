<?php
// Set headers to make the browser download the results as a csv file
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
$skipJSsettings = 1;
include_once "/opt/fpp/www/common.php";
include_once("/opt/fpp/www/config.php");
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";

	// filename for download

	
	$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
	
	$messagesResult = $db->query($messagesQuery) or die('Query failed');
	// Fetch the first row
	$row = $messagesResult->fetch(PDO::FETCH_ASSOC);
	
	// If no results are found, echo a message and stop
	if ($row == false){
		echo "No results";
		exit;
	}
	
	// Print the titles using the first line
	print_titles($row);
	// Iterate over the results and print each one in a line
	while ($row != false) {
		// Print the line
		echo implode(array_values($row), ",") . "\n";
		// Fetch the next line
		$row = $query->fetch(PDO::FETCH_ASSOC);
	}
	
	// Prints the column names
	function print_titles($row){
		echo implode(array_keys($row), ",") . "\n";
	}

?>