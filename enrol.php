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
 * @package    enrol_mobbexpayment
 * @copyright  2021 Mobbex
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
defined('MOODLE_INTERNAL') || die();
global $CFG;
?>

<?php
    $_SESSION['description'] = $coursefullname;
    $_SESSION['courseid'] = $course->id;
    $_SESSION['currency'] = $instance->currency;              

    function get_mobbex_amount($cost, $currency, $reverse) {
        $nodecimalcurrencies = array("bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg",
                                    "rwf", "ugx", "vnd", "vuv", "xaf", "xof", "xpf");

        if (!$currency) {
            $currency = 'ARS';
        }
        if (in_array(strtolower($currency), $nodecimalcurrencies)) {
            return abs($cost);
        } else {
            if ($reverse) {
                return abs( (float) $cost / 100);
            } else {
                return abs( (float) $cost * 100);
            }
        }
    }
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js">
</script>

<style>
#region-main h2 { display:none; }
.enrolmenticons { display: none;}
</style>

<div align="center">
    <div class="mobbex-img">
        <img src="<?php echo $CFG->wwwroot; ?>/enrol/mobbexpayment/mobbex.jpg"></div>
        <p><?php print_string("paymentrequired") ?></p>
        <!-- <p><b><?php echo $instancename; ?></b></p> //-->
        <p><b><?php echo get_string("cost").": {$instance->currency} {$cost}"; ?></b></p>
        <div class="couponcode-wrap">
            <input type=text id="coupon"/>
            <button id="apply"><?php echo get_string("applycode", "enrol_mobbexpayment"); ?></button>
        </div>

        <form id="form_data_new" action="" method="post">
            <input id="form_data_new_data" type="hidden" name="data" value="" />
        </form>

        <div id="reload">
            <div id="new_coupon" style="margin-bottom:10px;"></div>
            <?php
                $couponid = 0;
                $dataa = optional_param('data', null, PARAM_RAW);
                if ( isset($dataa) ) {
                    $cost = $dataa;
                    $couponid = required_param('coupon_id', PARAM_RAW);
                }
                //$_SESSION['amount'] = get_mobbex_amount($cost, $_SESSION['currency'], false);

                echo "<p><b> Final Cost : $instance->currency $cost </b></p>";
            ?>

            <?php 
                $costvalue = str_replace(".", "", $cost);
                if ($costvalue == 000) {  ?>
                    <div id="amountequalzero">
                        <button id="card-button-zero">
                            Enrol Now
                        </button>
                    </div>
                    <br>
                    <script type="text/javascript">
                        $(document.body).on('click', '#card-button-zero' ,function(){
                            var cost = "<?php echo str_replace(".", "", $cost); ?>";
                            if (cost == 000) {
                            document.getElementById("mobbexformfree").submit();
                            }
                        });
                    </script>
            <?php } else { ?>

                <!-- placeholder for Elements -->
                <div id="amountgreaterzero">
                    <strong>
                    <div id="card-element"></div> <br>
                    <button id="card-button">
                        Submit Payment
                    </button>
                    <div id="transaction-status">
                        <center> Your transaction is processing. Please wait... </center>
                    </div>
                    </strong>
                </div>
            <?php } ?>

    <form id="mobbexformfree" action="<?php
        echo "$CFG->wwwroot/enrol/mobbexpayment/free_enrol.php"?>" method="post">
        <input type="hidden" name="coupon_id" value="<?php p($couponid) ?>" class="coupon_id" />
        <input type="hidden" name="cmd" value="_xclick" />
        <input type="hidden" name="charset" value="utf-8" />
        <input type="hidden" name="item_name" value="<?php p($coursefullname) ?>" />
        <input type="hidden" name="item_number" value="<?php p($courseshortname) ?>" />
        <input type="hidden" name="quantity" value="1" />
        <input type="hidden" name="on0" value="<?php print_string("user") ?>" />
        <input type="hidden" name="os0" value="<?php p($userfullname) ?>" />
        <input type="hidden" name="custom" value="<?php echo "{$USER->id}-{$course->id}-{$instance->id}" ?>" />
        <input type="hidden" name="currency_code" value="<?php p($instance->currency) ?>" />
        <input type="hidden" name="amount" value="<?php p($cost) ?>" />
        <input type="hidden" name="for_auction" value="false" />
        <input type="hidden" name="no_note" value="1" />
        <input type="hidden" name="no_shipping" value="1" />
        <input type="hidden" name="rm" value="2" />
        <input type="hidden" name="cbt" value="<?php print_string("continuetocourse") ?>" />
        <input type="hidden" name="first_name" value="<?php p($userfirstname) ?>" />
        <input type="hidden" name="last_name" value="<?php p($userlastname) ?>" />
        <input type="hidden" name="address" value="<?php p($useraddress) ?>" />
        <input type="hidden" name="city" value="<?php p($usercity) ?>" />
        <input type="hidden" name="email" value="<?php p($USER->email) ?>" />
        <input type="hidden" name="country" value="<?php p($USER->country) ?>" />
    </form>

</div>
</div>

<style>
.couponcode-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
}
.couponcode-wrap .couponcode-text{
    font-size:14px;
}
.couponcode-wrap input#coupon{
    margin: 0 6px;
}
div#transaction-status, div#transaction-status-zero {
    margin: 15px;
    background: antiquewhite;
    color: chocolate;
    display: none;
}
.CardField-input-wrapper{ overflow: inherit;} 
.coursebox .content .summary{width:100%}
button#apply, button#card-button, button#card-button-zero{
   color: #fff;
   background-color: #1177d1;
   border: 1px solid #1177d1;
   padding: 5px 10px;
   font-size: 13px;
}
input#coupon {
   border: 1px dashed #a2a2a2;
   padding: 3px 5px;
}
p{ text-align:left;}
.mobbex-img img{width:130px;}
body#page-enrol-index #region-main .generalbox.info{
 width: 100%;
 box-shadow: none;
}
body#page-enrol-index #region-main .generalbox .card a img{
   max-width: 458px;
   height: 300px;
   padding: 0;
   box-shadow: 0 0 10px #b0afaf;
}
#page-enrol-index .access-btn{
 display: none;
}
body#page-enrol-index #region-main .generalbox:last-of-type {
   width: 468px;
   padding-left: 2rem;
   padding-right: 2rem;
   margin: 0 auto;
   float: left;
   box-shadow: 0 0 10px #ccc;
   clear: both;
   padding-bottom:30px !Important;
}
#page-enrol-index #region-main-box .card-title {
   position: relative;
   line-height: 59px;
   font-size: 2rem;
   text-transform: capitalize;
}
.StripeElement {
    padding: 15px;
    border: 1px solid #e9ebec;
    background: #f9f9f9;
    box-shadow: 0 10px 6px -4px #d4d2d2;
}
.StripeElement input[placeholder], [placeholder], *[placeholder] {
    color: red !important;
}
@media (min-width: 200px) and (max-width: 700px) {
#region-main{
    padding:0;
}   z
.generalbox {
   width: 300px;} 
body#page-enrol-index #region-main .generalbox:last-of-type{
 width: 320px;
 margin: 0 auto;
 float: none;
} 
#page-enrol-index p{
 text-align: center;
} 
#apply{
 margin-top: 10px;
}
#coupon{
 margin-top:10px;
}
#page-enrol-index #region-main-box .card-title{
 text-align: center;
}
#page-enrol-index #region-main-box .card-title:before, #page-enrol-index #region-main-box .card-title:after{
 display: none;
}
.couponcode-wrap { display: block;
}
}
</style>