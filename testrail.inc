<?php
require "testrail.php";

$testRail = "";

function doCredentials($userName, $userPass) {
  global $testRail;
  $user = '';
  try {
    //$testRail = new TestRailAPIClient('http://localhost/testrail');
    $testRail = new TestRailAPIClient('http://mscaqatr/testrail');
    $testRail->set_user($userName);
    $testRail->set_password($userPass);
    $user = $testRail->send_get("get_user_by_email&email=$userName");
    $_SESSION['testrail'] = $testRail;
    $_SESSION['users'] = $testRail->send_get("get_users");
  } catch(Exception $e) {
    print "$e";
  }
  return $user;
}

function getUsers() {

  $users = doTestRailCommand('get_users');

  return $users;
}

function getUserNameById($user_id) {

  //$user = doTestRail("get_user/$user_id");
  foreach ($_SESSION['users'] as $user) {
    if ($user['id'] == $user_id) {
      return $user['name'];
    }
  }

  return '';
}

function doTestRail($command) {
  $result = Array();
  try {
    $result = $_SESSION['testrail']->send_get($command);
  } catch (Exception $e) {
    print "$e";
  }
  return $result;
}

// TODO: Do not use this
//function getUserNames() {
//  $users = getUsers();
//  $userNames = array();
//
//  foreach ($users as $user) {
//    array_push($userNames, $user['name']);
//  }
//
//  return $userNames;
//}

?>

