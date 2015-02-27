<!DOCTYPE HTML>
<html><head><title>Graph demo</title>
<meta http-equiv="refresh" content="10">
<style type="text/css">
  body {font: 10pt arial;}
</style>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript" src="../graph-1.3.2/graph.js"></script>
<!--[if IE]><script type="text/javascript" src="../excanvas.js"></script><![endif]-->
<link rel="stylesheet" type="text/css" href="../graph-1.3.2/graph.css">

<script type="text/javascript">

google.load("visualization", "1");
google.setOnLoadCallback(drawVisualization);

function drawVisualization() {
  var data = new google.visualization.DataTable();
  data.addColumn('datetime', 'time');
  data.addColumn('number', 'Power Percentage');

<?php

require "db.inc";

$testRunId=$_GET['testRunId'];
if (isset($testRunId)) {
  #$result = doQuery("SELECT * FROM memory WHERE testrun_id='$testRunId'");
  $result = doQuery("SELECT * FROM measurement WHERE testrun_id='$testRunId'");
  while ($row = mysqli_fetch_array($result)) {
    if(preg_match("/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/", $row['time'], $matches)) {
      list(,$year, $mon, $day, $hour, $min, $sec) = $matches;
      print "data.addRow([new Date($year, $mon, $day, $hour, $min, $sec), {$row['value']}]);\n";
    }
  }
}
// TODO: Make existing method more configurable and use that  
function doQuery($query) {
  global $dbHost, $dbUser, $dbPass;
  $mysqli = new mysqli($dbHost, $dbUser, $dbPass, 'dashboard');

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
  var options = {
    "width":  "100%",
    "height": "350px"
  };

  var graph = new links.Graph(document.getElementById('nativeHeap'));
  graph.draw(data, options);

}
</script>
</head><body>
<div id="nativeHeap"></div>
<div id="info"></div></body></html>
