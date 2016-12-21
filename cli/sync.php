<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ID2DB CLI sync
 *
 * @package    local_id2db
 * @copyright  2016 Patrick LEMAIRE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//TODO : CRON task in Moodle
//       Prevent from creating an empty cohorts if student don't exist
//       Delete empty cohorts vides and/or non used one
//       Limit the number of cohorts created at one time (load limit)

define('CLI_SCRIPT', true);
if (php_sapi_name() != "cli") {
    // In cli-mode
    $SautDeLigne="<br />";
} else {
    $SautDeLigne="";
}

require (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require ('sync_id2db_lib.php');
require_once("$CFG->dirroot/enrol/cohort/locallib.php");

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

// Retrieving system parameters from the module
if ($config = get_config('local_id2db')) {
    $remoteusernamefield=$config->remoteusernamefield;
    $remotecohortfield=$config->remotecohortfield;
    $remotetablename=$config->remotetablename;
    $PARAM_hote = $config->dbhost; // Path to the server
    $PARAM_port = $config->dbport;
    $PARAM_nom_bd = $config->dbname; // Name of your database
    $PARAM_utilisateur = $config->dbuser; // Username to log in
    $PARAM_mot_passe = $config->dbpass; // User password to log in
    $forbiddenchar = explode(",",$config->forbiddenchar); 
    $cohortdesc=$config->cohortdesc;
    $cohortsufx=$config->cohortsufx;
    $customquery=$config->customquery;
    if (strlen($customquery)>0) {
        // TODO : Handle an error if {field}*} is not present in the custom query
        $customquery=  str_replace("{".$remoteusernamefield."}", $remoteusernamefield, $customquery);
        $customquery=  str_replace("{".$remotecohortfield."}", $remotecohortfield, $customquery);        
        $customquery=  str_replace("{".$remotetablename."}", $remotetablename, $customquery);
    }
}

try {
    $connexion = new PDO('mysql:host=' . $PARAM_hote . ';dbname=' . $PARAM_nom_bd, $PARAM_utilisateur, $PARAM_mot_passe);
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage() . '<br />';
    echo 'N° : ' . $e->getCode();
    die();
}

global $DB;

$starttime = microtime();

// Extract codes from mdl_course->idnumber in an array : sql(mdl_course)->codelist[courseid,codelist]
echo get_string('stage1', 'local_id2db').$SautDeLigne. PHP_EOL;
$sql = " SELECT c.id,c.idnumber FROM {course} c where idnumber<>''";
$codelist = $DB->get_records_sql($sql);
// We want a list of all the codes used (removing duplicates and removing unnecessary characters)
foreach ($codelist as $codes) {
    // TODO : Make the separator customisable
    $code = explode(',', str_replace($forbiddenchar, '', $codes->idnumber));
    // for every code[i] do :
    foreach ($code as $code_cohort) {
        // We feed a list of codes used
        $liste_codes_utiles[] = $code_cohort;
    }
}

$nb_cohort_created = 0; // Initializing the created cohort counter...
//TODO : Operate the above counter :o)

// in $liste_codes_utiles[], we have a list of "proper" codes but still with duplicate
foreach (array_unique($liste_codes_utiles) as $code_cohort_utile) {
    // Search for usernames in DB who are registred in code[i] : sql(code[i])->logins[]
    echo get_string('codecohort', 'local_id2db') . $code_cohort_utile.$SautDeLigne . PHP_EOL;
    if (strlen($customquery)>0) {
        // Presence of a custom query...
        // TODO : Handle an error if the fields are not present in the custom query
        $customquery_tmp =  str_replace("{*}", "'".$code_cohort_utile."'", $customquery);
        $resultats = $connexion->query($customquery_tmp);
    } else {
        // Default query...
        $customquery_tmp = "SELECT distinct
        (" . $remoteusernamefield . ")
        FROM
        ".$remotetablename."
        WHERE
        ".$remotecohortfield." = '" . $code_cohort_utile . "'";
        $resultats = $connexion->query($customquery_tmp);
    }
    $liste_etu = $resultats->fetchAll();
    
    echo get_string('nbstudent', 'local_id2db') . Count($liste_etu).$SautDeLigne . PHP_EOL;
    $liste_login = array();
    foreach ($liste_etu as $login) {        
        $liste_login[] = $login[$remoteusernamefield];
    }
    $resultats->closeCursor();   // Temporarily releasing DB connexion
    if (Count($liste_etu) > 0) { // If there is at least 1 student
        if (!is_cohort_already_exist($code_cohort_utile)) { // If the cohort does not exist yet
            // Create Cohort
            echo get_string('createcohort', 'local_id2db') . $code_cohort_utile . "...".$SautDeLigne. PHP_EOL;
            $nb_cohort_created+=1;
            // TODO : if ($nb_cohort_created>$max_cohort_created) break;
            $idcohort = create_cohort($code_cohort_utile,$cohortsufx,$cohortdesc);
        }
        if (is_id2db_cohort($code_cohort_utile)) {
            $idcohort = get_cohort_id($code_cohort_utile);
            // Missing students are added
            $etu_cohort = get_cohort_member($code_cohort_utile);
            $etu_a_ajouter = array_diff($liste_login, $etu_cohort);
            if (count($etu_a_ajouter) > 0) {
                echo count($etu_a_ajouter) . get_string('missingstudents', 'local_id2db').$SautDeLigne.PHP_EOL;
                add_users_to_cohort($etu_a_ajouter, $idcohort);
            }
            // Extra students are suppressed
            $etu_a_supprimer = array_diff($etu_cohort, $liste_login);
            if (count($etu_a_supprimer) > 0) {
                echo count($etu_a_supprimer) . get_string('obsostudent', 'local_id2db').$SautDeLigne.PHP_EOL;
                remove_users_to_cohort($etu_a_supprimer, $idcohort);
            }
        } else {
            // TODO : Error "Cohort already exists" to be processed !!!
            // Impossible to create a cohort with the same idnumber
            echo "Cohort en doublon !!".$SautDeLigne.PHP_EOL;
        }
    } else {
        echo get_string('nostudentindb', 'local_id2db') . $code_cohort_utile . $SautDeLigne.PHP_EOL;
        // We must treat the case of an obsolete cohort!
        if (is_cohort_already_exist($code_cohort_utile) AND is_id2db_cohort($code_cohort_utile)) {
            delete_cohort($code_cohort_utile);
            echo get_string('obsocohort', 'local_id2db') . $code_cohort_utile .$SautDeLigne.PHP_EOL;
        }
    }
}

// Step 2 (Update enroll methods with cohorts)
echo get_string('stage2', 'local_id2db').$SautDeLigne.PHP_EOL;
foreach ($codelist as $codes) {
    $code = explode(',', str_replace($forbiddenchar, '', $codes->idnumber));
    $id_cours = $codes->id;
    echo get_string('forcourse', 'local_id2db') . $id_cours .$SautDeLigne. PHP_EOL;
    // Adding missing cohort methods with an specific idnumber
    echo get_string('addmissingcohortsmeth', 'local_id2db').$SautDeLigne.PHP_EOL;
    foreach ($code as $code_cohort) {
        $id_cohort = get_cohort_id($code_cohort);
        if ($id_cohort > 0) {
            echo get_string('addinstance', 'local_id2db') . $code_cohort . " (" . $id_cohort . ")";
            // Check that the cohort is not already linked
            if (!is_cohort_already_linked($id_cours, $id_cohort)) {
                // Otherwise add a method
                add_cohort_to_course($id_cours, $code_cohort);
                echo get_string('addnewinstance', 'local_id2db').$SautDeLigne.PHP_EOL;
            } else {
                echo get_string('existinginstance', 'local_id2db').$SautDeLigne.PHP_EOL;
            }
        }
    }
    // Suppressing excess cohorts
    echo get_string('supprinstance', 'local_id2db').$SautDeLigne.PHP_EOL;
    $instances = $DB->get_recordset('enrol', array('enrol' => 'cohort', 'courseid' => $id_cours));
    foreach ($instances as $instance) {
    	if (strpos($instance->name,$cohortsufx)>0) {
        $cohort = $DB->get_record('cohort', array('id' => $instance->customint1));
        // We seek the presence in the codes for idnumber (case-insensitice)
        if (!in_array(strtolower($cohort->idnumber), array_map('strtolower',$code))) {
            echo get_string('delinstance', 'local_id2db') . $instance->id . $SautDeLigne.PHP_EOL;
            $enrol = enrol_get_plugin('cohort');
            $course_instances = enrol_get_instances($id_cours, false);
            $enrol->delete_instance($course_instances[$instance->id]);
        }
      }
    }
}

// Step N ° 3 (deletion of methods from courses without idnumber)
// TODO : Include courses with IDnumber but no valid codes
echo get_string('stage3', 'local_id2db').$SautDeLigne.PHP_EOL;
$sql = " SELECT c.id FROM {course} c where idnumber=''";
$coursSansIDnumber = $DB->get_records_sql($sql);
foreach ($coursSansIDnumber as $cours) {
	$si_methodeCohort = $DB->count_records('enrol', array('enrol' => 'cohort', 'courseid' => $cours->id));
	if ($si_methodeCohort!=0) {
		echo get_string('coursetarget', 'local_id2db').$cours->id." :".$SautDeLigne. PHP_EOL;
		$instances = $DB->get_recordset('enrol', array('enrol' => 'cohort', 'courseid' => $cours->id));
		foreach ($instances as $instance) {
			if (strpos($instance->name,$cohortsufx)>0) {
				echo get_string('uselessinstance', 'local_id2db').$instance->name."(ID=".$instance->id.") -> ".$cours->id.$SautDeLigne.PHP_EOL;
				$enrol = enrol_get_plugin('cohort');
				$course_instances = enrol_get_instances($cours->id, false);
				$enrol->delete_instance($course_instances[$instance->id]);
			} else {
				echo get_string('notid2dbcohort', 'local_id2db').$instance->name."(".$instance->id.")".$SautDeLigne.PHP_EOL;
			}
		}
	}
}


$difftime = microtime_diff($starttime, microtime());
print("Execution took " . $difftime . " seconds" . PHP_EOL);
