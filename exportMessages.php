<?php
//$DEBUG=true;
$skipJSsettings = 1;
include_once "/opt/fpp/www/common.php";
include_once("/opt/fpp/www/config.php");
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";
if(isset($_POST['EXPORT'])) {
	// filename for download
	$filename = "website_data_" . date('Ymd') . ".csv";
	
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: text/csv");
	
	$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
	
	$messagesResult = $db->query($messagesQuery) or die('Query failed');
	$row = $messagesResult->fetchArray();
	$out = fopen('php://output', 'w');
	// print column header
	//fputcsv($out, array_keys($row));
	//or print content directly
	while ($row = $messagesResult->fetchArray()) {
		fputcsv($out, array_values($row));
	}
	fclose($out);
}
?>