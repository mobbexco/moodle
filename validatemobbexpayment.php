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
 * Listens for Instant Payment Notification from Mobbex
 *
 * This script waits for Payment notification from Mobbex,
 *
 * @package    enrol_Mobbexpayment
 * @copyright  2021 Mobbex
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);
require("../../config.php");
require_login();
require_once($CFG->libdir.'/enrollib.php');
$answer = 'wrong';
$status = required_param('status', PARAM_RAW);
$transactionId = required_param('transactionId', PARAM_RAW);
$refence = required_param('reference', PARAM_RAW);
$type = required_param('type', PARAM_RAW);
$transaction = $DB->get_record("enrol_mobbexpayment", array("memo" => $refence));
//error_log("  ------Entrasss courseid ".print_r($transaction,true), 3, "/var/www/html/moodle/enrol/mobbexpayment/my-errors-Status-Before.log");

if($transaction)
{
    //error_log("-Entra  ".$transaction->courseid, 3, "/var/www/html/moodle/enrol/mobbexpayment/my-errors-Status-Correct.log");
    $plugin = enrol_get_plugin('mobbexpayment');
    $plugininstance = $DB->get_record("enrol", array("courseid" => $transaction->courseid, "enrol" => "mobbexpayment"));
    if($plugininstance && $status == '200')
    {
        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }
        $plugin->enrol_user($plugininstance, $transaction->userid, $plugininstance->roleid, $timestart, $timeend);
        $transaction->status = "successful";
        $transaction->payment_type = $type;
        $DB->update_record("enrol_mobbexpayment", $transaction);
        //redirect to course page 
        $url = "$CFG->wwwroot/course/view.php?id=$transaction->courseid";
        redirect($url, get_string('paymentthanks', '', ''));
    }
    $url = "$CFG->wwwroot/course/view.php?id=$transaction->courseid";
    redirect($url, get_string('paymentsorry', '', ''));
}
$url = "$CFG->wwwroot";
redirect($url, get_string('paymentsorry', '', ''));
