<html><head><title>Device Inventory</title>
<link rel='stylesheet' type='text/css' href='style.css' />

</head><body><center>

<?php
require "db.inc";
require "testrail.inc";
require "authentication.inc";
require "script.php";

extract($_GET);
define('ROOTPATH', dirname(__FILE__) . '/');
#print "<h1>".ROOTPATH."</h1>";
$phpSelf = "dashboard.php";
$table1="device";

session_start();

authenticate($action);

if (isset($_SESSION['user'])) {

### Display buttons at the top ###
print "<div style='width:100%;text-align:center;background-color:orange'>"
  . "<a href=$phpSelf?action=showdevices>Devices</a>"
  . " | <a href=$phpSelf?action=showscripts>Scripts</a>"
  . " | <a href=$phpSelf?action=graph&testRunId=0>Graph</a>"
  . " | <a href=$phpSelf?action=updateproperties>Update Properties</a>"
  . " | <a href=$phpSelf?action=help>Help</a>"
  . " | <a href=$phpSelf?action=showaccounts>Test Accounts</a>"
  . " | <a href=$phpSelf?action=logout>Log Out</a>"
  . "</div>";
  print getErrorMessage();
  clearErrorMessage();
  print "<h3>USER: {$_SESSION['user']['name']}</h3>";
}

switch ($action) {
  case 'showdevices':
    showDevices();
    break;
  case 'showaccounts':
    showAccounts();
    break;
  case 'adddevice':
    addDevice();
    break;
  case 'addaccount':
    addAccount();
    break;
  case 'editdevice':
    editDevice();
    break;
  case 'checkout':
    processCheckOut($_GET);
    break;
  case 'checkin':
    processCheckIn($_GET);
    break;
  case 'checkoutaccount':
    processCheckOutAccount($_GET);
    break;
  case 'checkinaccount':
    processCheckInAccount($_GET);
    break;
  case 'graph':
    doGraph($_GET);
    break;
  case 'processdevice':
    processDevice($_POST);
    break;
  case 'processaccount':
    processAccount($_POST);
    break;
  case 'updateproperties':
    updateProperties();
    break;
  case 'processupdateprop':
    processUpdateProp($_POST);
    break;
  case 'help':
    doHelp();
    break;
  default:
    doScriptSwitch($_POST, $_GET);
    // Add Authentification
    break;
}

function setErrorMessage($message) {
  $_SESSION['errormessage'] = "<span style='background-color:red'> " . $message . " </span><br />";
}

function getErrorMessage() {
  return $_SESSION['errormessage'];
}

function clearErrorMessage() {
  $_SESSION['errormessage']="";
}

function gotoHome() {
  header("Location: $phpSelf?action=showdevices");
  exit;
}

function gotoAccountPage() {
  header("Location: $phpSelf?action=showaccounts");
  exit;
}

function gotoUpdateProperties() {
  header("Location: $phpSelf?action=updateproperties");
  print "<h1>All fields need to be filled out</h1>";
  exit;
}

function updateProperties() {
  global $phpSelf;

  $html = "<center><div><form method='POST' action='$phpSelf?action=processupdateprop'>" 
  . "<td>" . "<br>"
  . "<p span style='color:red' align='top'>*</span><span style='color:green'><strong>Device Name:</strong></p>" 
  . "<input type='text' name='deviceName' autofocus /></td></tr>" . "</p>" 
  . "<td>" 
  . "<p span style='color:red' align='top'>*</span><span style='color:green'><strong>Device Properties:</strong></P>" 
  . "<textarea name='deviceProperties' cols='100', rows='25'></textarea></td></tr>" . "</p>"
  . "<input type='submit' value='Submit' />"
  . "</center></div></form>"; 
  print $html;


}

