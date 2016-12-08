<?php


include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";
$pluginVersion ="2.8";
$PLAYLIST_NAME="";
$MAJOR = "98";
$MINOR = "01";
$eventExtension = ".fevt";
//2.8 - Dec 2 2016 - do not add a message to the queue if it is profanity for those running not in immeidate mode it could send that out

//2.7 - Dec 2 2016 - Added more checking and message queue file managment
//2.6 - Dec 2 2016 - Profanity Threshold added and the ability to add your own reply text for the message your message contains profanity
//2.5 - Dec 2 2016 - added blacklist

//2.4 - Dec 2  2016 - Profanity fixes

//2.3 - Nov 30 2016 - Add default commands to new install

//2.2 - Nov 30 2016 - remove the /usr/bin/php from the header of TSMS.php - causing debugger errors on Twilio side. May need to 
//investigate providing a proper response XML to Twilio as well.


//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;

$SMSEventFile = $eventDirectory."/".$MAJOR."_".$MINOR.$eventExtension;
$SMSGETScriptFilename = $scriptDirectory."/".$pluginName."_GET.sh";

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


$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-TwilioControl.git";


$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


createSMSSequenceFiles();


logEntry("plugin update file: ".$pluginUpdateFile);


if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{
	


//	echo "Writring config fie <br/> \n";
	
	WriteSettingToFile("PLAYLIST_NAME",urlencode($_POST["PLAYLIST_NAME"]),$pluginName);
	WriteSettingToFile("WHITELIST_NUMBERS",urlencode($_POST["WHITELIST_NUMBERS"]),$pluginName);
	WriteSettingToFile("CONTROL_NUMBERS",urlencode($_POST["CONTROL_NUMBERS"]),$pluginName);
	WriteSettingToFile("REPLY_TEXT",urlencode($_POST["REPLY_TEXT"]),$pluginName);
	//WriteSettingToFile("VALID_COMMANDS",urlencode($_POST["VALID_COMMANDS"]),$pluginName);
	//WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("API_USER_ID",urlencode($_POST["API_USER_ID"]),$pluginName);
	WriteSettingToFile("API_KEY",urlencode($_POST["API_KEY"]),$pluginName);
	//WriteSettingToFile("IMMEDIATE_OUTPUT",urlencode($_POST["IMMEDIATE_OUTPUT"]),$pluginName);
	WriteSettingToFile("MATRIX_LOCATION",urlencode($_POST["MATRIX_LOCATION"]),$pluginName);
	
	WriteSettingToFile("PROFANITY_ENGINE",urlencode($_POST["PROFANITY_ENGINE"]),$pluginName);
	
	WriteSettingToFile("TSMS_ACCOUNT_SID",urlencode($_POST["TSMS_ACCOUNT_SID"]),$pluginName);
	WriteSettingToFile("TSMS_AUTH_TOKEN",urlencode($_POST["TSMS_AUTH_TOKEN"]),$pluginName);
	WriteSettingToFile("TSMS_PHONE_NUMBER",urlencode($_POST["TSMS_PHONE_NUMBER"]),$pluginName);
	
	//fpp command tables
	WriteSettingToFile("PLAY_COMMANDS",urlencode($_POST["PLAY_COMMANDS"]),$pluginName);
	WriteSettingToFile("STOP_COMMANDS",urlencode($_POST["STOP_COMMANDS"]),$pluginName);
	WriteSettingToFile("REPEAT_COMMANDS",urlencode($_POST["REPEAT_COMMANDS"]),$pluginName);
	WriteSettingToFile("STATUS_COMMANDS",urlencode($_POST["STATUS_COMMANDS"]),$pluginName);
	
	
	WriteSettingToFile("REMOTE_FPP_IP",urlencode($_POST["REMOTE_FPP_IP"]),$pluginName);
	
	WriteSettingToFile("MATRIX_MODE",urlencode($_POST["MATRIX_MODE"]),$pluginName);
	WriteSettingToFile("NAMES_PRE_TEXT",urlencode($_POST["NAMES_PRE_TEXT"]),$pluginName);
	
	WriteSettingToFile("PROFANITY_RESPONSE",urlencode($_POST["PROFANITY_RESPONSE"]),$pluginName);
	
	WriteSettingToFile("PROFANITY_THRESHOLD",urlencode($_POST["PROFANITY_THRESHOLD"]),$pluginName);
}

	
	$PLAYLIST_NAME = urldecode($pluginSettings['PLAYLIST_NAME']);
	$REMOTE_FPP_ENABLED = urldecode($pluginSettings['REMOTE_FPP_ENABLED']);
	$REMOTE_FPP_IP = urldecode($pluginSettings['REMOTE_FPP_IP']);
	$WHITELIST_NUMBERS = urldecode($pluginSettings['WHITELIST_NUMBERS']);
	$CONTROL_NUMBERS = urldecode($pluginSettings['CONTROL_NUMBERS']);
	$REPLY_TEXT = urldecode($pluginSettings['REPLY_TEXT']);
	//$VALID_COMMANDS = urldecode($pluginSettings['VALID_COMMANDS']);
	
	$LAST_READ = $pluginSettings['LAST_READ'];
	$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
	$API_KEY = urldecode($pluginSettings['API_KEY']);
	//$IMMEDIATE_OUTPUT = $pluginSettings['IMMEDIATE_OUTPUT'];
	$MATRIX_LOCATION = $pluginSettings['MATRIX_LOCATION'];

	//$ENABLED = $pluginSettings['ENABLED'];
	$PROFANITY_ENGINE = urldecode($pluginSettings['PROFANITY_ENGINE']);
	$DEBUG = urldecode($pluginSettings['DEBUG']);
	
	$TSMS_account_sid = urldecode($pluginSettings['TSMS_ACCOUNT_SID']);//'ACde7921f611cb46d9b972447d9b3b2ea9';
	$TSMS_auth_token = urldecode($pluginSettings['TSMS_AUTH_TOKEN']);//'6da171f99cb77e267f48ff3e6cbe1a34';
	$TSMS_phoneNumber = urldecode($pluginSettings['TSMS_PHONE_NUMBER']);//"+17209999485";
	
	$playCommands = urldecode($pluginSettings['PLAY_COMMANDS']);
	$stopCommands = urldecode($pluginSettings['STOP_COMMANDS']);
	$repeatCommands = urldecode($pluginSettings['REPEAT_COMMANDS']);
	$statusCommands = urldecode($pluginSettings['STATUS_COMMANDS']);
	
	$MATRIX_MODE = urldecode($pluginSettings['MATRIX_MODE']);
	
	$NAMES_PRE_TEXT = urldecode($pluginSettings['NAMES_PRE_TEXT']);
	
	$PROFANITY_RESPONSE = urldecode($pluginSettings['PROFANITY_RESPONSE']);
	
	$PROFANITY_THRESHOLD =urldecode($pluginSettings['PROFANITY_THRESHOLD']);
	
	if (trim($PROFANITY_RESPONSE) == "") {
		$PROFANITY_RESPONSE = "Your message contains Profanity, Sorry. More messages like this will ban your phone number";
	}
	
	if($PROFANITY_THRESHOLD =="") {
		$PROFANITY_THRESHOLD = "3";
	}
	
	//if($DEBUG)
		//print_r($pluginSettings);
		
	//load default commands if first install
	if(trim($playCommands) == "") {
		$playCommands = "play";
	}
	
	if(trim($stopCommands) == "") {
		$stopCommands = "terminate";
	}
	
	if(trim($repeatCommands) == "") {
		$repeatCommands = "repeat";
	}
	
	if(trim($statusCommands) == "") {
		$statusCommands = "status";
	}
	

