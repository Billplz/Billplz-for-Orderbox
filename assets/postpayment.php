<?php

sleep(9);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/billplz.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config.php';


$bill_id = isset($_POST['id']) ? $_POST['id'] : exit('Bye');
$transId = $_GET['transId'];

$billplzDB = new BillplzDB;
$db = $billplzDB->check_id($bill_id, $transId);
$bill_status = $db['status'];
$redirectUrl = $db['redirect_url'];

if (!$bill_status) {
    exit('Already Mark As Paid');
}

$sellingCurrencyAmount = $_GET['sellingcamount'];
$accountingCurrencyAmount = $_GET['accountingcamount'];

global $config;

$billplz = new billplz();
$data = $billplz->check_bill($config['api_key'], $bill_id, $config['mode']);

if ($data['paid']) {
    $status = 'Y';
    srand((double) microtime() * 1000000);
    $rkey = rand();
    $checksum = generateChecksum($transId, $sellingCurrencyAmount, $accountingCurrencyAmount, $status, $rkey, $config['key']);

    $post_data = [
        'transid' => $transId,
        'status' => $status,
        'rkey' => $rkey,
        'checksum' => $checksum,
        'sellingamount' => $sellingCurrencyAmount,
        'accountingamount' => $accountingCurrencyAmount,
    ];


    foreach ($post_data as $key => $value) {
        $post_items[] = $key . '=' . $value;
    }
    $post_string = implode('&', $post_items);

    $curl_connection = curl_init($redirectUrl);
    curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);


    curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
    $result = curl_exec($curl_connection);
    curl_close($curl_connection);

    // Save to Database
    $billplzDB->save_status('paid', $bill_id);

    exit('Success');
} else {
    exit('Do Nothing');
}