function processUpdateProp($hash) {
  global $phpSelf, $table1;
 if (!$hash['deviceName'] || !$hash['deviceProperties'])
 {
   gotoUpdateProperties();
 } else
 {

  $searchName=$hash['deviceName'];
#  printf("SELECT * FROM $table1 WHERE device_name LIKE '$searchNameSELECT * FROM $table1 WHERE device_name LIKE '$searchName''");
  $result=doQuery("SELECT * FROM $table1 WHERE device_name LIKE '$searchName'");

   if ($row = mysqli_fetch_assoc($result)) {
   #printf("UPDATE $table1 SET Properties='%s' WHERE device_name='%s'", $hash['deviceProperties'],$hash['deviceName']) . '<br>';
    doQuery(sprintf("UPDATE $table1 SET Properties='%s' WHERE device_name='%s'",$hash['deviceProperties'],$hash['deviceName']));
    print "<br><br>Information updated in the database successfullly.";
  
 } else if (!$row = mysqli_fetch_assoc($result)) {
   #printf("INSERT INTO device (device_name, Properties) VALUES ('%s', '%s')", $hash['deviceName'], $hash['deviceProperties']);
    doQuery(sprintf("INSERT INTO device (device_name, Properties) VALUES ('%s', '%s')", $hash['deviceName'], $hash['deviceProperties']));
    print "<br><br>Information inserted into the database successfullly.";
   }

 } 
}

function showDevices() {
  global $phpSelf;
  $query = "SELECT * FROM device LEFT JOIN carrier USING (carrier_id)"
    . " LEFT JOIN model USING (model_id)";
  $result = doQuery($query);
  $html = "<center><table cellspacing='1' class='device'>"
    . sprintf("<tr><th class='tl'>Name</th><th>Serial Number</th><th>Model</th><th>Carrier</th><th>Location</th><th class='tr'>Action</th></tr>");
  $action = "";
  $toggle = 0;
  while ($row = mysqli_fetch_array($result)) {
    switch ($row['device_location']) {
      case '':
        $action = "<a href='$phpSelf?action=checkout&device_id={$row['device_id']}'>Check Out</a>";
        break;
      case $_SESSION['user']['id']:
        $action = "<a href='$phpSelf?action=checkin&device_id={$row['device_id']}'>Check In</a>";
        break;
      default:
        $action = "";
        break;
    }
    $class = "toggle" . ($toggle?"On":"Off");
    $toggle = !$toggle;
    $html .= sprintf("<tr class='$class'>");
    $columns = Array($row['device_name'], $row['device_serial_number'], $row['model_name'], $row['carrier_name'], getUserNameById($row['device_location']), $action);
    foreach($columns as $column) {
      $html .= sprintf("<td>%s</td>", $column);
    }
  }
  $html .= "</table></center>";
  $html .= "<a href='$phpSelf?action=adddevice'>Add Device</a>";
  print $html;
  //print_r(doCredentials('automation@automation.com', 'automation'));
//  print_r(getUsers());
//  print_r(getUserNames());
//  print createUserSelect();
}

function showAccounts() {
  global $phpSelf;
  $result = doQuery("SELECT * FROM account LEFT JOIN accounttype USING (type_id)");
  $html = "<center><table cellspacing='1' class='account'>"
    . sprintf("<tr><th class='tl'>Account</th><th>Type</th><th>Password</th><th>Location</th><th class='tr'>Action</th></tr>");
  $action = "";
  $toggle = 0;
  while ($row = mysqli_fetch_array($result)) {
    switch ($row['account_location']) {
      case '':
        $action = "<a href='$phpSelf?action=checkoutaccount&account_id={$row['account_id']}'>Check Out</a>";
        break;
      case $_SESSION['user']['id']:
        $action = "<a href='$phpSelf?action=checkinaccount&account_id={$row['account_id']}'>Check In</a>";
        break;
      default:
        $action = "";
        break;
    }
    $class = "toggle" . ($toggle?"On":"Off");
    $toggle = !$toggle;
    $html .= sprintf("<tr class='$class'>");
    $columns = Array($row['account_name'], $row['type_name'], $row['password'], getUserNameById($row['account_location']), $action);
    foreach($columns as $column) {
      $html .= sprintf("<td>%s</td>", $column);
    }
  }
  $html .= "</table></center>";
  $html .= "<a href='$phpSelf?action=addaccount'>Add Account</a>";
  print $html;
}


function addDevice() {
  $html = "<center><div><form method='POST' action='$phpSelf?action=processdevice'>"
    . "<table style='padding:10px'>"
    . "<tr><td>Device Name</td><td><input type='text' name='device_name'></td></tr>"
    . "<tr><td>Carrier</td><td>" . createCarrierSelect() . "</td>"
    . "<tr><td colspan='2'><input type='submit' value='Add Device'/></td></tr>"
    . "</form></div></center>";

  print $html;
}

