<?php

$scriptPath = "/opt/samsung/client";

function doScriptSwitch($post, $get) {
  switch($get['action']) {
    case 'showscripts':
      showScripts();
      break;
    case 'processscript':
      processScript($post);
      break;
    case 'processkillscript':
      processKillScript($get);
      break;
  }
}

function showScripts() {
  global $phpSelf;
  print "<h1>Script Runner</h1>";
  print "<form  method='POST' action='$phpSelf?action=processscript'><table style='padding:15px'>"
    . "<tr><td>Test Case: </td><td><select name='testcase'>"
// TODO: This list should be dynamically generated
    . "<option value='1'>Milk Video: Open/Close</option>"
    . "<option value='2'>Milk Video: Launch</option>"
    . "<option value='3'>Milk Video: Following</option>"
    . "</select></td></tr>"
    //. "<tr><td>Device 1:</td><td><input type='text' name='device1'></td></tr>"
    //. "<tr><td>Device 2:</td><td><input type='text' name='device2'></td></tr>"
    . "<tr><td>Device 1:</td><td><input type='text' name='devices[]' size='30'></td></tr>"
    . "<tr><td>Device 2:</td><td><input type='text' name='devices[]' size='30'></td></tr>"
    . "<tr><td colspan='2' style='text-align:center'><input type='submit'></td></tr>"
    . "</table></form>";

  print getProcesses();
}

function processKillScript($hash) {
  // TODO: Add user information and process
  shell_exec("pkill -f testcase");
  print_r($hash);
  goScriptHome();
}

function processScript($hash) {
  global $scriptPath;
  //print "<h1>{$hash['device1']}</h1>";
  //print "<h1>{$hash['testcase']}</h1>";
  //print "<h1>" . print_r($hash, false) . "</h1>";
  $deviceString = "";
  foreach ($hash['devices'] as $device) {
    if ($device != "") {
      if (!isDeviceAvailable($device)) {
        // TODO: Set Error message here
        setErrorMessage("Error: Device is in use($device)");
        goScriptHome();
      }
      $deviceString .= "-device $device ";
    }
  }
  if ($deviceString == "") {
    setErrorMessage("Error: Device 1 must be defined");
    goScriptHome(); 
  }
  
  $command="perl -I $scriptPath $scriptPath/testcase.pl $deviceString -testcase {$hash['testcase']} > /dev/null &";
  print $command;
  shell_exec($command);
  goScriptHome();
}

function getProcesses() {
  global $phpSelf;
  $return = "";
  $result = shell_exec("pgrep -f -a testcase");
  $processes = split("\n", $result);
  foreach ($processes as $process) {
    if (preg_match("/testcase\.pl.* (\d+\.\d+\.\d+\.\d+)/", $process, $match)) {
      $return .= "Stop Process: <a href='$phpSelf?action=processkillscript'>" . $match[1] . "</a><br />";
    }
  }
  return $return; 
}

function isDeviceAvailable($device) {
  $matchString = "";
  if (preg_match("/>$device</", getProcesses())) {
    return false;
  }
  return true;
}

function goScriptHome() {
  global $phpSelf;
  header("Location: $phpSelf?action=showscripts");
  exit;
}

?>
