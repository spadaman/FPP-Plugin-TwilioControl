<?php $skipJSsettings = 1;
//include_once "/opt/fpp/www/common.php";
//include_once("/opt/fpp/www/config.php");
//include_once 'functions.inc.php';
//include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";
$tmpDownloadFilename = "/tmp/messages.csv";
	// filename for download

	$tmpData = "";
	$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
	
	$messagesResult = $db->query($messagesQuery) or die('Query failed');
	// Fetch the first row
	while ($row = $messagesResult->fetchArray()) {
	

		$tmpData .= implode(array_values($row), ",") . "\n";
		// Fetch the next line
		
	}
	
	//Write a copy locally as well
	$tmpDownloadFilename= $settings['configDirectory'] . "/" . $backup_fname;
	//Write data into backup file
	file_put_contents($tmpDownloadFilename, $tmpData);
	
	///Generate the headers to prompt browser to start download
	header("Content-Disposition: attachment; filename=\"" . $tmpDownloadFilename. "\"");
	header("Content-Type: application/csv");
	header("Content-Length: " . filesize($tmpDownloadFilename));
	header("Connection: close");
	//Output the file
	readfile($tmpDownloadFilename);
	//die
	exit;

?>