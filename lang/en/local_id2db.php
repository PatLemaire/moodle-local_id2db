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
 * ID2DB enrolment plugin language 'en'
 *
 * @package    local_id2db
 * @copyright  2016 Patrick LEMAIRE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */  

$string['pluginname'] = 'Link IDNumber to DataBase';
$string['pluginname_desc'] = 'Uses an external database and performs cohort registrations based on codes populated in the Course ID parameter (IDNumber)';


// Settings

$string['id2dbsettings'] = 'Settings id2db';

$string['settingsheaderdb'] = 'Connection to the external database';
$string['settingsheaderdb_desc'] = 'Below, specify the parameters needed to connect to the external database.';
$string['dbhost'] = 'Database Server';
$string['dbhost_desc'] = 'Enter the IP address or name of the database server.';
$string['dbport'] = 'Connection port';
$string['dbname'] = 'Name of the data base';
$string['dbname_desc'] = 'Name of the data base';
$string['dbuser'] = 'Username of the Database user';
$string['dbpass'] = 'Password of the Database user';
$string['remotetablename'] = 'Table name';
$string['remotetablename_desc'] = 'Enter the name of the table that contains the information.';

$string['remotefields'] = 'Name of external fields';
$string['remotefields_desc'] = 'Below, provide the names of fields used to generate cohorts.';
$string['remotecohortfield'] = 'Cohort code field';
$string['remotecohortfield_desc'] = 'Enter the field name in the external database that will be used for the link.';
$string['remoteusernamefield'] = 'Username field';
$string['remoteusernamefield_desc'] = 'Enter the field name in the external database associated with the user\'s name.';

$string['customquery'] = 'Custom Query';
$string['customquery_desc'] = 'It is possible that the data source is in a more complex form and requires the use of a more elaborate query. Enter here the query needed to get the data. Note that {*} indicates the location in the query where the code will be compared ...';

$string['othersettings'] = 'Other settings';
$string['forbiddenchar'] = 'Characters to be deleted';
$string['forbiddenchar_desc'] = 'List of characters, separated by a comma, which will be automatically deleted when reading codes.';
$string['cohortsufx'] = 'Cohort name suffix';
$string['cohortsufx_desc'] = 'Specify a string that will distinguish the cohorts generated by id2db from the others. The result will follow [Code] [string]. Example: Code1 (Automatic Cohort)';
$string['cohortsufx_default'] = '(Automatic Cohort)';
$string['cohortdesc'] = 'Description of Cohorts';
$string['cohortdesc_desc'] = 'Enter the description that will be used for the cohorts generated by id2db.';
$string['cohortdesc_default'] = 'Cohort for automatic enrollments';

// For trace/log
$string['stage1'] = 'Step 1 (Update Cohorts)';
$string['codecohort'] = 'Cohort Code =';
$string['nbstudent'] = 'Number students =';
$string['createcohort'] = 'Creating the Cohort ';
$string['missingstudents'] = ' missing students...';
$string['obsostudent'] = ' obsolete students...';
$string['nostudentindb'] = 'No student for the cohort in the database for ';
$string['obsocohort'] = 'Obsolete Cohort Removed : ';
$string['stage2'] = 'Step 2 (Update registration methods with cohorts)';
$string['forcourse'] = 'For the course n°';
$string['addmissingcohortsmeth'] = '...add missing cohorts from idnumber';
$string['addinstance'] = 'Liaison with cohort: ';
$string['addnewinstance'] = ' added !';
$string['existinginstance'] = ' : OK';
$string['supprinstance'] = '...removing obsolete cohorts';
$string['delinstance'] = 'Obsolete method ID=';
$string['stage3'] = 'Step 3 (removing methods for courses without idnumber)';
$string['coursetarget'] = 'For the course ID=';
$string['uselessinstance'] = 'Instance of unnecessary cohort method : ';
$string['notid2dbcohort'] = 'Cohort not affected ';