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
 * mobbex enrolment plugin version specification.
 *
 * @package    enrol_mobbexpayment
 * @copyright  Mobbex
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2021071401;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2013051401;        // Requires this Moodle version.
$plugin->component = 'enrol_mobbexpayment';    // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '0.1 (Build: 2020013100)';
$plugin->cron      = 60;
