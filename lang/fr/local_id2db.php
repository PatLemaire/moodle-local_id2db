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
 * ID2DB enrolment plugin language 'fr'
 *
 * @package    local_id2db
 * @copyright  2016 Patrick LEMAIRE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */  

$string['pluginname'] = 'Liaison IDNumber vers Bdd';
$string['pluginname_desc'] = 'Utilise une base de données externe et procède aux inscriptions par cohorts en fonction de codes renseignés dans le paramètre Numéro d\'identification du cours (IDNumber)';


// Settings

$string['id2dbsettings'] = 'Configuration id2db';

$string['settingsheaderdb'] = 'Connection à la base de données externe';
$string['settingsheaderdb_desc'] = 'Ci-dessous, indiquez les paramètres nécessaires à la connection à la base de données externe.';
$string['dbhost'] = 'Serveur de base de données';
$string['dbhost_desc'] = 'Renseigner l\'adresse IP ou le nom du serveur de base de données.';
$string['dbport'] = 'Port de connexion';
$string['dbname'] = 'Nom de la base de données';
$string['dbname_desc'] = 'Nom de la base de données';
$string['dbuser'] = 'Nom de l\'utilisateur';
$string['dbpass'] = 'Mot de passe de l\'utilisateur';
$string['remotetablename'] = 'Nom de la table';
$string['remotetablename_desc'] = 'Renseigner le nom de la table qui contient les informations.';

$string['remotefields'] = 'Nom des champs externes';
$string['remotefields_desc'] = 'Ci-dessous, indiquez les noms des champs servant à la génération des cohorts.';
$string['remotecohortfield'] = 'Champ du code cohort';
$string['remotecohortfield_desc'] = 'Renseigner le nom du champ dans la base de données externe qui sera utilisé pour la liaison.';
$string['remoteusernamefield'] = 'Champ du nom d\'utilisateur';
$string['remoteusernamefield_desc'] = 'Renseigner le nom du champ dans la base de données externe associé au nom d\'utilisateur.';

$string['customquery'] = 'Requête personnalisée';
$string['customquery_desc'] = 'Il est possible que la source de données se présente sous une forme plus complexe et nécessite l\'utilisation d\'une requête plus élaborée. Saisissez ici la requête nécessaire pour obtenir les données. Notez que {*} indique l\'endroit dans la requête où le code sera comparé...';

$string['othersettings'] = 'Autres réglages';
$string['forbiddenchar'] = 'Caractères à supprimer';
$string['forbiddenchar_desc'] = 'Liste des caractères, séparés par une virgule, qui seront automatiquement supprimer à la lecture des codes.';
$string['cohortsufx'] = 'Suffixe du nom des cohortes';
$string['cohortsufx_desc'] = 'Indiquer une chaine qui permettra de distinguer les cohortes générées par id2db des autres. Le résultat sera de la forme [Code] [chaine]. Exemple : Code1 (Cohorte automatique)';
$string['cohortsufx_default'] = '(Cohorte automatique)';
$string['cohortdesc'] = 'Description des cohortes';
$string['cohortdesc_desc'] = 'Renseigner la description qui servira pour les cohortes générées par id2db.';
$string['cohortdesc_default'] = 'Cohorte pour les inscriptions automatiques';

// For trace/log
$string['stage1'] = 'Etape N°1 (Mettre à jour les cohortes)';
$string['codecohort'] = 'Code Cohort=';
$string['nbstudent'] = 'Nbr etu =';
$string['createcohort'] = 'Création de la cohorte ';
$string['missingstudents'] = ' étudiants manquants...';
$string['obsostudent'] = ' étudiants en trop...';
$string['nostudentindb'] = 'Aucun étudiant pour la cohorte dans la Bdd pour ';
$string['obsocohort'] = 'Cohorte obsolète supprimée : ';
$string['stage2'] = 'Etape N°2 (Mettre à jour les méthodes d\'inscription avec cohortes)';
$string['forcourse'] = 'Pour le cours n°';
$string['addmissingcohortsmeth'] = '...ajout des cohortes manquantes depuis idnumber';
$string['addinstance'] = 'Liaison avec la cohorte: ';
$string['addnewinstance'] = ' ajouté !';
$string['existinginstance'] = ' : OK';
$string['supprinstance'] = '...suppression des cohortes en trop';
$string['delinstance'] = 'Méthode obsolète ID=';
$string['stage3'] = 'Etape N°3 (suppression des méthodes pour les cours sans idnumber)';
$string['coursetarget'] = 'Pour le cours ID=';
$string['uselessinstance'] = 'Association de méthode cohorte inutile : ';
$string['notid2dbcohort'] = 'Cohort non concernée ';