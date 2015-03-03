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
  print "<h1>Test Case Runner</h1>";
  print "<form  method='POST' action='$phpSelf?action=processscript'><table style='padding:15px'>"
    . "<tr><td>Test Case: </td><td><select name='testcase'>"
// TODO: This list should be dynamically generated
    . "<option value='1'>Milk Video: Open/Close</option>"
    . "<option value='2'>Milk Video: Launch</option>"
    . "<option value='3'>Milk Video: Power</option>"
    . "<option value='4'>Milk Video: MemoryTrack</option>"
    . "<option value='5'>Milk Video: Following (2 devices)</option>"
    . "<option disabled>------------</option>"
    . "<option value='6'>Milk Music: Open/Close</option>"
    . "<option value='7'>Milk Music: Launch</option>"
    . "<option value='8'>Milk Music: Power</option>"
    . "<option value='9'>Milk Music: MemoryTrack</option>"
    . "</select></td></tr>"
    //. "<tr><td>Device 1:</td><td><input type='text' name='device1'></td></tr>"
    //. "<tr><td>Device 2:</td><td><input type='text' name='device2'></td></tr>"
    . "<tr><td>Device 1:</td><td><input type='text' name='devices[]' size='30'></td></tr>"
    . "<tr><td>Device 2:</td><td><input type='text' name='devices[]' size='30'></td></tr>"
    . "<tr><td colspan='2' style='text-align:center'><input type='submit'></td></tr>"
    . "</table></form>";
  print getProcesses();
  print showResults();
}

function processKillScript($hash) {
  // TODO: Add user information and process
  $ip = $hash['ip'];
  shell_exec("pkill -f testcase.*$ip");
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
  
  $userId = getUserId();
  $command="perl -I $scriptPath $scriptPath/testcase.pl $deviceString -testcase {$hash['testcase']} -userId $userId > /dev/null &";
  print $command;
  shell_exec($command);
  goScriptHome();
}

function getProcesses() {
  global $phpSelf;
  $return = "";
  $result = shell_exec("pgrep -f -a testcase");
  $processes = split("\n", $result);
  $userId = getUserId();
  foreach ($processes as $process) {
    if (preg_match("/testcase\.pl.* (\d+\.\d+\.\d+\.\d+).*-userId $userId$/", $process, $match)) {
      $ip = $match[1];
      $return .= "Stop Process: <a href='$phpSelf?action=processkillscript&ip=$ip'>$ip</a><br />";
    }
  }
  return $return; 
}

function showResults() {
  $userId = getUserId();
  $base = "../downloads";
  $folder = sprintf("../downloads/%06s",  $userId);
  $command = "ls $folder";
  //print "$command<br />";
  $logs = explode("\n",shell_exec($command));
  rsort($logs);
  $table = "<br /><br /><table width='25%' style='text-align:center'>";
  $table .= "<th>Test Case Run Id</th><th>Log</th><th>Display</th>";
  foreach ($logs as $log) {
    if (preg_match('/_(\d+)\.log/', $log, $matches)) {
      # TODO: Only add graph link if exists in db
      $table .= "<tr><td>$matches[1]</td><td><a download href='$folder/$log'>Download</a></td><td><a href='$phpSelf?action=graph&testRunId=$matches[1]'>Graph</a></td></tr>";
    };
  }
  print $table;
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

function getUserId() {
  # TODO: Handle guest user
  $uid = $_SESSION['user']['id'];
  if (!isset($uid)) {
    $uid = 0;
  }
  return $uid;
}

function getTestCaseRunId() {
  # TODO: This should be replaced by the Test Rail Test Case Run Id value when the proper criteria is determined
  return time();
}

?>
