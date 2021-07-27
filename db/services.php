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
 * This file registers the plugin's external functions.
 *
 * @package   mobbexpayment
 * @copyright 2021 Mobbex
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

$functions = array(
    "mobbexpayment_change_status" => array(
        "classname"   => "mobbex_services",
        "methodname"  => "change_status",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Change the status of the enrollment.",
        "type"        => "write"
    ),

    "mobbexpayment_enroll" => array(
        "classname"   => "mobbex_services",
        "methodname"  => "enroll_user",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Search for services that contain the specified event.",
        "type"        => "read"
    ),

);