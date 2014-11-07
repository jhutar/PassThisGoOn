<?php

require_once('functions.php');
require_once('data.php');

list($team, $secret, $question, $previous_question, $previous_answer, $previous_start, $previous_end) = load_for_question();

# Print header of the page
html_header($contest, $team, $secret, $questions);

# If on first question or on already initialized question, just show it.
# Othervise evaluate answer to previous question.
if ($question == 0 OR ! is_null(loadQ_DB($team, $question))) {
  $question_human = $question + 1;
  echo "<h1>Question ".$question_human." out of ".count($questions)."</h1>\n";
  echo $questions[$question]['text'];
  html_answer_form($question, $team, $secret, $questions);
} else {
  if (evaluateQ($previous_answer, $questions[$previous_question]['answer'])) {
    # If last question was answered correctly, initiate 
    $previous_end = endQ_DB($team, $question-1);
    createQ_DB($team, $secret, $question);
    $elapsed = $previous_end - $previous_start;
    if (strlen($previous_answer) > 10) {
      $previous_answer_new = substr($previous_answer, 0, 10).'...';
    } else {
      $previous_answer_new = $previous_answer;
    }
    echo "<p class='pass'>Answer to your previous question (<code>".htmlspecialchars($previous_answer_new)."</code>) was correct and you made it in ".$elapsed." seconds!</p>\n";
    # Show question and answer form
    $question_human = $question + 1;
    echo "<h1>Question ".$question_human." out of ".count($questions)."</h1>\n";
    echo $questions[$question]['text'];
    html_answer_form($question, $team, $secret, $questions);
  } else {
    # If last question was answered INcorrectly
    echo "<p class='fail'>Answer to your previous question (<code>".$previous_answer."</code>) was incorrect! Please <a href='question.php?question=".$previous_question."&secret=".$secret."&team=".$team."'>go back</a> and try again.</p>\n";
  }
}

html_footer();

?>
