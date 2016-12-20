<?php

require_once("$CFG->dirroot/lib/dml/moodle_database.php");

/**
 * Returns if cohort idnumber already exist.
 * @param string $idnumber
 * @return bool if cohort idnumber already exist
 */
function is_cohort_already_exist($idnumber) {
    global $DB;
    $sql = "SELECT c.idnumber FROM {cohort} c where idnumber = :idnumber";
    $params = array('idnumber' => $idnumber);
    $query_result = $DB->get_records_sql($sql, $params);
    if (count($query_result) > 0)
        return true;
}

/**
 * Returns the id of an existing cohort
 * @param string $idnumber
 * @return int id of the cohort
 */
function get_cohort_id($idnumber) {
    global $DB;
    $sql = "SELECT c.id FROM {cohort} c where idnumber = :idnumber";
    $params = array('idnumber' => $idnumber);
    $query_result = $DB->get_records_sql($sql, $params);
    foreach ($query_result as $result) {
        return $result->id;
    }
    return 0;
    // TODO: Check if unique
}

/**
 * Create a new cohort 
 * @param string $idnumber
 * @param string $namesuffix
 * @param string $description
 * @return cohort id
 */
function create_cohort($idnumber,$namesuffix,$description) {
    $cohort = new StdClass();
    $cohort->name = $idnumber . ' '.$namesuffix;
    $cohort->idnumber = $idnumber;
    $cohort->contextid = context_system::instance()->id;
    $cohort->description = $description;
    return cohort_add_cohort($cohort);
}

/**
 * Return if a cohort is a id2db cohort with IDnumber as parameter
 * @param string $idnumber
 * @return bool
 */
function is_id2db_cohort($idnumber) {
    global $DB;
    $idcohort=get_cohort_id($idnumber);  
    if ($idcohort>0) {
        $cohort = $DB->get_record('cohort', array('id'=>$idcohort), '*', MUST_EXIST);
        //$cohort->contextid = context_system::instance()->id;
        if ($config = get_config('local_id2db')) {
            $cohortsufx=$config->cohortsufx;
        }
        if (strpos($cohort->name,$cohortsufx)>0) {
            // The string contains the suffix !! This should be an id2db cohort ...
            // TODO : Check that the suffix is at the end of the name
            return true;
        }
      }
    return false;
}

/**
 * Delete a cohort 
 * @param string $idnumber
 * @return void
 */
function delete_cohort($idnumber) {
	global $DB;
	$idcohort=get_cohort_id($idnumber);  
	if ($idcohort>0) {
		$cohort = $DB->get_record('cohort', array('id'=>$idcohort), '*', MUST_EXIST);
    $cohort->contextid = context_system::instance()->id;
    cohort_delete_cohort($cohort);
  }
}

/**
 * Returns list of member for a certain cohort in parameter
 * @param string $idnumber
 * @return array of usernames member
 */
function get_cohort_member($idnumber) {
    global $DB;
    $member_list = array();
    $cohortid = get_cohort_id($idnumber);
    $sql = " SELECT u.id,u.username
                          FROM {user} u
                         JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                        WHERE u.deleted=0";
    $params['cohortid'] = $cohortid;
    $members = $DB->get_records_sql($sql, $params);
    foreach ($members as $user) {
        $member_list[] = $user->username;
    }
    return $member_list;
}

/**
 * Add a method cohort to a course
 * @param int $courseid
 * @param int $cohort_idnumber
 * @return void
 */
function add_cohort_to_course($courseid, $cohort_idnumber) {
    global $DB;
    $enrol = enrol_get_plugin('cohort');
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            if ($config = get_config('local_id2db')) {
            $cohortsufx=$config->cohortsufx;
        }
    $enrol->add_instance($course, array('name' => $cohort_idnumber . ' '.$cohortsufx, 'status' => 0, 'customint1' => get_cohort_id($cohort_idnumber), 'roleid' => 5, 'customint2' => 0));
    // Force the sync after adding a new method ...
    $trace = new null_progress_trace();
    enrol_cohort_sync($trace, $course->id);
    $trace->finished();
}

/**
 * Return if a method cohort is already linked to a course
 * @param int $courseid
 * @param int $cohortid
 * @return bool
 */
function is_cohort_already_linked($courseid, $cohortid) {
    $enrol = enrol_get_plugin('cohort');
    $instances = enrol_get_instances($courseid, false);
    foreach ($instances as $instance) {
        if (($instance->customint1 == $cohortid) and ($instance->enrol == 'cohort'))
            return true;
    }
    return false;
}

/**
 * Add students (array of login) to a cohort
 * and return true if everything is good
 * @param array of string: users login
 * @param int $cohortid
 * @return bool
 */
function add_users_to_cohort($users, $cohortid) {
	// TODO : Untraducted strings !!!
    global $DB;
    if (php_sapi_name() != "cli") {
    // In cli-mode
    $SautDeLigne="<br />";
} else {
    $SautDeLigne="";
}
    foreach ($users as $user) {
        if ($DB->get_record('cohort', array('id' => $cohortid))) {
            if ($userid = $DB->get_record('user', array('username' => $user))) {
                cohort_add_member($cohortid, $userid->id);
            } else {
                echo "L'utilisateur " . $user . " n'existe pas !".$SautDeLigne.PHP_EOL;
            }
        } else {
            echo "La cohorte " . $cohortid . " n'existe pas !".$SautDeLigne.PHP_EOL;
            return false;
        }
    }
    return true;
}

/**
 * Remove students (array of login) to a cohort
 * and return true if everything is good
 * @param array of string: users login
 * @param int $cohortid
 * @return bool
 */
function remove_users_to_cohort($users, $cohortid) {
	// TODO : Untraducted strings !!!
    global $DB;
    foreach ($users as $user) {
        if ($DB->get_record('cohort', array('id' => $cohortid))) {
            if ($userid = $DB->get_record('user', array('username' => $user))) {
                cohort_remove_member($cohortid, $userid->id);
                //echo "cohort_remove_member(" . $cohortid . "," . $userid->id . ")<BR />";
            } else {
                echo "L'utilisateur " . $user . " n'existe pas !".PHP_EOL;
            }
        } else {
            echo "La cohorte " . $cohortid . " n'existe pas !".PHP_EOL;
            return false;
        }
    }
    return true;
}