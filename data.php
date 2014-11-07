<?php

$contest = '';
$contest_text = '';
$contest_bye = '';

# When question['answer'] is string, users answer will be normalized before
# comparing to this set value (see function evaluateQ()). Same for this
# correct string. If it is an function, it will be ran and its return value
# retermines pass or fail.
# Used http://randomtextgenerator.com/ and http://www.asciitohex.com/ to get the strings
$questions = array();
$questions[] = array(
  'text' => '',
  'answer' => ''
);

?>
