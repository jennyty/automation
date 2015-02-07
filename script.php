<?php

$scriptPath = "/mnt/hgfs/Shared/deploy/backup_2015_02_07_090226/Agent/";

function doScriptSwitch($post, $get) {
  switch($get['action']) {
    case 'showscripts':
      showScripts();
      break;
    case 'processscript':
      processScript($_POST);
      break;
  }
}


function showScripts() {
  print "<h1>Scripts Home</h1>";
  print "<form  method='POST' action='$phpSelf?action=processscript'><table>"
    . "<tr><td>Device 1:</td><td><input type='text' name='device1'></td></tr>"
    . "<tr><td>Device 2:</td><td><input type='text' name='device2'></td></tr>"
    . "<tr><td colspan='2'><input type='submit'></td></tr>"
    . "</table></form>";

  $result = shell_exec("pgrep -f -a testcase");
  $processes = split("\n", $result);
  foreach ($processes as $process) {
    if ($process != "") {
      print "PROCESSES: " . $process . "<br />";
    }
  }
}

function processKillScript($hash) {

}

function processScript($hash) {
  global $scriptPath;
  print "<h1>{$hash['device1']}</h1>";
  
  $command="perl -I $scriptPath $scriptPath/testcase.pl -device {$hash['device1']} -testcase 1 > /dev/null &";
  print $command;
  shell_exec($command);
  // TODO: Propegate the device info
  header("Location: $phpSelf?action=showscripts");
  exit;
}

?>
