<html><head><title>Device Inventory</title>
<link rel='stylesheet' type='text/css' href='style.css' />

</head><body><center>

<?php
require "db.inc";
require "testrail.inc";
require "authentication.inc";
extract($_GET);
define('ROOTPATH', dirname(__FILE__) . '/');
#print "<h1>".ROOTPATH."</h1>";
$phpSelf = "dashboard.php";
$table1="device";

session_start();

authenticate($action);

if (isset($_SESSION['user'])) {

### Display buttons at the top ###
# print "<div style='width:100%;text-align:center;background-color:orange'><a href=$phpSelf?action=logout><button>Log Out</button></a> <a href=$phpSelf?action=updateproperties><button>Update Properties</button></a> <a href=$phpSelf?action=showdevices><button>Home</button></a></div>";
print "<div style='width:100%;text-align:center;background-color:orange'>"
  . "<a href=$phpSelf?action=showdevices>Home</a>"
  . " | <a href=$phpSelf?action=updateproperties>Update Properties</a>"
  . " | <a href=$phpSelf?action=logout>Log Out</a>"
  . "</div>";

  print "<h3>USER: {$_SESSION['user']['name']}</h3>";
}

switch ($action) {
  case 'showdevices':
    showDevices();
    break;
  case 'adddevice':
    addDevice();
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
  case 'processdevice':
    processDevice($_POST);
    break;
  case 'updateproperties':
    updateProperties();
    break;
  case 'processupdateprop':
    processUpdateProp($_POST);
    break;
  default:
    // Add Authentification
    break;
}

function gotoHome() {
  header("Location: $phpSelf?action=showdevices");
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
  $result = doQuery("SELECT * FROM device LEFT JOIN carrier USING (carrier_id)");
  $html = "<center><table cellspacing='1' class='device'>"
    . sprintf("<tr><th class='tl'>Name</th><th>Carrier</th><th>Location</th><th class='tr'>Action</th></tr>");
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
    $columns = Array($row['device_name'], $row['carrier_name'], getUserNameById($row['device_location']), $action);
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

function addDevice() {
  $html = "<center><div><form method='POST' action='$phpSelf?action=processdevice'>"
    . "<table style='padding:10px'>"
    . "<tr><td>Device Name</td><td><input type='text' name='device_name'></td></tr>"
    . "<tr><td>Carrier</td><td>" . createCarrierSelect() . "</td>"
    . "<tr><td colspan='2'><input type='submit' value='Add Device'/></td></tr>"
    . "</form></div></center>";

  print $html;
}

function processDevice($hash) {
  doQuery(sprintf("INSERT INTO device (device_name, carrier_id) VALUES ('%s', '%s')", $hash['device_name'], $hash['carrier_id']));
  gotoHome();
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

?>

</center></body></html>

