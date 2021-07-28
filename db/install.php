<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mobbexpayment
 * @category    upgrade
 * @copyright   2021 Mobbex
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_enrol_mobbexpayment_install() {
    // Enable the mobbexpayment payment gateway on installation. It still needs to be configured and enabled for accounts.
    $order = (!empty($CFG->paygw_plugins_sortorder)) ? explode(',', $CFG->paygw_plugins_sortorder) : [];
    set_config('paygw_plugins_sortorder', join(',', array_merge($order, ['mobbex'])));

    return true;
}
