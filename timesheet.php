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

function showTimeSheet($get) {
  $shift = $get['shift'];
  if (!isset($shift)) {
    $shift = 0;
  } 
  $lastSunday = strtotime('last Sunday') + (7*24*60*60*$shift);
  #print date("Y-m-d",$lastSunday) . "<br />"; 
  #print date("Y-m-d",$lastSunday-(7*24*60*60*1)) . "<br />"; 
  #print date("Y-m-d",$lastSunday-(7*24*60*60*2)) . "<br />";
  $week = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
  print "<form  method='POST' action='$phpSelf?action=processtimesheet'><table style='padding:15px'>";
  print "<table cellspacing='0'>";
  print "<tr><td colspan='8' style='text-align:center'>"
    . "<a href='$phpSelf?action=showtimesheet&shift=" . ($shift - 1) . "'>prev</a> | "
    . "<a href='$phpSelf?action=showtimesheet'>current</a> | "
    . "<a href='$phpSelf?action=showtimesheet&shift=" . ($shift + 1) . "'>next</a><br /><hr /></td></tr>";
  #print "<tr><td colspan='8' style='text-align:center'><select><option>This week</options><option>Last week</option></select></td></tr>";
  #:wprint "<tr style='text-align: center'><td>Project</td><td>sun(".$day[0].")</td><td>mon</td><td>tue</td><td>wed</td><td>thu</td><td>fri</td><td>sat</td></tr>";
  $html = "<tr style='text-align: center;text-style:bold'><td>Project</td>";

  # Date header
  for ($lp=0;$lp < 7;$lp++) {
    $day   = date("m/d",$lastSunday+(24*60*60*$lp));
    $today = date("m/d",strtotime('today'));
    $html .= "<td style='color:" . (($day == $today)?"red":"black") . "'>{$week[$lp]} ({$day})</td>";
  }

  print $html . "</tr>"; 
  
  for($lp2=0;$lp2<5;$lp2++) {
    print "<tr><td>" . createProjectSelect() . "</td>";
    for($lp = 0;$lp < 7;$lp++) {
      print "<td><input type='text' style='width:120px' name='time[]'></td>";
    }
    print "</tr>";
  }

  print "<tr><td colspan='8' style='text-align:center'><hr /><input type='button' value='Check'></input><input type='submit' value='submit'></input></td></tr>";
  print "</table>";
  print "</form>";
}


function createProjectSelect() {
  return createSelect("dashboard", "project_id[]", "SELECT project_id, project_name FROM project ORDER BY project_name");
}

function processTimeSheet($post) {
  print_r($post);
  print "<hr />";
  #print_r($post['proj ect_id']);
  $cnt = 0;
  foreach($post['project_id'] as $id) {
    for($lp=0+($cnt*7);$lp<7+($cnt*7);$lp++) {
      $time = $post['time'][$lp];
      if (isset($time) && $time != "") {
        print "PROJECT: $id TIME: ".$time."<br />";
      }
    }
    $cnt++;
  }
}

?>
