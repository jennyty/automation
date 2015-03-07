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
  data.addColumn('number', 'Native Heap Size');
  data.addColumn('number', 'Native Heap Alloc');
  
  var data2 = new google.visualization.DataTable();
  data2.addColumn('datetime', 'time');
  data2.addColumn('number', 'Dalvik Heap Size');
  data2.addColumn('number', 'Dalvik Heap Alloc');

<?php
require "database.php";

$testRunId=$_GET['testRunId'];
if (isset($testRunId)) {
  $result = doQuery("dashboard", "SELECT * FROM memory WHERE testrun_id='$testRunId'");
  while ($row = mysqli_fetch_array($result)) {
    if(preg_match("/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/", $row['time'], $matches)) {
      list(,$year, $mon, $day, $hour, $min, $sec) = $matches;
      print "data.addRow([new Date($year, $mon, $day, $hour, $min, $sec), {$row['native_heap_size']}, {$row['native_heap_alloc']}]);\n";
      print "data2.addRow([new Date($year, $mon, $day, $hour, $min, $sec), {$row['dalvik_heap_size']}, {$row['dalvik_heap_alloc']}]);\n";
    }
  }
}
?>
  var options = {
    "width":  "100%",
    "height": "350px"
  };
  var options2 = {
    "width":  "100%",
    "height": "350px",
    "lines": [
      {"color": "green" ,"style": "line"},
      {"color": "orange","style": "line"}
    ],
  };

  var graph = new links.Graph(document.getElementById('nativeHeap'));
  graph.draw(data, options);

  var graph = new links.Graph(document.getElementById('dalvikHeap'));
  graph.draw(data2, options2);
}
</script>
</head><body>
<div id="nativeHeap"></div>
<div id="dalvikHeap"></div>
<div id="info"></div></body></html>
