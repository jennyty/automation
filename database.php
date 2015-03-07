<?php
require "db.inc";

function doQuery($schema, $query) {
  global $dbHost, $dbUser, $dbPass;

  $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $schema);

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

?>
