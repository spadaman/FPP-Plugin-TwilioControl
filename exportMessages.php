<?php $skipJSsettings = 1; ?>
<?php include_once "/opt/fpp/www/common.php"; ?>
<?
$pluginName = "TwilioControl";
$tmpDownloadFilename = "/tmp/messages.csv";
$Plugin_DBName = $settings['configDirectory']."/FPP.".$pluginName.".db";
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
$tmpData = "";
	$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
	$messagesResult = $db->query($messagesQuery) or die('Query failed');
	while ($row = $messagesResult->fetchArray()) {
	$tmpData .= implode(array_values($row), ",") . "\n";
		// Fetch the next line
		}
file_put_contents($tmpDownloadFilename, $tmpData);
	header("Content-Disposition: attachment; filename=\"" . $tmpDownloadFilename. "\"");
	header("Content-Type: text/csv");
	header("Content-Length: " . filesize($tmpDownloadFilename));
//	header("Connection: close");
	readfile($tmpDownloadFilename);
	//die
	exit;
?>