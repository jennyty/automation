<?php

// "example request: http://path/to/resource?method=getresults&testid=xxxx
  require "tr.inc";
  require "/sandbox/packages/testrail-api-master/php/testrail.php";
  require "database.php";
  require_once "RestServer.php";

  $rest = new RestServer('Result');
  $rest->handle();

class Result
{
  function addresult($testid,$status_id,$comment) {
    global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_post("add_result/$testid",
             array(
                'status_id' => $status_id,
	        'custom_devicetype' => 2,
                'comment' =>  $comment,
         )
  );  

    # var_export($case);
    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));

   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
    return $jsonStr;
  }

  function addresultforcase($runid,$testid,$status_id,$comment) {
    global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_post("add_result_for_case/$runid/$testid",
             array(
                'status_id' => $status_id,
	        'custom_devicetype' => 1,
                'comment' =>  $comment,
         )
  );  

    # var_export($case);
    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));
    echo "<br><br>";
#    echo "case: " .  $jsonStr;

   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
    return $jsonStr;
  }


  function getresults($testid) {
    global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_get('get_results/'.$testid);
    #print_r($case);
    #var_export($case);

    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));
    echo "<br>";
#    echo "case: " .  $jsonStr;

   } catch (Exception $e) {
     echo "Error:  " ,$e-> getMessage();
   }
    return $jsonStr;
  }


  function getresultsforcase($runid, $caseid) {
    global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_get("get_results_for_case/$runid/$caseid");
    #var_export($case);
    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));
    echo "<br><br>";
#    echo "case: " .  $jsonStr;
   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
    return $jsonStr;
  }


  function getresultsforrun($runid) {
    global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_get('get_results_for_run/'.$runid);
    #var_export($case);
    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));
    echo "<br><br>";
#    echo "case: " .  $jsonStr;
   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
    return $jsonStr;
  }


  function getcase($caseid) {
   global $host, $user, $password;
    try {
    $client = new TestRailAPIClient($host);
    $client->set_user($user);
    $client->set_password($password);

    $case = $client->send_get('get_case/'.$caseid);
    #print_r($case);
    var_export($case);
    #converting array to json format 
    $jsonStr = html_entity_decode(json_encode($case));
    echo "<br><br>";
#    echo "case: " .  $jsonStr;
   } catch (Exception $e) {
     echo "Error: " ,$e-> getMessage();
   }
    return $jsonStr;
  }


  function insertMeasurementValue($run,$date,$value,$type) {
   $result=mysql_query("INSERT INTO measurement (testrun_id, time, value, datatype_id) values ($run, $date, $value,$type)");
   print "Insert completed.";
   mysql_close(($conn));
  }

  # request.php?method=getDevices&options={}
  # request.php?method=getDevices&options={"carrier_name":"android-tmobile-us", "model_name":"SM-G900T"}

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


