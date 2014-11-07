<?php
require_once('functions.php');
require_once('data.php');
html_header($contest);
?>

<h1>Welcome to <?php echo $contest; ?></h1>

<?php echo $contest_text; ?>

<form method='POST' action='initiate.php'>
  Player: <input type='text' name='email'>
  <input type='submit' value='Answer'>
</form>

<?php
html_footer();
?>
