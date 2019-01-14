<?php
if(!defined('INITIALIZED'))
	exit;

require_once('./custom_scripts/paypal/config.php');
/*
 * PayPal IPN IPs (it can change in future)
 * https://ppmts.custhelp.com/app/answers/detail/a_id/92
 * search: notify.paypal.com (IPN delivery)
*/
if(!in_array($_SERVER['REMOTE_ADDR'], array('173.0.81.1', '173.0.81.33', '66.211.170.66'))) {
	echo 'wrong IP';
	exit;
}

$receiverMail = $_REQUEST['receiver_email']; // ots admin mail
$status = $_REQUEST['payment_status']; // payment status, we add only when is 'Completed'
$currency = $_REQUEST['mc_currency']; // money currency, like USD or EUR
$gross = $_REQUEST['mc_gross']; // amount of money, like: 10.00
$payerMail = $_REQUEST['payer_email']; // player mail
$accountID = $_REQUEST['custom']; // user account ID
$transactionID = $_REQUEST['txn_id']; // transaction ID

$coins = floor($gross * 120);

$logFile = 'custom_scripts/paypal/reported_ids/' . $transactionID . '.log';
$test = 'cache/paypal/ids/' . $transactionID . '.tmp';
file_put_contents($test, 'accountID:' . $accountID . ',mail:' . $payerMail . ',amount:' . $gross . ' ' . $currency . ',points:' . $coins);
if (!file_exists($logFile) && $status == 'Completed') {
	if ($receiverMail == $paypal['mail'] && $currency == $paypal['money_currency']) {
		$account = new Account($accountID);
		if ($account->isLoaded()) {
			if (file_put_contents($logFile, 'accountID:' . $accountID . ',mail:' . $payerMail . ',amount:' . $gross . ' ' . $currency . ',points:' . $coins) !== false) {
				$account->setPremiumPoints($account->getPremiumPoints() + $coins);
				$account->save();
			}
		}
		break;
	}
}
exit;