if($REPLY_TEXT == "") {
	$REPLY_TEXT = "Thank you for your message, it has been added to the Queue";
}

	
	//crate the event file
	function createSMSEventFile() {
		
		global $SMSEventFile,$pluginName,$MAJOR,$MINOR,$SMSGETScriptFilename;
		
		
		logEntry("Creating  event file: ".$SMSEventFile);
		
		$data = "";
		$data .= "majorID=".$MAJOR."\n";
		$data .= "minorID=".$MINOR."\n";
		
		$data .= "name='".$pluginName."_GET"."'\n";
			
		$data .= "effect=''\n";
		$data .="startChannel=\n";
		$data .= "script='".pathinfo($SMSGETScriptFilename,PATHINFO_BASENAME)."'\n";
		
		
		
		$fs = fopen($SMSEventFile,"w");
		fputs($fs, $data);
		fclose($fs);
	}
	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
	}

?>

<html>
<head>
</head>

<div id="TwilioControl" class="settings">
<fieldset>
<legend><?php echo $pluginName." Version: ".$pluginVersion;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None
</ul>

<p>Configuration:
<ul>
<li>Configure your whitelist of numbers, and your control number</li>
<li>Your control numbers, and white list numbers should be comma separated</li>
<li>Control numbers can send valid commands to be processed</li>
<li>ALL control numbers will get status commands when including the SMS-STATUS-SEND.FSEQ sequence in a playlist</li>
<li>The phone numbers for any field need to be in the format of +(countryCode)(number) example USA number 800-555-1212 = +18005551212</li>
</ul>

