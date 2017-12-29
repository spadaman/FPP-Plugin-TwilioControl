<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');
$pluginName = "TwilioControl";
$tmpDownloadFilename = "/tmp/messages.csv";
$Plugin_DBName = "/home/fpp/media/config/FPP.".$pluginName.".db";
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
$tmpData = "";
// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');
$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
$messagesResult = $db->query($messagesQuery) or die('Query failed');
// loop over the rows, outputting them
while ($row = $messagesResult->fetchArray()) fputcsv($output, $row);
	//	while ($row = $messagesResult->fetchArray()) { $tmpData .= implode(array_values($row), ",") . "\n"; }
//	file_put_contents($tmpDownloadFilename, $tmpData);
	//header("Content-Disposition: attachment; filename=\"" . $tmpDownloadFilename. "\"");
	//header("Content-Type: text/csv");
	//header("Content-Length: " . filesize($tmpDownloadFilename));
	//header("Connection: close");
	//readfile($tmpDownloadFilename);
	//exit;
?>