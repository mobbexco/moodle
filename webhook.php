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
 * Listens for  Payment Notification from Mobbex
 * This script waits for Payment notification from Mobbex,
 * 
 * @package    enrol_mobbexpayment
 * @copyright  2021 Mobbex
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);
//Add Moodle config files
require_once('../../config.php');
require_once($CFG->libdir.'/enrollib.php');
//Retrive params from POST call
$res = [];
parse_str(file_get_contents('php://input'), $res);//Use it for php older versions
$body_data = json_decode($res['body']);
if($body_data){
    //obtain useful info
    $result_data =$body_data->data;
    $status_code = $result_data->payment->status->code;
    $reference = $result_data->payment->reference;
    //get mobbex trasanction from database using payment reference/memo
    $transaction = $DB->get_record("enrol_mobbexpayment", array("memo" => $reference));
    if($transaction){
        $plugin = enrol_get_plugin('mobbexpayment');//get mobbexpayment instance
        $plugininstance = $DB->get_record("enrol", array("courseid" => $transaction->courseid, "enrol" => "mobbexpayment"));
        if($plugininstance &&  ($status_code == 4 || $status_code >= 200 && $status_code < 400) )
        {
            //in case time prediod is set
            if ($plugininstance->enrolperiod) {
                $timestart = time();
                $timeend   = $timestart + $plugininstance->enrolperiod;
            } else {
                $timestart = 0;
                $timeend   = 0;
            }
            //payment successful then enrol user
            $plugin->enrol_user($plugininstance, $transaction->userid, $plugininstance->roleid, $timestart, $timeend);
            $transaction->status = "successful";
        }elseif($plugininstance &&  ($status_code == '2' || $status_code == '3' || $status_code == '100')){
            if (!empty($result_data->payment->operation->type) && $result_data->payment->operation->type === 'payment.2-step' && $status_code == 3) {
                $transaction->status = "authorized";
            } else {
                $transaction->status = "on-hold";
            }
            
        }else{
            //payment went wrong
            $transaction->status = "failed";
        }
        //Set transaction new state
        $DB->update_record("enrol_mobbexpayment", $transaction);
    }
}

return true;