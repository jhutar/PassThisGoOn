<?php

require_once('functions.php');
require_once('data.php');

list($team, $secret, $question, $previous_question, $previous_answer, $previous_start, $previous_end) = load_for_question();

html_header($contest, $team, $secret, $questions);

# Evaluate answer to last question in this contest.
if (evaluateQ($previous_answer, $questions[$previous_question]['answer'])) {
  # If last question was answered correctly, initiate 
  $previous_end = endQ_DB($team, $question-1);
  $elapsed = $previous_end - $previous_start;
  echo "<p class='pass'>Answer to your previous question (<code>".$previous_answer."</code>) was correct and you made it in ".$elapsed." seconds!</p>\n";
  # Show thank you
  echo "<h1>Thank you!</h1>";
  list($q_count, $q_time) = timeT_DB($team);
  echo "<p>Your time was: ".$q_time." seconds.</p>";
  echo $contest_bye;
} else {
  # If last question was answered INcorrectly
  echo "<p class='fail'>Answer to your previous question (<code>".$previous_answer."</code>) was incorrect! Please <a href='question.php?question=".$previous_question."&secret=".$secret."&team=".$team."'>go back</a> and try again.</p>\n";
}

html_footer();

?>
