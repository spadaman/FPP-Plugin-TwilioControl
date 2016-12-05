<?php

if (isset($_REQUEST["log"])) {
	$log = trim($_REQUEST["log"]);
} else {
	echo "No log file specified.<br>";
	exit;
}

$lines = 20;
if (isset($_REQUEST["lines"])) {
	$lines = trim($_REQUEST["lines"]);
}

if (!file_exists($log)) {
	echo "$log does not exist.<br>";
	exit;
}

$cmd = "tail -$lines $log";
exec("$cmd 2>&1", $output);

foreach ($output as $line) {
	echo "$line<br>";
}
?>
<script language="javascript" type="text/JavaScript">
        function getLog(log, lines) {
                var url = "getLogFile.php?log=" + log + "&lines=" + lines;
                request.open("GET", url, true);
                request.onreadystatechange = updatePage;
                request.send(null);
        }

        function tail(command,log,lines) {
                if (command == "start") {
                        document.getElementById("watchStart").disabled = true;
                        document.getElementById("watchStop").disabled = false;
                        timer = setInterval(function() {getLog(log,lines);},5000);
                } else {
                        document.getElementById("watchStart").disabled = false;
                        document.getElementById("watchStop").disabled = true;
                        clearTimeout(timer);
                }
        }

        function updatePage() {
                if (request.readyState == 4) {
                        if (request.status == 200) {
                                var currentLogValue = request.responseText.split("\n");
                                eval(currentLogValue);

                                document.getElementById("log").innerHTML = currentLogValue;
                        }
                }
        }

        var request = (window.XMLHttpRequest) ? new XMLHttpRequest() : (window.ActiveXObject ? new window.ActiveXObject("Microsoft.XMLHTTP") : false);
</script>

<html>



<div id="log" style="width:100%; height:90%; overflow:auto;"></div>

<td style="width:100px;">Watch</td>
                        <td>
                                <input type="button" style="width:40px; 0px" id="watchStart" name="watch" value="Start" onclick="tail('start',document.getElementById('logfile').value, document.getElementById('loglength').value);">
                                <input type="button" style="width:40px; 0px" id="watchStop" name="watch" value="Stop" disabled=true onclick="tail('stop','','');">
                        </td>


</html>