<?php

require_once('functions.php');
require_once('data.php');

html_header($contest);

echo "<h1>Teams by number of answered questions</h1>";

function mysort($a, $b) {
  return $a['q_time'] - $b['q_time'];
}

$teams = listT_DB();
for ($i=0; $i<count($teams); $i++) {
  list($teams[$i]['q_count'], $teams[$i]['q_time']) = timeT_DB($teams[$i]['team']);
}
for ($i=count($questions); $i>=0; $i--) {
  $selected = array();
  foreach ($teams as $team) {
    if ($team['q_count'] == $i) {
      $selected[] = $team;
    }
  }
  usort($selected, mysort);
  echo "<h2>Teams with ".$i." answered questions</h2>\n";
  echo "<table>\n";
  echo "<tr><th>Team</th><th>Questions</th><th>Seconds</th></tr>\n";
  foreach ($selected as $team) {
    echo "<tr><td>".htmlspecialchars($team['email'])."</td><td>".$team['q_count']."</td><td>".$team['q_time']."</td></tr>\n";
  }
  echo "</table>\n";
}

html_footer();

?>
