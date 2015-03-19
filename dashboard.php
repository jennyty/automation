<html><head><title>Dashboard</title>
<link rel='stylesheet' type='text/css' href='style.css' />

</head><body><center>

<?php
require "database.php";
require "testrail.inc";
require "authentication.inc";
require "script.php";
require "timesheet.php";

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
  . "<a href=$phpSelf?action=showhome>Home</a>"
  . " | <a href=$phpSelf?action=showdevices>Devices</a>"
  . " | <a href=$phpSelf?action=showaccounts>Test Accounts</a>"
  . " | <a href=$phpSelf?action=showscripts>Scripts</a>"
#  . " | <a href=$phpSelf?action=graph&testRunId=0>Graph</a>"
#  . " | <a href=$phpSelf?action=updateproperties>Update Properties</a>"
  . " | <a href=$phpSelf?action=showtimesheet>Time Sheet</a>"
  . " | <a href=$phpSelf?action=help>Help</a>"
  . " | <a href=$phpSelf?action=logout>Log Out</a>"
  . "</div>";
  print getErrorMessage();
  clearErrorMessage();
  print "<h3>USER: {$_SESSION['user']['name']}</h3>";
}

switch ($action) {
  case 'showhome':
    showHome();
    break;
  case 'showdevices':
    showDevices($_GET);
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
    doTimeSheetSwitch($_POST, $_GET);
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
  header("Location: $phpSelf?action=showhome");
  exit;
}

function gotoDevices() {
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

function getPropValue($deviceProperties, $value) {
  if(preg_match("/\[$value\]: \[(.*)\]/", $deviceProperties, $matches)) {
    return $matches[1];
  }
}

function processUpdateProp($hash) {
  global $phpSelf, $table1;

  $deviceSerialNumber = getPropValue($hash['deviceProperties'], 'ro.serialno');
  $deviceModelId = getModelId(getPropValue($hash['deviceProperties'], 'ro.product.model'));
  $deviceCarrierId = getCarrierId(getPropValue($hash['deviceProperties'], 'ro.com.google.clientidbase.am'));

  if (!$hash['deviceName'] || !$hash['deviceProperties'] || !$deviceSerialNumber) {
    gotoUpdateProperties();
  } else {
    $searchName=$hash['deviceName'];
#    printf("SELECT * FROM $table1 WHERE device_name LIKE '$searchNameSELECT * FROM $table1 WHERE device_name LIKE '$searchName''");
    $result=doQuery("deviceinventory", "SELECT * FROM $table1 WHERE device_serial_number = '$deviceSerialNumber'");
   
    if ($row = mysqli_fetch_assoc($result)) {
      #printf("UPDATE $table1 SET Properties='%s' WHERE device_name='%s'", $hash['deviceProperties'],$hash['deviceName']) . '<br>';
      doQuery("deviceinventory", sprintf("UPDATE $table1 SET Properties='%s', device_name='%s' WHERE device_serial_number='%s'", $hash['deviceProperties'], $hash['deviceName'], $deviceSerialNumber));
      print "<br><br>Information updated in the database successfully.";
    } else if (!$row = mysqli_fetch_assoc($result)) {
      #printf("INSERT INTO device (device_name, Properties) VALUES ('%s', '%s')", $hash['deviceName'], $hash['deviceProperties']);
      doQuery("deviceinventory", sprintf("INSERT INTO device (device_name, Properties, device_serial_number) VALUES ('%s', '%s', '%s')", $hash['deviceName'], $hash['deviceProperties'], $deviceSerialNumber));
      print "<br><br>Information inserted into the database successfully.";
    }
    # TODO: Maybe not have this separate?
    if (isset($deviceModelId) && isset($deviceCarrierId)) {
      doQuery("deviceinventory", sprintf("UPDATE $table1 SET model_id='%s', carrier_id='%s' WHERE device_serial_number='%s'", $deviceModelId, $deviceCarrierId, $deviceSerialNumber));
    }
  } 
}

function getModelId($modelName) {
  if (isset($modelName)) {
    $query = "SELECT * FROM model WHERE model_name='$modelName'";
    $result = doQuery("deviceinventory", $query);

    if ($row = mysqli_fetch_assoc($result)) {
      return $row['model_id'];
    }

    return doQuery("deviceinventory", sprintf("INSERT INTO model (model_name) VALUES ('%s')", $modelName));
  }
}

function getCarrierId($carrierName) {
  if (isset($carrierName)) {
    $query = "SELECT * FROM carrier WHERE carrier_name='$carrierName'";
    $result = doQuery("deviceinventory", $query);

    if ($row = mysqli_fetch_assoc($result)) {
      return $row['carrier_id'];
    }

    return doQuery("deviceinventory", sprintf("INSERT INTO carrier (carrier_name) VALUES ('%s')", $carrierName));
  }
}

function showHome() {
  print "<iframe src='home.php' width='95%' height='100%' seamless frameborder='0'></iframe><br />";
}

function showDevices($hash) {
  global $phpSelf;
  $orderBy = "";

  if (isset($hash['sort'])) {
    $_SESSION['device_sort'] = $hash['sort'];
  }

  # Saving as a session variable so sort doesn't need to propergate through checkin/checkout
  if (isset($_SESSION['device_sort'])) {
    $orderBy = " ORDER BY " . $_SESSION['device_sort'];
  }

  $query = "SELECT * FROM device LEFT JOIN carrier USING (carrier_id) LEFT JOIN model USING (model_id)" . $orderBy;
  $result = doQuery("deviceinventory", $query);
  $thisUrl = "$phpSelf?action=showdevices";
  $html = "<center><table cellspacing='1' class='device'>"
    . sprintf("<tr><th class='tl'><a href='$thisUrl&sort=device_name'>Name</a></th>"
      . "<th><a href='$thisUrl&sort=device_serial_number'>Serial Number</a></th>"
      . "<th><a href='$thisUrl&sort=model_name'>Model</a></th>"
      . "<th><a href='$thisUrl&sort=model_desc'>Desc</a></th>"
      . "<th><a href='$thisUrl&sort=carrier_name'>Carrier</a></th>"
      . "<th>Location</th><th class='tr'>Action</th></tr>");
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
    $columns = Array($row['device_name'], $row['device_serial_number'], $row['model_name'], $row['model_desc'], $row['carrier_name'], getUserNameById($row['device_location']), $action);
    foreach($columns as $column) {
      $html .= sprintf("<td>%s</td>", $column);
    }
  }
  $html .= "</table></center>";
  #$html .= "<a href='$phpSelf?action=adddevice'>Add Device</a>";
  $html .= "<a href=$phpSelf?action=updateproperties>Add Device</a>";
  print $html;
  //print_r(doCredentials('automation@automation.com', 'automation'));
//  print_r(getUsers());
//  print_r(getUserNames());
//  print createUserSelect();
}

