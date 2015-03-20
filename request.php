<?php

// "example request: http://path/to/resource?method=getresults&testid=xxxx
  require "tr.inc";
  require "/sandbox/packages/testrail-api-master/php/testrail.php";
  require "database.php";
  require_once "RestServer.php";

 function docommon0($testid,$methodName,$prefix,$lst)
  {
    try {
    global $host, $user, $password;

    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    if (method_exists($client, $methodName)) {
      $case = $client->$methodName($prefix.$testid,$lst);
    } else {
      echo "bad";
    }

   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
   return $case;
  }


$rest = new RestServer('Result');
$rest->handle();

class Result
{
  function addresult($testid,$status_id,$comment) {
    $test_array = array('status_id' => $status_id,
                        'custom_devicetype' => 2,
                        'comment' =>  $comment,
                       );
    $case = docommon0($testid,"send_post","add_result/",$test_array);
    $jsonStr = html_entity_decode(json_encode($case));

    return $jsonStr;
  }


  function getresults($testid) {
    $case = docommon0($testid,'send_get','get_results/');

    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));

    return $jsonStr;
  }


  function insertMeasurementValue($run,$date,$value,$type) {
   try {
     $query="INSERT INTO measurement (testrun_id, time, value, datatype_id) VALUES ('$run', '$date', '$value','$type')";
     $result = doQuery("dashboard", $query);
     echo "<p></p>";
     echo "Insert completed.";
   }catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
  }


  function insertMemory($run,$date,$nativeHeapSize,$nativeHeapAlloc,$dalvikHeapSize,$dalvikHeapAlloc) {
   try {
     echo "<br>";
     $query="INSERT INTO memory (testrun_id, time, native_heap_size, native_heap_alloc, dalvik_heap_size, dalvik_heap_alloc) VALUES  ('$run','$date','$nativeHeapSize','$nativeHeapAlloc','$dalvikHeapSize','$dalvikHeapAlloc')";
     $result = doQuery("dashboard", $query);
     echo "<p></p>";
     echo "Insert completed.";
   }catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
  }


  function getDevices($options) {
    $hash = json_decode($options, true);
    $where = "WHERE 1=1";
    $orderby = "";
    
    if (isset($hash["model_name"])) {
      $where .= sprintf(" AND model_name='%s'", $hash["model_name"]);
    }

    if (isset($hash["carrier_name"])) {
      $where .= sprintf(" AND carrier_name='%s'", $hash["carrier_name"]);
    }

    if (isset($hash["orderby"])) {
      $orderby = sprintf(" ORDER BY %s", $hash["orderby"]);
    }
    
    $query = "SELECT device_name, carrier_name, model_name FROM device LEFT JOIN carrier USING (carrier_id) LEFT JOIN model USING (model_id) $where $orderby";

    $result = doQuery("deviceinventory", $query);
    $data = array();
    while ($row = mysqli_fetch_array($result)) {
      array_push($data, $row);
    }
    
    return json_encode($data);
  }
}

?>


