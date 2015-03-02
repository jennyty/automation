<?php

// "example request: http://path/to/resource?method=getresults&testid=xxxx
  require "tr.inc";
  require "/sandbox/packages/testrail-api-master/php/testrail.php";
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


}

?>


