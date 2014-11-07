<?php

require_once('functions.php');

# Load player identification
if (isset($_POST['email'])) {
  $email = $_POST['email'];
} else {
  exit("No player identification provided :-(");
}
if (strlen($email) == 0) {
  exit("Empty player identification provided :-(");
}

# Generate new secrtet parameter
$secret = rand();   # I know, this sucks. It is not random enough.

# Initiate team
$team = createT_DB($email, $secret);

# Initiate first question
createQ_DB($team, $secret, 0);

header("Location: question.php?question=0&secret=".$secret."&team=".$team);
die();

?>