function showAccounts() {
  global $phpSelf;
  $result = doQuery("deviceinventory", "SELECT * FROM account LEFT JOIN accounttype USING (type_id) ORDER BY type_name");
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
  doQuery("deviceinventory", sprintf("INSERT INTO device (device_name, carrier_id) VALUES ('%s', '%s')", $hash['device_name'], $hash['carrier_id']));
  gotoDevices();
}

function processAccount($hash) {
  doQuery("deviceinventory", sprintf("INSERT INTO account (account_name, password, type_id) VALUES ('%s', '%s', '%s')", $hash['account_name'], $hash['account_password'], $hash['type_id']));
  gotoAccountPage();
}

function editDevice() {

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
  return createSelect("deviceinventory", "carrier_id", "SELECT carrier_id, carrier_name FROM carrier ORDER BY carrier_name", false);
}

function createSelect($schema, $name, $query, $blankFirst) {
  $result = doQuery($schema, $query);

  $html = "<select name='$name'>";
  if ($blankFirst) {
    $html .= "<option></option>";
  }
  while ($row = mysqli_fetch_array($result)) {
    $html .= sprintf("<option value='%s'>%s</option>", $row[0], $row[1]);
  }
  $html .= "</select>";

  return $html;
}

function createAccountTypeSelect() {
  return createSelect("deviceinventory", "type_id", "SELECT type_id, type_name FROM accounttype ORDER BY type_name");
}

function processCheckOut($hash) {
  // TODO: Check if Available
  changeDeviceLocation($hash['device_id'], $_SESSION['user']['id']);
  gotoDevices();
}

function processCheckIn($hash) {
  changeDeviceLocation($hash['device_id'], 'NULL');
  gotoDevices();
}

function changeDeviceLocation($device_id, $device_location) {
  doQuery("deviceinventory", sprintf("UPDATE device SET device_location=%s WHERE device_id='%s'", $device_location, $device_id));
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
  doQuery("deviceinventory", sprintf("UPDATE account SET account_location=%s WHERE account_id='%s'", $account_location, $account_id));
}

function doGraph($get) {
  $testRunId = $get["testRunId"];
  print "<iframe src='graph.php?testRunId=$testRunId' width='95%' height='100%' seamless frameborder='0'></iframe><br />";
}

function doHelp() {
  print "<iframe src='help.php' width='95%' height='100%' seamless frameborder='0'></iframe><br />";
}

?>

</center></body></html>

