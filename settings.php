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
 * Mobbex enrolments Mobbexpayment plugin settings and presets.
 *
 * @package    enrol_mobbexpayment
 * @copyright  2021 Mobbex
 */

defined('MOODLE_INTERNAL') || die();

if (is_siteadmin()) {

    $settings->add(new admin_setting_heading('enrol_mobbexpayment_settings',
    '', get_string('pluginname_desc', 'enrol_mobbexpayment')));
    //API KEY
    $settings->add(new admin_setting_configtext('enrol_mobbexpayment/apikey', 'Mobbex Api Key',
    'Ingrese Api Key', '', PARAM_TEXT));
    //ACCESS TOKEN
    $settings->add(new admin_setting_configtext('enrol_mobbexpayment/accesstoken',
    'Mobbex Access Token',
    'Ingrese access token', '', PARAM_TEXT));
    //TEST MODE
    $settings->add(new admin_setting_configcheckbox('enrol_mobbexpayment/test',
    "Test Mode", '', 0));
    //GENERAL SETTINGS, can be removed
    $settings->add(new admin_setting_configcheckbox('enrol_mobbexpayment/mailstudents',
    get_string('mailstudents', 'enrol_mobbexpayment'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_mobbexpayment/mailteachers',
    get_string('mailteachers', 'enrol_mobbexpayment'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_mobbexpayment/mailadmins',
    get_string('mailadmins', 'enrol_mobbexpayment'), '', 0));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    // it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_mobbexpayment/expiredaction',
    get_string('expiredaction', 'enrol_mobbexpayment'), get_string('expiredaction_help', 'enrol_mobbexpayment'),
    ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    // Enrol instance defaults.
    $settings->add(new admin_setting_heading('enrol_mobbexpayment_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_mobbexpayment/status',
        get_string('status', 'enrol_mobbexpayment'), get_string('status_desc', 'enrol_mobbexpayment'),
        ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_mobbexpayment/cost', get_string('cost', 'enrol_mobbexpayment'),
    '', 0, PARAM_FLOAT, 4));

    $mobbexcurrencies = enrol_get_plugin('mobbexpayment')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_mobbexpayment/currency',
    get_string('currency', 'enrol_mobbexpayment'), '', 'ARS', $mobbexcurrencies));

    $settings->add(new admin_setting_configtext('enrol_mobbexpayment/maxenrolled',
        get_string('maxenrolled', 'enrol_mobbexpayment'), get_string('maxenrolled_help', 'enrol_mobbexpayment'), 0, PARAM_INT));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_mobbexpayment/roleid',
            get_string('defaultrole', 'enrol_mobbexpayment'),
            get_string('defaultrole_desc', 'enrol_mobbexpayment'),
            $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_mobbexpayment/enrolperiod',
        get_string('enrolperiod', 'enrol_mobbexpayment'), get_string('enrolperiod_desc', 'enrol_mobbexpayment'), 0));
}
