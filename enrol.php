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
 * This script allow courses enrolment using mobbexpayment
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
    echo filter_input(INPUT_GET, 'status', FILTER_SANITIZE_URL);
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

        <form id="form_data_new" action="" method="post">
            <input id="form_data_new_data" type="hidden" name="data" value="" />
        </form>

    <?php if($_SERVER['HTTPS']!="on") { ?>
        <div id="reload">
            <div id="new_coupon" style="margin-bottom:10px;"></div>
            <?php
                $couponid = 0;
                $dataa = optional_param('data', null, PARAM_RAW);
                if ( isset($dataa) ) {
                    $cost = $dataa;
                    $couponid = required_param('coupon_id', PARAM_RAW);
                }
                echo "<p><b> Final Cost : $instance->currency $cost </b></p>";
            ?>

            <?php 
                $costvalue = str_replace(".", "", $cost);
                if ($costvalue == 000) {  ?>
                    <div id="amountequalzeromobbex">
                        <strong>
                        <div id="card-element"></div> <br>
                        <button id="card-button-mobbex">
                            Submit Payment
                        </button>
                        <div id="transaction-status-mobbex">
                            <center> Your transaction is processing. Please wait... </center>
                        </div>
                        </strong>
                    </div>
                    <br>
            <?php } else { ?>

                <!-- placeholder for Elements -->
                <div id="amountgreaterzeromobbex">
                    <strong>
                    <div id="card-element"></div> <br>
                    <button id="card-button-mobbex">
                        Submit Payment
                    </button>
                    <div id="transaction-status-mobbex">
                        <center> Your transaction is processing. Please wait... </center>
                    </div>
                    </strong>
                </div>
            <?php } ?>
        
        
        <script type="text/javascript">
            var style = {
                base: {
                    fontSize:'15px',
                    color:'#000',
                    '::placeholder': {
                    color: '#000',
                    }
                },
            };

            //var cardElement = document.createElement('card', {style: style});
            //cardElement.mount('#card-element');
            var cardholderName = "<?php echo $userfullname; ?>";
            var emailId = "<?php echo $USER->email; ?>";
            var amount = "<?php echo $cost; ?>";
            var cardButton = document.getElementById('card-button-mobbex');
            var status = 0;
            var postal = null;

            cardButton.addEventListener('click', function(event) {
                if (event.error) {
                    status = 0;
                } else {
                    status = 1;
                }

                if (status == 0 || status == null) {
                    $("#transaction-status-mobbex").css("display", "none");
                    console.info("Error event");
                } else {
                    $("#transaction-status-mobbex").css("display", "block");
                    $("#card-button-mobbex").attr("disabled", true);
                
                    $.ajax({
                        url: "<?php echo $CFG->wwwroot; ?>/enrol/mobbexpayment/payment.php",
                        type: 'GET',
                        data: {
                            'receiptemail' : emailId,
                            'amount' : amount,
                        },
                    })
                    .done(function( data ) {
                        window.location.href = data;
                    })
                    .fail(function() {
                        console.error("Error in ajax call");
                    });
                
                }
            });

        </script>
    <?php } ?>
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
div#transaction-status-mobbex, div#transaction-status-mobbex-zero {
    margin: 15px;
    background: antiquewhite;
    color: chocolate;
    display: none;
}
.CardField-input-wrapper{ overflow: inherit;} 
.coursebox .content .summary{width:100%}
button#apply, button#card-button, button#card-button-zero-mobbex{
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
.MobbexElement {
    padding: 15px;
    border: 1px solid #e9ebec;
    background: #f9f9f9;
    box-shadow: 0 10px 6px -4px #d4d2d2;
}
.MobbexElement input[placeholder], [placeholder], *[placeholder] {
    color: red !important;
}
@media (min-width: 200px) and (max-width: 750px) {
#region-main{
    padding:0;
}  
}
</style>