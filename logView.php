<?php
/*
 * Easy PHP Tail
 * by: Thomas Depole
 * v1.0
 *
 * just fill in the varibles bellow, open in a web browser and tail away
 */
error_reporting(0);
//
//Version 1 for release
$pluginName ="TwilioControl";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

//MATRIX ACTIVE - true / false to catch more messages if they arrive
$MATRIX_ACTIVE = false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");
include_once("profanity.inc.php");

// this line loads the library
//require('Twilio/Services/Twilio.php');
require ('Twilio/autoload.php');



$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

$profanityMessageQueueFile = $settings['configDirectory']."/plugin.".$pluginName.".ProfanityQueue";

$blacklistFile = $settings['configDirectory']."/plugin.".$pluginName.".Blacklist";

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}

require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	$logFile = $settings['logDirectory']."/".$pluginName.".log";
	$DEBUG=urldecode($pluginSettings['DEBUG']);


	$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
	//page name to run the matrix code to output to matrix (remote or local);
	$MATRIX_EXEC_PAGE_NAME = "matrix.php";

	$PLAYLIST_NAME = urldecode($pluginSettings['PLAYLIST_NAME']);
	$WHITELIST_NUMBERS = urldecode($pluginSettings['WHITELIST_NUMBERS']);
	$CONTROL_NUMBERS = urldecode($pluginSettings['CONTROL_NUMBERS']);
	$REPLY_TEXT = urldecode($pluginSettings['REPLY_TEXT']);
	$VALID_COMMANDS = urldecode($pluginSettings['VALID_COMMANDS']);
	$IMMEDIATE_OUTPUT = urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
	$MATRIX_LOCATION = urldecode($pluginSettings['MATRIX_LOCATION']);
	$API_KEY = urldecode($pluginSettings['API_KEY']);
	$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
	$PROFANITY_ENGINE = urldecode($pluginSettings['PROFANITY_ENGINE']);

	$TSMS_account_sid = urldecode($pluginSettings['TSMS_ACCOUNT_SID']);
	$TSMS_auth_token = urldecode($pluginSettings['TSMS_AUTH_TOKEN']);
	$TSMS_phoneNumber = urldecode($pluginSettings['TSMS_PHONE_NUMBER']);

	$playCommands = urldecode($pluginSettings['PLAY_COMMANDS']);
	$stopCommands = urldecode($pluginSettings['STOP_COMMANDS']);
	$repeatCommands = urldecode($pluginSettings['REPEAT_COMMANDS']);
	$statusCommands = urldecode($pluginSettings['STATUS_COMMANDS']);

	$REMOTE_FPP_ENABLED = urldecode($pluginSettings['REMOTE_FPP_ENABLED']);
	$REMOTE_FPP_IP = urldecode($pluginSettings['REMOTE_FPP_IP']);

	$MATRIX_MODE = urldecode($pluginSettings['MATRIX_MODE']);

	$NAMES_PRE_TEXT = urldecode($pluginSettings['NAMES_PRE_TEXT']);

	$MATRIX_ACTIVE = urldecode($pluginSettings['MATRIX_ACTIVE']);
	if($MATRIX_MODE == "") {
		//default to free text
		$MATRIX_MODE = "FREE";
	}


	$ENABLED = urldecode($pluginSettings['ENABLED']);
	//$COMMAND_ARRAY = explode(",",trim(strtoupper($VALID_COMMANDS)));

	$CONTROL_NUMBER_ARRAY = explode(",",$CONTROL_NUMBERS);


	$WHITELIST_NUMBER_ARRAY = explode(",",$WHITELIST_NUMBERS);


	$PROFANITY_RESPONSE = urldecode($pluginSettings['PROFANITY_RESPONSE']);

	$PROFANITY_THRESHOLD =urldecode($pluginSettings['PROFANITY_THRESHOLD']);

	$TSMS_from = "";
	$TSMS_body = "";
	$TSMS_BODY_CONTAINED_HEX = false;

//$logFile = "/log_data.log"; // local path to log file

//
$interval = 1000; //how often it checks the log file for changes, min 100
$textColor = ""; //use CSS color
// Don't have to change anything bellow
if(!$textColor) $textColor = "white";
if($interval < 100)  $interval = 100;
if($_GET['getLog']){
	echo file_get_contents($logFile);
}else{
	?>
<html>
	<title>Log</title>
	<style>
		@import url(http://fonts.googleapis.com/css?family=Ubuntu);
		body{
			background-color: black;
			color: <?php echo $textColor; ?>;
			font-family: 'Ubuntu', sans-serif;
			font-size: 16px;
			line-height: 20px;	
		}
		h4{
			font-size: 18px;
			line-height: 22px;
			color: #353535;
		}
		#log {
			position: relative;
			top: -34px;
		}
		#scrollLock{
			width:2px;
			height: 2px;
			overflow:visible;
		}
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script>
		setInterval(readLogFile, <?php echo $interval; ?>);
		window.onload = readLogFile; 
		var pathname = window.location.pathname;
		var scrollLock = true;
		
		$(document).ready(function(){
			$('.disableScrollLock').click(function(){
				$("html,body").clearQueue()
				$(".disableScrollLock").hide();
				$(".enableScrollLock").show();
				scrollLock = false;
			});
			$('.enableScrollLock').click(function(){
				$("html,body").clearQueue()
				$(".enableScrollLock").hide();
				$(".disableScrollLock").show();
				scrollLock = true;
			});
		});
		function readLogFile(){
			$.get(pathname, { getLog : "true" }, function(data) {
				data = data.replace(new RegExp("\n", "g"), "<br />");
		        $("#log").html(data);
		        
		        if(scrollLock == true) { $('html,body').animate({scrollTop: $("#scrollLock").offset().top}, <?php echo $interval; ?>) };
		    });
		}
	</script>
	<body>
		<h4><?php echo $logFile; ?></h4>
		<div id="log">
			
		</div>
		<div id="scrollLock"> <input class="disableScrollLock" type="button" value="Disable Scroll Lock" /> <input class="enableScrollLock" style="display: none;" type="button" value="Enable Scroll Lock" /></div>
	</body>
</html>
<?php  } ?>