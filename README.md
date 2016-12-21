# moodle-local_id2db

First attempt to publish a moodle plugin...

PLEASE BE TOLERANT and really patient ;o)

## General purpose
If you want to synchronize your course enrollments with an external database, you can choose to activate the 'database' plugin. Each time a user logs into Moodle, it will check for registration data that matches. A big problem with this mechanism is that you have to wait until the user is connected at least once to start communication, for example.

What we are proposing here is a mechanism that still operates a database but that periodically enroll users to courses that match through cohort methods.

## What this module does...
3 steps :
* Step 1: Update Cohorts

Look for all the courses with IDNumber. Every value is splited into codes. Each code is submitted to the database and if only one user is found, a cohort is created. If there are new students, they will be added to the cohort, if there are fewer, they will be removed from the cohort.
* Step 2: Update registration methods with cohorts

From previous step, the methods are attached to the courses or else removed.
* Step 3: Removing methods for courses without idnumber

Final step, if a course had idnumber but it has been removed after, the methods are also removed.

## A few points...
Until now the scheduled task is triggered by the CRON. You will have to fix the frequency of this task directly in the CRONTAB (see below).

## Installation
Place the unzipped folder in the moodle/local/ (should be moodle/local/id2db rename it if necessary).

Go to your moodle/admin/index.php and refresh to see the new module to be installed. You will be asked to enter some parameters and you need to add a task in the CRONTAB of your server.

For example, if you want the task to run every hour: 
```
0 * * * *   apache       /usr/bin/php /path/to/moodle/local/id2db/cli/sync.php >/dev/null 2>&1
```
Parameters are still reachable from local plugin administration menu of the site.
