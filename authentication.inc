<?php

function authenticate() {
  extract($_GET);

  switch ($action) {
    case 'login':
      doLogIn();
      break;
    case 'processlogin':
      doAuthentication($_POST);
      break;
    case 'logout':
      doLogOut();
      break;
    default:
      if (!sessionAuthenticate()) {
        gotoLogin();
      }
      break;
  }
}

function doAuthentication($post) {
  $userName = $post['userName'];
  $password = $post['password'];

  if (!isset($userName) || !isset($password) || $userName == "" || $password == "") {
    gotoLogin();
  }

  $cred = doCredentials($userName, $password);

  # Backdoor: This should not be checked in
  if ($userName == "guest" && $password == "guest") {
    $cred['name'] = 'guest';
  }
  #########################################
  if ($cred == '') {
    unset($_SESSION['user']);
    gotoLogin();
  }

  $_SESSION['user'] = $cred;

  gotoHome();
}

function sessionAuthenticate() {
  if (!isset($_SESSION['user'])) {
    return false;
  }
  return true;
}

function doLogIn() {
  global $phpSelf;
  $html = "<br /><form method='POST' action='$phpSelf?action=processlogin'>"
    . "<table style='padding: 10px;background-color:lightblue'>"
    . "<tr><td>User Name:</td><td><input name='userName' type='text'></td></tr>"
    . "<tr><td>Password:</td><td><input name='password' type='password'> </td></tr>"
    . "<tr><td colspan='2'><input type='submit' value='Log In'></td></tr>"
    . "</table></form>";

  print $html;
}

function doLogOut() {
  unset($_SESSION['user']);
  session_destroy();
  gotoHome();
}

function gotoLogin() {
  header("Location: $phpSelf?action=login");
  exit;
}

?>

