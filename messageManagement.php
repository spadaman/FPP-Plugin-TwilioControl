<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";


$messageQueuePluginPath = $settings['pluginDirectory']."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}

$blacklistFile = $settings['configDirectory']."/plugin.".$pluginName.".Blacklist";

if($_POST['addBlacklist'] != "") {
	logEntry("Adding a blacklist number");
	
	for($i=0;$i<count($_POST["addBlacklist"]);$i++)
	{
		
		$blacklistNumber=$_POST["phoneNumber"][$i];
		$messageText=$_POST["messageText"][$i];
	}
	
	//$blacklistNumber = $_POST['phoneNumber'];
	//$messageText = $_POST['messageText'];
	
	addBlacklist($messageText,$pluginName,$blacklistNumber);
	
	echo "Number: ".$blacklistNumber." added to ".$pluginName." Blacklist with message: ".$messageText;
	
}

$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-TwilioControl.git";



$profanityMessageQueueFile = $settings['configDirectory']."/plugin.".$pluginName.".ProfanityQueue";




$pluginMessages = getPluginMessages($pluginName, 0);

//print_r($pluginMessages);
$messageCount = count($pluginMessages);

echo "<form name=\"messageManagementBlacklist\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?plugin=".$pluginName."&page=messageManagement.php\"> \n";


echo "<center><h1><b>".$pluginName." Message Management</b></h1></center> <br/> \n";

//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";

echo "<tr> \n";
echo "<td> \n";
echo "Date Received \n";
echo "</td> \n";
echo "<td> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "</tr> \n";
for($i=0;$i<=$messageCount-1;$i++ ) {

	echo "<tr> \n";
	
	$messageQueueParts = explode("|",$pluginMessages[$i]);
	
	//unix timestamp
	echo "<td> \n";
	
	echo date('d M Y H:i:s',$messageQueueParts[0]);
	echo "</td> \n";
	
	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[1]);
	echo "<input type=\"hidden\" name=\"messageText[]\" value=\"".urldecode($messageQueueParts[1])."\"> \n";
	echo "</td> \n";
	
	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[3]);
	echo "<input type=\"hidden\" name=\"phoneNumber[]\" value=\"".urldecode($messageQueueParts[3])."\"> \n";
	echo "</td> \n";
	
	echo "<td> \n";
	echo "<input type=\"submit\" name=\"addBlacklist\" value=\"BLACKLIST\"> \n";
	echo "</td> \n";
	//plugin Subscription
	//echo "<td> \n";
	
	//echo $messageQueueParts[2];
	//echo "</td> \n";
	
	echo "</tr> \n";
	//echo $pluginMessages[$i];
}

echo "</table> \n";
//echo "</textarea> \n";

echo "<hr> \n";
echo "<center><b><h2>Profanity Messages</h2></b></center>\n";


$pluginMessages = null;
$messageCount = 0;
$pluginMessages = getPluginMessages($pluginName, 0, $profanityMessageQueueFile);

//print_r($pluginMessages);
$messageCount = count($pluginMessages);



//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";
echo "<tr> \n";
echo "<td> \n";
echo "Date Received \n";
echo "</td> \n";
echo "<td> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "</tr> \n";
for($i=0;$i<=$messageCount-1;$i++ ) {

	echo "<tr> \n";

	$messageQueueParts = explode("|",$pluginMessages[$i]);

	//unix timestamp
	echo "<td> \n";

	echo date('d M Y H:i:s',$messageQueueParts[0]);
	echo "</td> \n";

	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[1]);
	echo "<input type=\"hidden\" name=\"messageText[]\" value=\"".urldecode($messageQueueParts[1])."\"> \n";
	echo "</td> \n";
	
	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[3]);
	echo "<input type=\"hidden\" name=\"phoneNumber[]\" value=\"".urldecode($messageQueueParts[3])."\"> \n";
	echo "</td> \n";
	
	echo "<td> \n";
	echo "<input type=\"submit\" name=\"addBlacklist\" value=\"BLACKLIST\"> \n";
	echo "</td> \n";

	//plugin Subscription
	//echo "<td> \n";

	//echo $messageQueueParts[2];
	//echo "</td> \n";

	echo "</tr> \n";
	//echo $pluginMessages[$i];
}

echo "</table> \n";
//echo "</textarea> \n";

echo "</form> \n";


$pluginMessages = null;
$messageCount = 0;
$pluginMessages = getPluginMessages($pluginName, 0, $blacklistFile);

//print_r($pluginMessages);
$messageCount = count($pluginMessages);

echo "<hr> \n";
echo "<center><b><h2>Blacklisted Messages</h2></b></center>\n";


//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";
echo "<tr> \n";
echo "<td> \n";
echo "Date Received \n";
echo "</td> \n";
echo "<td> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "</tr> \n";
for($i=0;$i<=$messageCount-1;$i++ ) {

	echo "<tr> \n";

	$messageQueueParts = explode("|",$pluginMessages[$i]);

	//unix timestamp
	echo "<td> \n";

	echo date('d M Y H:i:s',$messageQueueParts[0]);
	echo "</td> \n";

	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[1]);
	
	echo "</td> \n";

	echo "<td> \n";
	//message data
	echo urldecode($messageQueueParts[3]);
	
	echo "</td> \n";

	echo "<td> \n";
	echo "<input type=\"submit\" name=\"addBlacklist\" value=\"BLACKLIST\"> \n";
	echo "</td> \n";

	//plugin Subscription
	//echo "<td> \n";

	//echo $messageQueueParts[2];
	//echo "</td> \n";

	echo "</tr> \n";
	//echo $pluginMessages[$i];
}

echo "</table> \n";
//echo "</textarea> \n";

?>
