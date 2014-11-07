<?php

function load_variable($name) {
  if (isset($_GET[$name])) {
    return $_GET[$name];
  } elseif (isset($_POST[$name])) {
    return $_POST[$name];
  } else {
    exit("No ".$name." provided :-(");
  }
}

function loadT_DB($team) {
  # Load correct row from DB
  # CSV database format:
  # <team>,<secret>,<email_or_something>
  $loaded = null;

  $file = fopen("teams.csv", "r");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      if ((int) $data[0] == $team) {
        # We have our row, go on
        $loaded = array((int) $data[0], (int) $data[1], $data[2]);
        break;
      }
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);

  return $loaded;
}

function listT_DB() {
  # List teams
  $loaded = array();

  $file = fopen("teams.csv", "r");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      $loaded[] = array('team' => (int) $data[0], 'secret' => (int) $data[1], 'email' => $data[2]);
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);

  return $loaded;
}

function createT_DB($email, $secret) {
  $team = 0;
  $file = fopen("teams.csv", "r+");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      # Find unique team ID
      if ((int) $data[0] >= $team) {
        $team = (int) $data[0] + 1;
      }
      if ($data[2] == $email) {
        exit("Identification of player already used :-(");
      }
    }
    fseek($file, 0, SEEK_END);
    fputcsv($file, array($team, $secret, $email));
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

 fclose($file);
 return $team;
}

function timeT_DB($team) {
  # Count total time and number of passed questions for given team
  $q_count = 0;
  $q_time = 0;

  $file = fopen("data.csv", "r");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      if ((int) $data[0] == $team && (int) $data[3] != 0 && (int) $data[4] != 0) {
        $q_count = $q_count + 1;
        $q_time = $q_time + (int) $data[4] - (int) $data[3];
      }
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);

  return array($q_count, $q_time);
}

function loadQ_DB($team, $question) {
  # Load correct row from DB
  # CSV database format:
  # <team>,<secret>,<question>,<started>,<finished>
  $loaded = null;

  $file = fopen("data.csv", "r");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      #echo "<pre>\n";
      #print_r($data);
      #echo "</pre>\n";
      if ((int) $data[0] == $team && (int) $data[2] == $question) {
        # We have our row, go on
        $loaded = array((int) $data[0], (int) $data[1], (int) $data[2], (int) $data[3], (int) $data[4]);
        break;
      }
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);

  return $loaded;
}

function countQ_DB($team) {
  # Count number of already answered questions by the team
  $answered = 0;

  $file = fopen("data.csv", "r");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      if ((int) $data[0] == $team && (int) $data[4] != 0) {
        $answered++;
      }
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);

  return $answered;
}

function createQ_DB($team, $secret, $question) {
  $file = fopen("data.csv", "r+");

  if (flock($file, LOCK_EX)) {
    while (($data = fgetcsv($file)) !== FALSE) {
      if ((int) $data[0] == $team && (int) $data[2] == $question) {
        # We already have the row created, so do not create it again
        flock($file, LOCK_UN);
        fclose($file);
        return;
      }
    }
    fseek($file, 0, SEEK_END);
    fputcsv($file, array($team, $secret, $question, time(), 0));
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);
}

function endQ_DB($team, $question) {
  $file = fopen("data.csv", "r+");

  if (flock($file, LOCK_EX)) {
    $data_full = array();
    while (($data = fgetcsv($file)) !== FALSE) {
      if ((int) $data[0] == $team && (int) $data[2] == $question) {
        # If question was already answered, ignore this attempt to modify DB
        if ($data[4] != 0) {
          flock($file, LOCK_UN);
          return (int) $data[4];
        }
        $end = time();
        $data[4] = $end;
      }
      $data_full[] = $data;
    }
    ftruncate($file, 0);
    rewind($file);
    foreach ($data_full as $data) {
      fputcsv($file, $data);
    }
    flock($file, LOCK_UN);
  } else{
    exit("Could not lock DB file :-(");
  }

  fclose($file);
  return $end;
}

