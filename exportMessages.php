<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TwilioMessages.csv');
$pluginName = "TwilioControl";
$Plugin_DBName = "/home/fpp/media/config/FPP.".$pluginName.".db";
    
if(file_exists($settings['configDirectory'] . "/FPP.FPP-Plugin-MessageQueue.db")) {
    $Plugin_DBName = $settings['configDirectory'] . "/FPP.FPP-Plugin-MessageQueue.db";
} else if(file_exists($settings['configDirectory'] . "/FPP.MessageQueue.db")) {
    $Plugin_DBName = $settings['configDirectory'] . "/FPP.MessageQueue.db";
}

$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');
$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
$messagesResult = $db->query($messagesQuery) or die('Query failed');
//loop over the rows, outputting them
while ($row = $messagesResult->fetchArray()) fputcsv($output, $row);
exit;
?>