<ul>
<li>This plugin allows the use of two profanity checkers. NeutrinoAPI and WebPurify</li>
<li>Users have reported that Web Purify is much more thurough than Neutrino</li>
<li>NeutrinoAPI profanity checker located at: https://www.neutrinoapi.com/api/bad-word-filter/</li>
<li>WebPurify is located at http://webpurify.com</li>
<li>You will need to visit their site and generate a userid and API Key</li>
<li>NOTE: Each have limited checks on FREE accounts</li>

<p>DISCLAIMER:
<ul>
<li>The Author and supporters of this plugin are NOT responsible for SMS charges that may be incurred by using this plugin</li>
<li>Check with your mobile provider BEFORE using this to ensure your account status</li>
</ul>


<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'];?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
//will add a 'reset' to this later

echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";


$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

//if($ENABLED== 1 || $ENABLED == "on") {
	//	echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
PrintSettingCheckbox("Plugin: ".$pluginName." ", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//	} else {
//		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}

echo "<p/> \n";
echo "Immediately output to Matrix (Run MATRIX plugin): ";

//if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
//	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
	PrintSettingCheckbox("Immediate output to Matrix", "IMMEDIATE_OUTPUT", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//} else {
	//echo "<input type=\"checkbox\"  name=\"IMMEDIATE_OUTPUT\"> \n";
//}
echo "<p/> \n";
?>
MATRIX Message Plugin Location: (IP Address. default 127.0.0.1);
<input type="text" size="15" value="<? if($MATRIX_LOCATION !="" ) { echo $MATRIX_LOCATION; } else { echo "127.0.0.1";}?>" name="MATRIX_LOCATION" id="MATRIX_LOCATION"></input>
<p/>
<?
echo "<p/> \n";
echo "Send Commands to Remote FPP: ";

//if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
//	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
PrintSettingCheckbox("Remote FPP Commands", "REMOTE_FPP_ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//} else {
//echo "<input type=\"checkbox\"  name=\"IMMEDIATE_OUTPUT\"> \n";
//}
echo "<p/> \n";
?>
Remote FPP IP : (IP Address. default 127.0.0.1);
<input type="text" size="15" value="<? if($REMOTE_FPP_IP !="" ) { echo $REMOTE_FPP_IP; } else { echo "127.0.0.1";}?>" name="REMOTE_FPP_IP" id="REMOTE_FPP_IP"></input>
<p/>
<?
echo "<p/> \n";

echo "Playlist Name: ";
PrintMediaOptions();

 function PrintMediaOptions()
  {
	  global $playlistDirectory;

		echo "<select name=\"PLAYLIST_NAME\">";

	$playlistEntries = scandir($playlistDirectory);
	sort($playlistEntries);
	
    foreach($playlistEntries as $playlist) 
    {
      if($playlist != '.' && $playlist != '..')
      {
        echo "<option value=\"" . $playlist . "\">" . $playlist . "</option>";
      }
	}
  echo "</select>";
  }

echo "<p/> \n";



echo "Twilio PHONE NUMBER: \n";

echo "<input type=\"text\" name=\"TSMS_PHONE_NUMBER\" size=\"16\" value=\"".$TSMS_phoneNumber."\"> \n";


echo "<p/> \n";

echo "Twilio Account SID: \n";

echo "<input type=\"text\" name=\"TSMS_ACCOUNT_SID\" size=\"32\" value=\"".$TSMS_account_sid."\"> \n";


echo "<p/> \n";

echo "Twilio Auth Token: \n";

echo "<input type=\"text\" name=\"TSMS_AUTH_TOKEN\" size=\"64\" value=\"".$TSMS_auth_token."\"> \n";


echo "<p/> \n";


printValidFPPCommands();

//echo "Valid Commands: \n";

//echo "<input type=\"text\" name=\"VALID_COMMANDS\" size=\"16\" value=\"".$VALID_COMMANDS."\"> \n";


echo "<p/> \n";

echo "Reply Text: \n";

echo "<input type=\"text\" name=\"REPLY_TEXT\" size=\"64\" value=\"".$REPLY_TEXT."\"> \n";
echo "<p/> \n";

echo "White List Numbers(comma separated): \n";

echo "<input type=\"text\" name=\"WHITELIST_NUMBERS\" size=\"64\" value=\"".$WHITELIST_NUMBERS."\"> \n";


echo "<p/> \n";

echo "CONTROL NUMBER: \n";

echo "<input type=\"text\" name=\"CONTROL_NUMBERS\" size=\"16\" value=\"".$CONTROL_NUMBERS."\"> \n";


echo "<p/> \n";

echo "Matrix Mode (Names Only, or Free Text): \n";
echo "<select name=\"MATRIX_MODE\"> \n";
if($MATRIX_MODE !="" ) {
	switch ($MATRIX_MODE)
	{
		case "NAMES":
			echo "<option selected value=\"".$MATRIX_MODE."\">Names Only</option> \n";
			echo "<option value=\"FREE\">FREE TEXT</option> \n";
			break;

		case "FREE":
			echo "<option selected value=\"".$MATRIX_MODE."\">Free Text</option> \n";
			echo "<option value=\"NAMES\">NAMES Only</option> \n";
			break;
				
		default:
			echo "<option value=\"FREE\">Free Text</option> \n";
			echo "<option value=\"NAMES\">NAMES Only</option> \n";
			break;

	}

} else {

	echo "<option value=\"FREE\">Free Text</option> \n";
	echo "<option value=\"NAMES\">NAMES Only</option> \n";
}
echo "</select> \n";
echo "<p/> \n";

echo "If (NAMES) Mode, the Pre text to send in front of their name (e.g. Merry Christmas <name>) \n";
echo "<br/> \n";

echo "<input type=\"text\" name=\"NAMES_PRE_TEXT\" size=\"64\" value=\"".$NAMES_PRE_TEXT."\"> \n";

 

echo "<p/> \n";

echo "Profanity Engine: \n";
echo "<select name=\"PROFANITY_ENGINE\"> \n";
	if($PROFANITY_ENGINE !="" ) {
              switch ($PROFANITY_ENGINE)
				{
					case "NEUTRINO":
                                		echo "<option selected value=\"".$PROFANITY_ENGINE."\">".$PROFANITY_ENGINE."</option> \n";
                                		echo "<option value=\"WEBPURIFY\">WEBPURIFY</option> \n";
                                		break;
                                		
					case "WEBPURIFY":
                                		echo "<option selected value=\"".$PROFANITY_ENGINE."\">".$PROFANITY_ENGINE."</option> \n";
                                		echo "<option value=\"NEUTRINO\">NEUTRINO</option> \n";
                        			break;
			
					default:
						echo "<option value=\"NEUTRINO\">NEUTRINO</option> \n";
						echo "<option value=\"WEBPURIFY\">WEBPURIFY</option> \n";
							break;
	
				}
	
			} else {

                                echo "<option value=\"NEUTRINO\">NEUTRINO</option> \n";
                                echo "<option value=\"WEBPURIFY\">WEBPURIFY</option> \n";
			}
               
			echo "</select> \n";
echo "<p/> \n";
echo "Profanity API User ID: \n";

echo "<input type=\"text\" name=\"API_USER_ID\" size=\"32\" value=\"".$API_USER_ID."\"> \n";


echo "<p/> \n";

echo "Profanity API KEY: \n";

echo "<input type=\"text\" name=\"API_KEY\" size=\"64\" value=\"".$API_KEY."\"> \n";


echo "<p/> \n";

echo "<hr> \n";
echo "Responses: \n";
echo "<br/> \n";
echo "Response when the text contains profanity: \n";
echo "<p/> \n";



echo "<input type=\"text\" name=\"PROFANITY_RESPONSE\" size=\"64\" value=\"".$PROFANITY_RESPONSE."\"> \n";

echo "<p/> \n";

echo "Profanity Threshold. If a user sends in more than this number of profanity messages, will alert the control number: \n";
echo "<p/> \n";



echo "<input type=\"text\" name=\"PROFANITY_THRESHOLD\" size=\"3\" value=\"".$PROFANITY_THRESHOLD."\"> \n";

echo "<p/> \n";
echo "DEBUG: ";

//if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
//	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
PrintSettingCheckbox("DEBUG", "DEBUG", $restart = 0, $reboot = 0, "true", "", $pluginName = $pluginName, $callbackName = "");

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>


<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR']?>/plugin.php?plugin=<?echo $pluginName;?>&page=messageManagement.php">
<input id="MessageManagementButton" name="Message Management" type="submit" value="Message Management">
</form>

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR']?>/plugin.php?plugin=<?echo $pluginName;?>&page=logView.php">
<input id="LogViewer" name="logviewer" type="submit" value="Log Viewer">
</form>

<?
echo "<a href=\"http://".$_SERVER['SERVER_ADDR']."/plugin.php?plugin=".$pluginName."&page=logView.php target=\"_top\">Log Viewer</a> \n";
?>
<p>To report a bug, please file it against the sms Control plugin project on Git:<? echo $gitURL;?> 
</fieldset>
</div>
<br />
</html>
