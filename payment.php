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
 * Creates Mobbex checkout after ajax call from enrol page
 * using course and user data
 * Only logged users can enrol
 *
 * @package    enrol_mobbexpayment
 * @copyright  2021 Mobbex
 */

// Moodle global variables
require_once('../../config.php');
//Only logged user from here
require_login();
require_once($CFG->libdir.'/filelib.php');
use curl;
global $DB, $USER, $CFG, $_SESSION;

//Get variables from Mobbex configuration 
$plugin = enrol_get_plugin('mobbexpayment');
$apiKey = $plugin->get_config('apikey');
//Get params from enrol.php
$test_active = $plugin->get_config('test');
$accessToken = $plugin->get_config('accesstoken');
$courseid = $_SESSION['courseid'];
$currency = $_SESSION['currency'];
$description = $_SESSION['description'];
$receiptemail = required_param('receiptemail', PARAM_RAW);
$amount = required_param('amount', PARAM_RAW);
$tracking_ref = $USER->id."_".$courseid."_".time();// reference string is unique for every inscription (using time() function)

if (empty($apiKey) || empty($courseid) || empty($amount) || empty($currency) || empty($description) || empty($accessToken)) {
    //Fail if there is a missing param
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else {
    if($amount>0){
        //Set API configuration variables
        $headers = array(
            'cache-control: no-cache',
            'content-type: application/json',
            'x-api-key: ' . $apiKey,
            'x-access-token: ' . $accessToken,
        );
        //Set Costumer data
        //TODO: Add DNI
        $customer = [
            'name' => $USER->username,
            'email' => $receiptemail,
            'phone' => "",
        ];

        // Create data
        $data = array(
            'reference' => $tracking_ref,
            'currency' => 'ARS',
            'description' => $description,
            'test' => $test_active, 
            'return_url' => $CFG->wwwroot.'/enrol/mobbexpayment/validatemobbexpayment.php?reference='.$tracking_ref,//reference is used when Mobbex return from payment page to identify the transaction
            'webhook' => $CFG->wwwroot.'/enrol/mobbexpayment/webhook.php',
            'redirect' => 0,
            'total' => $amount,
            'customer' => $customer,
            'timeout' => 5,
        );
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mobbex.com/p/checkout",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo false;
        } else {
            $result = json_decode($response);	
            if ($result->result) {
                //If checkout created successful, then create Mobbex transaction in database.
                $data = new stdClass();
                $data->courseid = $courseid;
                $data->userid = $USER->id;
                $data->transactionid = $result->data->id;
                $data->memo = $tracking_ref;//Save reference as MEMO, its goin to be use when Mobbex confirm payment.
                $data->status = "created";
                $data->receiver_email = $receiptemail;
                $data->amount = $amount;
                $DB->insert_record("enrol_mobbexpayment", $data);
                //return Mobbex payment URL
                echo $result->data->url;
            } else {
                echo false;
            }
        }
    }elseif($amount==0)
    {
        //in case time prediod is set
        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }
        //if amount is zero(free)
        $plugininstance = $DB->get_record("enrol", array("courseid" => $courseid, "enrol" => "mobbexpayment"));
        $plugin->enrol_user($plugininstance, $USER->id, $plugininstance->roleid, $timestart, $timeend);
        //Save mobbex transaction as free 
        $data = new stdClass();
        $data->courseid = $courseid;
        $data->userid = $USER->id;
        $data->memo = $tracking_ref;
        $data->status = "success";
        $data->receiver_email = $receiptemail;
        $data->amount = 0;
        $transaction->payment_type = "free";
        $DB->insert_record("enrol_mobbexpayment", $data);
        $url = "$CFG->wwwroot/course/view.php?id=$transaction->courseid";
        redirect($url, get_string('paymentthanks', '', ''));
    }
    die;
}

