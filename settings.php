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
 * ID2DB enrolment plugin settings.
 *
 * @package    local_id2db
 * @copyright  2016 Patrick LEMAIRE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_id2db', new lang_string('id2dbsettings', 'local_id2db'));

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('local_id2db_settings', '', get_string('pluginname_desc', 'local_id2db')));
    
    //---- Connexion Bdd
    $settings->add(new admin_setting_heading('local_id2db_settingsheaderdb', get_string('settingsheaderdb', 'local_id2db'), ''));
    $settings->add(new admin_setting_heading('local_id2db_remotefieldsheader', '', get_string('settingsheaderdb_desc', 'local_id2db')));     

    $settings->add(new admin_setting_configtext('local_id2db/dbhost', get_string('dbhost', 'local_id2db'), get_string('dbhost_desc', 'local_id2db'), 'localhost'));
    $settings->add(new admin_setting_configtext('local_id2db/dbport', get_string('dbport', 'local_id2db'), '', '3306'));
    $settings->add(new admin_setting_configtext('local_id2db/dbname', get_string('dbname', 'local_id2db'), get_string('dbname_desc', 'local_id2db'), ''));
    $settings->add(new admin_setting_configtext('local_id2db/dbuser', get_string('dbuser', 'local_id2db'), '', ''));
    $settings->add(new admin_setting_configpasswordunmask('local_id2db/dbpass', get_string('dbpass', 'local_id2db'), ' ', ''));
    $settings->add(new admin_setting_configtext('local_id2db/remotetablename', get_string('remotetablename', 'local_id2db'), get_string('remotetablename_desc', 'local_id2db'), ''));

    //---- Champs distants
    $settings->add(new admin_setting_heading('local_id2db_remotefields', get_string('remotefields', 'local_id2db'), ''));
    $settings->add(new admin_setting_heading('local_id2db_remotefieldsheader', '', get_string('remotefields_desc', 'local_id2db')));    

    $settings->add(new admin_setting_configtext('local_id2db/remotecohortfield', get_string('remotecohortfield', 'local_id2db'), get_string('remotecohortfield_desc', 'local_id2db'), ''));
    $settings->add(new admin_setting_configtext('local_id2db/remoteusernamefield', get_string('remoteusernamefield', 'local_id2db'), get_string('remoteusernamefield_desc', 'local_id2db'), ''));
    $settings->add(new admin_setting_configtextarea('local_id2db/customquery', get_string('customquery', 'local_id2db'), get_string('customquery_desc', 'local_id2db'), 'SELECT distinct
        ({login})
        FROM
        {inscriptions}
        WHERE
        {code} = {*}'));
    
    //---- Autres
    $settings->add(new admin_setting_heading('local_id2db_others', get_string('othersettings', 'local_id2db'), ''));
    
    $settings->add(new admin_setting_configtext('local_id2db/forbiddenchar', get_string('forbiddenchar', 'local_id2db'), get_string('forbiddenchar_desc', 'local_id2db'), ' ,%,/,-,*,\',",+,_,&,#,$,Â£,â‚¬'));
    $settings->add(new admin_setting_configtext('local_id2db/cohortsufx', get_string('cohortsufx', 'local_id2db'), get_string('cohortsufx_desc', 'local_id2db'), get_string('cohortsufx_default', 'local_id2db')));
    $settings->add(new admin_setting_configtext('local_id2db/cohortdesc', get_string('cohortdesc', 'local_id2db'), get_string('cohortdesc_desc', 'local_id2db'), get_string('cohortdesc_default', 'local_id2db')));
    
    $ADMIN->add('localplugins', $settings);

}


