<?php $skipJSsettings = 1; ?>
<?
$pluginName = "TwilioControl";
$tmpDownloadFilename = "/tmp/messages.csv";
$Plugin_DBName = "/home/fpp/media/config/FPP.".$pluginName.".db";
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
$tmpData = "";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');
// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');
$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
$messagesResult = $db->query($messagesQuery) or die('Query failed');
// output the column headings
//fputcsv($output, array('Column 1', 'Column 2', 'Column 3'));
// fetch the data
//mysql_connect('localhost', 'username', 'password');
//mysql_select_db('database');
//$rows = mysql_query('SELECT field1,field2,field3 FROM table');
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