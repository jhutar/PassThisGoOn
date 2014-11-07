PassThisGoOn
============

PHP application used to run online contest where you were shown next question only when you passed previous one, mesuring time.

data.php
--------

Here you define actual questions and texts for you competition.

`$contest` ... name of your contest

`$contest_text` ... text with instructions shown when users are going to start

`$contest_bye` ... text at the end when all questions are answered

`$questions` ... is an array of questions to ask where each question is array with **text** key whose value is actual question and **answer** key whose value can be either *string* (then it is normalized same way as users actual answer and then just compared to get pass/fail) of *function* (then the function is executed with only param being users actual answer and its return value determines pass/fail)

results.php
-----------

Here you can watch how users are progressing.

data.csv and teams.csv
----------------------

These files have to be writable (check both Linux permissions and correct SELinux context). On Fedora or Red Hat Enterprise Linux that would be something like:

    cd /var/www/html
    chown apache *.csv
    chmod u+w *.csv
    chcon -t httpd_sys_rw_content_t *.csv