function evaluateQ($answer_given, $answer_correct) {
  if (is_callable($answer_correct)) {
    return call_user_func($answer_correct, $answer_given);
  } else {
    $answer_given = strtoupper(preg_replace('/\s+/', '', $answer_given));
    $answer_correct = strtoupper(preg_replace('/\s+/', '', $answer_correct));
    return $answer_given === $answer_correct;
  }
}

function html_header($contest, $team=null, $secret=null, $questions=null) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo $contest; ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="main.css" />
  <!-- Taken from http://www.getcsstemplates.com -->
</head>

<body>
  <div id="wrapper">

    <div id="header">
      <h1><?php echo $contest; ?></h1>
    </div>

<?php if (! is_null($team)) { ?>
    <div id="navbar">
      <div id="menu">
        <ul>
          <?php
            echo "          <li><a href='/'>Home</a></li>\n";
            $q_count = countQ_DB($team)+1;
            if ($q_count > count($questions)) {
              $q_count = count($questions);
            }
            for ($i = 1; $i <= $q_count; $i++) {
              echo "          <li><a href='question.php?question=".($i-1)."&team=".$team."&secret=".$secret."'>Q".$i."</a></li>\n";
            }
          ?>
        </ul>
      </div>
    </div>
<?php } ?>

    <div id="maintextcolumn">
<?php
}

function html_answer_form($question, $team, $secret, $questions) {
?>
<form method='POST' action='<?php echo $question + 1 >= count($questions) ? "finish.php" : "question.php"; ?>'>
  <input type='hidden' name='team' value='<?php echo (int) $team; ?>'>
  <input type='hidden' name='secret' value='<?php echo (int) $secret; ?>'>
  <input type='hidden' name='question' value='<?php echo (int) $question + 1; ?>'>
  <textarea name='answer' cols='50' rows='5'></textarea>
  <input type='submit' value='Answer'>
</form>
<?php
}

function html_footer() {
###Sharable link: echo "<a href='question.php?question=".$question."&secret=".$secret."' title='Share this link only with team mates'>Team '".$team."'</a>";
?>
    </div>
    <div id="footer">
      Page design: Copyright &copy; 2007 by <a href='http://www.getcsstemplates.com'>Free CSS Templates and Layouts</a>
    </div>

  </div>

</body>
</html>
<?php
}

function load_for_question() {
  # Load team ID
  $team = (int) load_variable('team');

  # Load secret string. Can be in GET or POST, where GET array have precedence.
  $secret = (int) load_variable('secret');

  # Load question ID
  $question = (int) load_variable('question');

  # Verify secret and team string
  list($team_db, $secret_db, $email_db) = loadT_DB($team);
  if ($secret != $secret_db) {
    exit("Incorrect secret string provided :-(");
  }
  if ($team != $team_db) {
    exit("Incorrect team ID provided :-(");
  }

  # Load answer to previous question (not applicale for first question)
  $previous_answer = null;
  if ($question != 0) {
    if (isset($_POST['answer'])) {
      $previous_answer = $_POST['answer'];
    }
  }

  # Load previous question (or first one if we are on 1st one - record was
  # created when we have logged in) to get timings and ensure we were allowed
  # to answer to it. Also make sure that if we were not supposed to see that
  # question, we do not go on with this question.
  $loaded = loadQ_DB($team, $question == 0 ? 0 : $question-1);
  if (is_null($loaded)) {
    exit("Previous question was not initiated for you yet, so you can not answer to it :-(");
  } else {
    list($previous_team, $previous_secret, $previous_question, $previous_start, $previous_end) = $loaded;
  }

  # Verify again just to be sure
  if ($previous_secret != $secret) {
    exit("Incorrect secret passed :-(");
  }
  if ($previous_team != $team) {
    exit("Incorrect team ID passed :-(");
  }

  # Return data we have loaded
  return array($team, $secret, $question, $previous_question, $previous_answer, $previous_start, $previous_end);
}

?>
