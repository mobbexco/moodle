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
 * Listens for Instant Payment Notification from Stripe
 *
 * This script waits for Payment notification from Stripe,
 * then double checks that data by sending it back to Stripe.
 * If Stripe verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    enrol_stripepayment
 * @copyright  2021 Mobbex
 */

require_once('../../config.php');
require_login();

global $DB, $USER, $CFG, $_SESSION;

$plugin = enrol_get_plugin('mobbexpayment');


$apiKey = $plugin->get_config('apikey');
$accessToken = $plugin->get_config('accesstoken');
$courseid = $_SESSION['courseid'];
$tracking_ref = $USER->id."_".$courseid;
$amount = $_SESSION['amount'];
$currency = $_SESSION['currency'];
$description = $_SESSION['description'];
$receiptemail = required_param('receiptemail', PARAM_RAW);

if (empty($secretkey) || empty($courseid) || empty($amount) || empty($currency) || empty($description) || empty($receiptemail)) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else {
    /// REST CALL
    $headers = array(
        'cache-control: no-cache',
        'content-type: application/json',
        'x-api-key: ' . $apiKey,
        'x-access-token: ' . $accessToken,
    );

    $customer = [
        'name' => $order->username(),
        'email' => $receiptemail,
        'phone' => "",
    ];

    // Create data
    $data = array(
        'reference' => $tracking_ref,
        'currency' => 'ARS',
        'description' => 'User # ' . $USER->id ." _ CourseId # ".$courseid,
        'test' => true, // TODO: Add to config !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        'return_url' => $CFG->wwwroot.'/enrol/mobbexpayment/validatemobbexpayment.php?reference='.$tracking_ref,
        'webhook' => $CFG->wwwroot.'/enrol/mobbexpayment/webhook.php',
        'redirect' => 0,
        'total' => round($order->getGrandTotal(), 2),
        'customer' => $customer,
        'timeout' => 5,
        'installments' => $this->getInstallments($products),
    );

    require_once('./curl.php');
    $curl = new curl;
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
        return false;
    } else {
        $result = json_decode($response['body']);	
        if ($result->result) {
            return true;
        } else {
            return false;
        }
    }
}

if (isset($intent->client_secret)) {
    echo $intent->client_secret;
}

die;