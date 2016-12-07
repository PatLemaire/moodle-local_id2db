<?php
//TODO : Tâche planifiée dans le CRON Moodle
//       Empêcher la création de cohortes vides si étu fantôme
//       Purger les cohortes vides et/ou non utilisées
//       Limiter le nb de cohortes créées en une fois (limite de charge)

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

//$max_cohort_created = 500; // Nombre de cohortes créées en une fois...

// Récupération des paramètres système du module
if ($config = get_config('local_id2db')) {
    $remoteusernamefield=$config->remoteusernamefield;
    $remotecohortfield=$config->remotecohortfield;
    $remotetablename=$config->remotetablename;
    $PARAM_hote = $config->dbhost; // le chemin vers le serveur
    $PARAM_port = $config->dbport;
    $PARAM_nom_bd = $config->dbname; // le nom de votre base de données
    $PARAM_utilisateur = $config->dbuser; // nom d'utilisateur pour se connecter
    $PARAM_mot_passe = $config->dbpass; // mot de passe de l'utilisateur pour se connecter
    $forbiddenchar = explode(",",$config->forbiddenchar); 
    $cohortdesc=$config->cohortdesc;
    $cohortsufx=$config->cohortsufx;
    $customquery=$config->customquery;
    if (strlen($customquery)>0) {
        // TODO : gérer une erreur si {le champs}*} n'est pas présents dans la requête personnalisée
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

// extraire les codes apogée depuis mdl_course->idnumber dans un tableau : sql(mdl_course)->codelist[courseid,codelist]
echo get_string('stage1', 'local_id2db').$SautDeLigne. PHP_EOL;
$sql = " SELECT c.id,c.idnumber FROM {course} c where idnumber<>''";
$codelist = $DB->get_records_sql($sql);
// On veut une liste de tous les codes utilisés (suppression des doublons et retrait des caractères inutiles)
// Pour chaque codes dans codelist faire :
foreach ($codelist as $codes) {
    //exploser codes dans code[] avec le séparateur 'virgule'
    // TODO : rendre le séparateur paramétrable
    $code = explode(',', str_replace($forbiddenchar, '', $codes->idnumber));
    // pour chaque code[i] faire :
    foreach ($code as $code_cohort) {
        // on alimente une liste des codes utilisés
        $liste_codes_utiles[] = $code_cohort;
    }
}

$nb_cohort_created = 0; // Initialisation du compteur de cohortes créées...
//TODO : exploiter le compteur ci-dessus :o)

// dans $liste_codes_utiles[], on a une liste "propre" mais AVEC doublons possibles
foreach (array_unique($liste_codes_utiles) as $code_cohort_utile) {
    // recherche les login étu dans la bdd qui sont inscrits dans code[i] : sql(code[i])->logins[]
    echo get_string('codecohort', 'local_id2db') . $code_cohort_utile.$SautDeLigne . PHP_EOL;
    if (strlen($customquery)>0) {
        // Présence d'un requête personnalisée...
        // TODO : gérer une erreur si les champs ne sont pas présents dans la requête personnalisée
        $customquery_tmp =  str_replace("{*}", "'".$code_cohort_utile."'", $customquery);
        $resultats = $connexion->query($customquery_tmp);
    } else {
        // requête classique...
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
    $resultats->closeCursor();   // On libère temporairement le serveur de Bdd
    if (Count($liste_etu) > 0) { // S'il y a au moins 1 étudiant
        if (!is_cohort_already_exist($code_cohort_utile)) { // Si la cohorte n'existe pas encore
            // Créer la cohorte
            echo get_string('createcohort', 'local_id2db') . $code_cohort_utile . "...".$SautDeLigne. PHP_EOL;
            $nb_cohort_created+=1;
            // A revoir : if ($nb_cohort_created>$max_cohort_created) break;
            $idcohort = create_cohort($code_cohort_utile,$cohortsufx,$cohortdesc);
        }
        if (is_id2db_cohort($code_cohort_utile)) {
            $idcohort = get_cohort_id($code_cohort_utile);
            // On ajoute les étudiants manquants
            $etu_cohort = get_cohort_member($code_cohort_utile);
            $etu_a_ajouter = array_diff($liste_login, $etu_cohort);
            if (count($etu_a_ajouter) > 0) {
                echo count($etu_a_ajouter) . get_string('missingstudents', 'local_id2db').$SautDeLigne.PHP_EOL;
                add_users_to_cohort($etu_a_ajouter, $idcohort);
            }
            // On supprime les étudiants en trop
            $etu_a_supprimer = array_diff($etu_cohort, $liste_login);
            if (count($etu_a_supprimer) > 0) {
                echo count($etu_a_supprimer) . get_string('obsostudent', 'local_id2db').$SautDeLigne.PHP_EOL;
                remove_users_to_cohort($etu_a_supprimer, $idcohort);
            }
        } else {
            // TODO : Erreur cohorte déjà existante à traiter !!!
            // impossible de créer une cohorte portant le même idnumber
            echo "Cohort en doublon !!".$SautDeLigne.PHP_EOL;
        }
    } else {
        echo get_string('nostudentindb', 'local_id2db') . $code_cohort_utile . $SautDeLigne.PHP_EOL;
        // On doit traité le cas d'une cohorte obsolète !
        if (is_cohort_already_exist($code_cohort_utile) AND is_id2db_cohort($code_cohort_utile)) {
            delete_cohort($code_cohort_utile);
            echo get_string('obsocohort', 'local_id2db') . $code_cohort_utile .$SautDeLigne.PHP_EOL;
        }
    }
}

// Etape N°2 (Mettre à jour les méthodes d'inscription avec cohortes)
echo get_string('stage2', 'local_id2db').$SautDeLigne.PHP_EOL;
// Pour chaque codes dans codelist faire :
foreach ($codelist as $codes) {
    //exploser codes dans code[] avec le séparateur 'virgule'
    $code = explode(',', str_replace($forbiddenchar, '', $codes->idnumber));
    $id_cours = $codes->id;
    // pour chaque code[i] faire :
    echo get_string('forcourse', 'local_id2db') . $id_cours .$SautDeLigne. PHP_EOL;
    // ajout des méthodes cohortes manquantes depuis idnumber
    echo get_string('addmissingcohortsmeth', 'local_id2db').$SautDeLigne.PHP_EOL;
    foreach ($code as $code_cohort) {
        //supression des caractères inutiles
        $id_cohort = get_cohort_id($code_cohort);
        if ($id_cohort > 0) {
            echo get_string('addinstance', 'local_id2db') . $code_cohort . " (" . $id_cohort . ")";
            // vérifier que la cohorte n'est pas déjà liée
            if (!is_cohort_already_linked($id_cours, $id_cohort)) {
                // sinon ajouter une méthode
                add_cohort_to_course($id_cours, $code_cohort);
                echo get_string('addnewinstance', 'local_id2db').$SautDeLigne.PHP_EOL;
            } else {
                echo get_string('existinginstance', 'local_id2db').$SautDeLigne.PHP_EOL;
            }
        }
    }
    // suppression des cohortes en trop
    echo get_string('supprinstance', 'local_id2db').$SautDeLigne.PHP_EOL;
    $instances = $DB->get_recordset('enrol', array('enrol' => 'cohort', 'courseid' => $id_cours));
    foreach ($instances as $instance) {
    	if (strpos($instance->name,$cohortsufx)>0) {
        $cohort = $DB->get_record('cohort', array('id' => $instance->customint1));
        // on cherche la présence dans les codes du idnumber (case-insensitice)
        if (!in_array(strtolower($cohort->idnumber), array_map('strtolower',$code))) {
            echo get_string('delinstance', 'local_id2db') . $instance->id . $SautDeLigne.PHP_EOL;
            $enrol = enrol_get_plugin('cohort');
            $course_instances = enrol_get_instances($id_cours, false);
            $enrol->delete_instance($course_instances[$instance->id]);
        }
      }
    }
}

// Etape N°3 (suppression des méthodes pour les cours sans idnumber)
// TODO : Inclure les cours avec IDnumber mais pas de code valide
echo get_string('stage3', 'local_id2db').$SautDeLigne.PHP_EOL;
$sql = " SELECT c.id FROM {course} c where idnumber=''";
$coursSansIDnumber = $DB->get_records_sql($sql);
// Pour chaque codes dans coursSansIDnumber faire :
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
