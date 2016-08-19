#!/usr/bin/php

<?php
//error_reporting(0);
//

$pluginName ="TwilioControl";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");
include_once("profanity.inc.php");

// this line loads the library
require('twilio/Services/Twilio.php');




$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

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

	if(urldecode($pluginSettings['DEBUG'] != "")) {
		$DEBUG=urldecode($pluginSettings['DEBUG']);
	}

	$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
	//page name to run the matrix code to output to matrix (remote or local);
	$MATRIX_EXEC_PAGE_NAME = "matrix.php";

	$EMAIL = urldecode($pluginSettings['EMAIL']);
	$PASSWORD = urldecode($pluginSettings['PASSWORD']);
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
	
	//if the command values do not have anything, set some defaults
	if(trim($playCommands) == "") {
		$playCommands = "PLAY";
		
	}
	
	if(trim($stopCommands) == "") {
		$stopCommands = "TERMINATE";
	
	}
	if(trim($repeatCommands) == "") {
		$repeatCommands = "REPEAT";
	
	}
	if(trim($statusCommands) == "") {
		$statusCommands = "STATUS";
	
	}
	
	


	if($DEBUG)
		print_r($pluginSettings);

		$playCommandsArray = explode(",",trim(strtoupper($playCommands)));
		$stopCommandsArray = explode(",",trim(strtoupper($stopCommands)));
		$repeatCommandsArray = explode(",",trim(strtoupper($repeatCommands)));
		$statusCommandsArray = explode(",",trim(strtoupper($statusCommands)));

		$COMMAND_ARRAY = explode(",",trim(strtoupper($VALID_COMMANDS)));
		
		$CONTROL_NUMBER_ARRAY = explode(",",$CONTROL_NUMBERS);


		$WHITELIST_NUMBER_ARRAY = explode(",",$WHITELIST_NUMBERS);

		$logFile = $settings['logDirectory']."/".$pluginName.".log";
		if($DEBUG)
			print_r($COMMAND_ARRAY);

			//give google voice time to sleep
			$GVSleepTime = 5;

			$ENABLED="";

			$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));



			if(($pid = lockHelper::lock()) === FALSE) {
				exit(0);

			}
			

			$client = new Services_Twilio($TSMS_account_sid, $TSMS_auth_token);
	
			//arg0 is  the program
			//arg1 is the first argument in the registration this will be --list
			//$DEBUG=true;
			//echo "Enabled: ".$ENABLED."<br/> \n";


			if(strtoupper($ENABLED) != "ON" && $ENABLED != "1") {
				logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use");
				lockHelper::unlock();
				exit(0);
			}

			if(isset($_POST['From']))
				$TSMS_from = $_POST['From'];
				if(isset($_POST['Body']))
					$TSMS_body = $_POST['Body'];
			
			if($DEBUG) {
				logEntry("Twilio account_sid: ".$TSMS_account_sid);
				logEntry("Twilio account pass: ".$TSMS_auth_token);
				
				logEntry("TSMS message from: ".$TSMS_from);
				logEntry("TSMS Message body: ".$TSMS_body);
				
				
			}
			
			//respond back 
			$TSMS_outgoingMessage = "You sent in message: ".$TSMS_body;
			
		//$client->account->messages->create(array( 'To' => $TSMS_from, 'From' => $TSMS_phoneNumber, 'Body' => $TSMS_outgoingMessage));


			$messageQueue = processNewMessages($SMS_TYPE="TWILIO", $TSMS_from, $TSMS_body);

			if($DEBUG)
				print_r($messageQueue);

				if($messageQueue == null) {
					lockHelper::unlock();
					exit(0);
				}

				if($DEBUG)
					print_r($messageQueue);


					//process the message queue or exit
					//check to see if the request is in the valid commands
					logEntry("Messages to process qty: ".count($messageQueue));

					for($i=0;$i<=count($messageQueue)-1;$i++) {
						//prevent messages to get entered more than once if in control and whitelist array
						$MESSAGE_USED=false;
						$from = $messageQueue[$i][0];
						$messageText = $messageQueue[$i][1];

						logEntry("processing message: ".$i." from: ".$from." Message: ".$messageText);

						$messageText= preg_replace('/\s+/', ' ', $messageText);
						$messageParts = explode(" ",$messageText);

						if(in_array($from,$CONTROL_NUMBER_ARRAY))
						{
							///message used is to make sure that we do not process a message twice if it is from a number that is both a whitelist AND control numbers
							$MESSAGE_USED=true;
							logEntry("Control number found: ".$from);
				

									
							if(in_array(trim(strtoupper($messageParts[0])),$playCommandsArray)) {
								logEntry( "SMS play cmd FOUND!!!");
								$CMD = "PLAY";
								
							}
									
								
							if(in_array(trim(strtoupper($messageParts[0])),$stopCommandsArray)) {
								logEntry( "SMS stop cmd FOUND!!!");
								$CMD = "STOP";
							
							} 
							
							if(in_array(trim(strtoupper($messageParts[0])),$repeatCommandsArray)) {
								logEntry( "SMS repeat cmd FOUND!!!");
								$CMD = "REPEAT";
								
							} 
							
							if(in_array(trim(strtoupper($messageParts[0])),$statusCommandsArray)) {
								logEntry( "SMS status cmd FOUND!!!");
								$CMD = "STATUS";
							}

							
						//	if(in_array(trim(strtoupper($messageParts[0])),$COMMAND_ARRAY)) {
						if($CMD != "") {
								logEntry("Command request: ".$messageText. " in uppercase is in control array");
								//do we have a playlist name?
								if($messageParts[1] != "") {
									processSMSCommand($from,$CMD,$messageParts[1]);
									//processSMSCommand($from,$messageParts[0],$messageParts[1]);
								} else {

									//play the configured playlist@!!!! from the plugin
									processSMSCommand($from,$CMD,$PLAYLIST_NAME);
									//processSMSCommand($from,$messageParts[0],$PLAYLIST_NAME);
								}
								
								
								$REPLY_TEXT_CMD = "Thank you - your command has been accepted from control number: ".$TSMS_from;
								
								$client->account->messages->create(array( 'To' => $TSMS_from, 'From' => $TSMS_phoneNumber, 'Body' => $REPLY_TEXT_CMD));
								
									
							} else {
								//generic message to display from control number just like a regular user
								processSMSMessage($from,$messageText);
								$client->account->messages->create(array( 'To' => $TSMS_from, 'From' => $TSMS_phoneNumber, 'Body' => $REPLY_TEXT));
							//	$gv->sendSMS($from,$REPLY_TEXT);
								//sleep(1);

								//processReadSentMessages();
							}
								
						}

						if(in_array($from,$WHITELIST_NUMBER_ARRAY) && !$MESSAGE_USED)

						{
							$MESSAGE_USED=true;
							logEntry($messageText. " is from a white listed number");
							processSMSMessage($from,$messageText);
							
						
						//	sleep(1);
							
							//processReadSentMessages();

						} else if(!$MESSAGE_USED){

							//not from a white listed or a control number so just a regular user
							//need to check for profanity
							//profanity checker API
							switch($PROFANITY_ENGINE) {
									
								case "NEUTRINO":
									$profanityCheck = check_for_profanity_neutrinoapi($messageText);
									break;

								case "WEBPURIFY":
									$profanityCheck = check_for_profanity_WebPurify($messageText);
									break;

								default:
									//default turn off profanity check
									$profanityCheck == false;
									break;
							}
							if(!$profanityCheck) {

								logEntry("Message: ".$messageText. " PASSED");
						
								$client->account->messages->create(array( 'To' => $TSMS_from, 'From' => $TSMS_phoneNumber, 'Body' => $REPLY_TEXT));
								processSMSMessage($TSMS_from,$messageText);
								sleep(1);
					

							} else {
								logEntry("message: ".$messageText." FAILED");
								$REPLY_TEXT = "Your message contains Profanity, Sorry. More messages like this will ban your phone number";

							//	$gv->sendSMS($from,$REPLY_TEXT);
								$client->account->messages->create(array( 'To' => $TSMS_from, 'From' => $TSMS_phoneNumber, 'Body' => $REPLY_TEXT));
								sleep(1);
							//	processReadSentMessages();

							}
						}


					}

					if($IMMEDIATE_OUTPUT != "ON" && $IMMEDIATE_OUTPUT != "1") {
						logEntry("NOT immediately outputting to matrix");
					} else {
						logEntry("IMMEDIATE OUTPUT ENABLED");
						logEntry("Matrix location: ".$MATRIX_LOCATION);
						logEntry("Matrix Exec page: ".$MATRIX_EXEC_PAGE_NAME);

						if($MATRIX_LOCATION != "127.0.0.1") {
							$remoteCMD = "/usr/bin/curl -s --basic 'http://".$MATRIX_LOCATION."/plugin.php?plugin=".$MATRIX_MESSAGE_PLUGIN_NAME."&page=".$MATRIX_EXEC_PAGE_NAME."&nopage=1' > /dev/null";
							logEntry("REMOTE MATRIX TRIGGER: ".$remoteCMD);
							exec($remoteCMD);
						} else {
							$IMMEDIATE_CMD = $settings['pluginDirectory']."/".$MATRIX_MESSAGE_PLUGIN_NAME."/matrix.php";
							logEntry("LOCAL command: ".$IMMEDIATE_CMD);
							exec($IMMEDIATE_CMD);
						}
					}


					lockHelper::unlock();
					exit(0);

					?>