function addAccount() {
  $html = "<center><div><form method='POST' action='$phpSelf?action=processaccount'>"
    . "<table style='padding:10px'>"
    . "<tr><td>Account Name</td><td><input type='text' name='account_name'></td></tr>"
    . "<tr><td>Account Password</td><td><input type='text' name='account_password'></td></tr>"
    . "<tr><td>Account Type</td><td>" . createAccountTypeSelect() . "</td>"
    . "<tr><td colspan='2'><input type='submit' value='Add Account'/></td></tr>"
    . "</form></div></center>";

  print $html;
}

function processDevice($hash) {
  doQuery(sprintf("INSERT INTO device (device_name, carrier_id) VALUES ('%s', '%s')", $hash['device_name'], $hash['carrier_id']));
  gotoHome();
}

function processAccount($hash) {
  doQuery(sprintf("INSERT INTO account (account_name, password, type_id) VALUES ('%s', '%s', '%s')", $hash['account_name'], $hash['account_password'], $hash['type_id']));
  gotoAccountPage();
}

function editDevice() {

}

function doQuery($query) {
  global $dbHost, $dbUser, $dbPass;

  $mysqli = new mysqli($dbHost, $dbUser, $dbPass, 'deviceinventory');

  if ($mysqli->connect_errno) {
    die ("Cannot Connect \n");
  }

  if (!($result = $mysqli->query($query))) {
    die ("Cannot Query \n ");
  }

  $result = ($mysqli->insert_id == 0)?$result:$mysqli->insert_id;
  $mysqli->close();

  return $result;
}

function createUserSelect() {
  $users = getUsers();

  $html = "<select>";

  foreach ($users as $user) {
    $html .= sprintf("<option id='%s'>%s</option>", $user['id'], $user['name']);
  }

  $html .= "<\select>";

  return $html;
}

function createCarrierSelect() {
  return createSelect("carrier_id", "SELECT carrier_id, carrier_name FROM carrier ORDER BY carrier_name");
}

function createSelect($name, $query) {
  $result = doQuery($query);

  $html = "<select name='$name'>";
  while ($row = mysqli_fetch_array($result)) {
    $html .= sprintf("<option value='%s'>%s</option>", $row[0], $row[1]);
  }
  $html .= "</select>";

  return $html;
}

function createAccountTypeSelect() {
  return createSelect("type_id", "SELECT type_id, type_name FROM accounttype ORDER BY type_name");
}

function processCheckOut($hash) {
  // TODO: Check if Available
  changeDeviceLocation($hash['device_id'], $_SESSION['user']['id']);
  gotoHome();
}

function processCheckIn($hash) {
  changeDeviceLocation($hash['device_id'], 'NULL');
  gotoHome();
}

function changeDeviceLocation($device_id, $device_location) {
  doQuery(sprintf("UPDATE device SET device_location=%s WHERE device_id='%s'", $device_location, $device_id));
}

function processCheckOutAccount($hash) {
  // TODO: Check if Available
  changeAccountLocation($hash['account_id'], $_SESSION['user']['id']);
  gotoAccountPage();
}

function processCheckInAccount($hash) {
  changeAccountLocation($hash['account_id'], 'NULL');
  gotoAccountPage();
}

function changeAccountLocation($account_id, $account_location) {
  doQuery(sprintf("UPDATE account SET account_location=%s WHERE account_id='%s'", $account_location, $account_id));
}

function doGraph($get) {
  $testRunId = $get["testRunId"];
  print "<iframe src='graph.php?testRunId=$testRunId' width='95%' height='100%' seamless frameborder='0'></iframe><br /><br />";
}

function doHelp() {
  print "<div style='text-align:left'><b>Loading Agent on the device</b><br /><hr>"
    . "<ul><li>Download the jar to the local system <a href='../uploads/uiautomator.jar'>Agent Download</a></li>"
    . "<li>Copy the jar to the device: <span style='color:red'>adb push bin/uiautomator.jar /data/local/tmp/</span></li>"
    . "<li>Run the script on the device: <span style='color:red'>adb shell uiautomator runtest uiautomator.jar -c uiautomator.Agent --nohup</span></li>"
    . "<li>Confirm you can connect to the device</li></ul></div>";
}

?>

</center></body></html>

