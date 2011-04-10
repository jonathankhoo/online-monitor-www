<?php

$contents = strip_tags(file_get_contents('schedules/apr11sch_v2.html'));

$today = date('d M');

$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y"));
$tomorrow = date('d M', $tomorrow);

$today_pos = stripos($contents, $today);
$tomorrow_pos = stripos($contents, $tomorrow);
$difference = $tomorrow_pos - $today_pos;

$info = substr($contents, $today_pos, $difference);

//echo $info, '<br>';

// Get times and project IDs
$pattern = "/[\d]{2}:[\d]{2}[\s]*-[\s]*[\d]{2}:[\d]{2}[\s]*(P[\d]+|Maintenance|Director's Time)/";
preg_match_all($pattern, $info, $matches);

// Get Project description: PXXX|Main|Dir description XX:XX
$pattern = "/(P[\d]+|Maintenance|Director's Time)(.*)/";
//preg_match_all($pattern, $info, $description_matches);
preg_match_all($pattern, $info, $description_matches);

/*echo '<pre>';
print_r($description_matches);
echo '</pre>';*/

$schedules = new stdClass();
$i = 0;

foreach ($matches[0] as $m) {
  $tmp = explode(' ', $m);
  $schedules->start_time[] = $tmp[0];
  $schedules->end_time[] = $tmp[2];

  if (isset($tmp[4])) {
    $schedules->project_id[] = 'Director\'s Time';
  } else  {
    $schedules->project_id[] = $tmp[3];
  }

  $schedules->project_description[] = $description_matches[2][$i];
  $i++;
}

echo '<table class="gridtable">';
echo '<tr><th>Start </th><th>End </th><th>Project</th></tr>';

$current_time = time();
$found_current_project = FALSE;
$current_day = TRUE;

for ($i = 0; $i < count($schedules->start_time); $i++) {
  $start_time = $schedules->start_time[$i];
  $end_time = $schedules->end_time[$i];
  $project_id = $schedules->project_id[$i];
  $project_description = $schedules->project_description[$i];

  $start = strtotime($start_time);

  if ($i != 0 && $start_time === "00:00") {
    $current_day = FALSE;
  }

  if ($end_time === "24:00") {
    $end = strtotime("23:59");
  } else {
    $end = strtotime($end_time);
  }

  if ($current_time >= $start && $current_time < $end && $found_current_project === FALSE) {
    if ($project_id == "Director's Time" || $project_id == "Maintenance") {
      echo "<tr class=\"highlight\"><td>$start_time</td><td>$end_time</td><td class=center>$project_id</td></tr>";
    } else {
      echo "<tr class=\"highlight\"><td>$start_time</td><td>$end_time</td><td class=center><abbr title=\"$project_description\">$project_id</td></tr>";
    }
    $found_current_project = TRUE;
  } else {
    if ($current_day === TRUE) {
      if ($project_id == "Director's Time" || $project_id == "Maintenance") {
        echo "<tr class=\"today\"><td>$start_time</td><td>$end_time</td><td class=center>$project_id</td></tr>";
      } else {
        echo "<tr class=\"today\"><td>$start_time</td><td>$end_time</td><td class=center><abbr title=\"$project_description\">$project_id</td></tr>";
      }
    } else {
      if ($project_id == "Director's Time" || $project_id == "Maintenance") {
        echo "<tr class=\"tomorrow\"><td>$start_time</td><td>$end_time</td><td class=center>$project_id</td></tr>";
      } else {
        echo "<tr class=\"tomorrow\"><td>$start_time</td><td>$end_time</td><td class=center><abbr title=\"$project_description\">$project_id</td></tr>";
      }
    }
  }
}

echo '</table>';

?>
