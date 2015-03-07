<?php

function doTimeSheetSwitch($post, $get) {
  switch($get['action']) {
    case 'showtimesheet':
      showTimeSheet($get);
      break;
    case 'processtimesheet':
      processTimeSheet($post);
      break;
  }
}

function gotoTimeSheet($shift) {
  header("Location: $phpSelf?action=showtimesheet&shift=$shift");
  exit;
}

function showTimeSheet($get) {
  $shift = $get['shift'];
  if (!isset($shift)) {
    $shift = 0;
  } 
  $lastSunday = strtotime('last Sunday') + (7*24*60*60*$shift);
  $week = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
  $html = "<form  method='POST' action='$phpSelf?action=processtimesheet'>"
    . "<table cellspacing='0' style='padding:2px'>"
    . "<tr><td colspan='9' style='text-align:center'>"
    . "<a href='$phpSelf?action=showtimesheet&shift=" . ($shift - 1) . "'>prev</a> | "
    . "<a href='$phpSelf?action=showtimesheet'>current</a> | "
    . "<a href='$phpSelf?action=showtimesheet&shift=" . ($shift + 1) . "'>next</a><br /><hr /></td></tr>"
    . "<tr style='text-align: center;text-style:bold'><td>Project</td>";

  # Date header
  for ($lp=0;$lp < 7;$lp++) {
    $day   = date("m/d",$lastSunday+(24*60*60*$lp));
    $today = date("m/d",strtotime('today'));
    $html .= "<td style='color:" . (($day == $today)?"red":"black") . "'>{$week[$lp]} ({$day})</td>";
  }
  $html .= "<td>Total</td>";
  $html .= "</tr>";

  $html .= getLogged($shift); 
  
  for($lp2=0;$lp2<5;$lp2++) {
    $html .= "<tr><td>" . createProjectSelect() . "</td>";
    for($lp = 0;$lp < 7;$lp++) {
      $html .= "<td><input type='text' style='width:120px' name='time[]'></td>";
    }
    $html .= "<td></td></tr>";
  }

  $html .= "<tr><td colspan='9' style='text-align:center'><hr /><input type='button' value='Check'></input><input type='submit' value='submit'></input></td></tr>";
  $html .= "</table>";
  $html .= "<input type='hidden' name='shift' value='$shift' />";
  $html .= "</form>";
  print $html;
}

function getLogged($shift) {
  # TODO: Join product and order
  $query = sprintf("SELECT * FROM time JOIN project USING (project_id) WHERE user_id = '%s' AND time_stamp >= '%s' AND time_stamp <= '%s' ORDER BY project_name, time_stamp", $_SESSION['user']['id'], getStamp(0,$shift), getStamp(6,$shift));
  $result = doQuery("dashboard", $query);
  $return = "";
  $projects=array();
  $data=array();
  while ($row = mysqli_fetch_assoc($result)) {
    array_push($projects, $row['project_name']);
    $return .= print_r($row, true);
    $data[$row['project_name']][$row['time_stamp']] = $row['time_value'];
  }
  $projects = array_unique($projects);
  $html = "";
  $sum = 0;
  foreach($projects as $project) {
    $html .= "<tr style='text-align: center;color:green'><td>$project</td>";
    $total = 0;
    for($lp = 0 ; $lp < 7 ; $lp++) {
      $datum = $data[$project][getStamp($lp, $shift)];
      $html .= sprintf("<td>%s</td>",isset($datum)?$datum:"");
      $total += $datum;
    }
    $sum += $total;
    $html .= "<td style='text-align:right'>$total</td>";
    $html .= "</tr>";
  }
  $html .= "<tr><td colspan='8'></td><td style='text-align:right'>$sum</td></tr>";
  return $html;
}

function createProjectSelect() {
  return createSelect("dashboard", "project_id[]", "SELECT project_id, project_name FROM project ORDER BY project_name", true);
}

function processTimeSheet($post) {
  print_r($post);
  $shift = $post['shift'];
  print "<hr />";
  #print_r($post['proj ect_id']);
  $cnt = 0;
  foreach($post['project_id'] as $id) {
    $query = sprintf("DELETE FROM time WHERE user_id='%s' AND project_id='%s' AND time_stamp >='%s' AND time_stamp <='%s'",$_SESSION['user']['id'], $id, getStamp(0, $shift), getStamp(6, $shift));
    print "$query<br />";
    doQuery("dashboard", $query);
    for($lp=0 ; $lp < 7 ; $lp++) {
      $time = $post['time'][$lp + ($cnt * 7)];
      if (isset($time) && $time != "" && $id != "") {
        $query = sprintf("INSERT INTO time (time_stamp, user_id, project_id, time_value) VALUES ('%s','%s','%s','%s')", getStamp($lp, $shift), $_SESSION['user']['id'], $id, $time);
        doQuery("dashboard", $query);
      }
    }
    $cnt++;
  }
  gotoTimeSheet($shift);
}

function getStamp($dayOfWeek, $shift) {
  return strtotime('last Sunday') + (7*24*60*60*$shift) + (24*60*60*$dayOfWeek);  
}

?>
