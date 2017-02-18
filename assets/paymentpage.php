<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/billplz.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config.php';

class OrderBoxBillplz {

    private $data = [];

    function __construct() {

        global $config;

        if (!isset($_GET['paymenttypeid'])) {
            exit('Payment ID does not set');
        }

        if ($_GET['resellerCurrency'] === 'MYR' || $_GET['resellerCurrency'] === 'RM') {
            // MYR or RM is only accepted currency
        } else {
            exit('Not supported currency');
        }

        $_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);

        $this->data = [
            'transId' => $_GET['transid'],
            'description' => $_GET['description'],
            'sellingCurrencyAmount' => $_GET['sellingcurrencyamount'], //This refers to the amount of transaction in your Selling Currency
            'custEmail' => $_GET["emailAddr"],
            'custName' => $_GET["name"],
            'key' => $config['key'],
            'api_key' => $config['api_key'],
            'collection_id' => $config['collection_id'],
            'mode' => $config['mode'],
            'callbackURL' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/postpayment.php" . "?transId=" . $_GET['transid'] . "&sellingcamount=" . $_GET['sellingcurrencyamount'] . "&accountingcamount=" . $_GET['accountingcurrencyamount'],
            'redirectURL' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/afterpayment.php" . "?transId=" . $_GET['transid'] . "&sellingcamount=" . $_GET['sellingcurrencyamount'] . "&accountingcamount=" . $_GET['accountingcurrencyamount'],
        ];
    }

    function verifyChecksum() {
        //$paymentTypeId, $transId, $userId, $userType, $transactionType, $invoiceIds, $debitNoteIds, $description, $sellingCurrencyAmount, $accountingCurrencyAmount, $key, $checksum
        if (verifyChecksum($_GET['paymenttypeid'], $_GET['transid'], $_GET['userid'], $_GET['usertype'], $_GET['transactiontype'], $_GET['invoiceids'], $_GET['debitnoteids'], $this->data['description'], $_GET['sellingcurrencyamount'], $_GET['accountingcurrencyamount'], $this->data['key'], $_GET['checksum'])) {
            $this->generateBills();
        } else {
            exit('Invalid Checksum');
        }
    }

    private function generateBills() {
        $obj = new billplz;
        $obj
                ->setCollection($this->data['collection_id'])
                ->setEmail($this->data['custEmail'])
                ->setName($this->data['custName'])
                ->setAmount($this->data['sellingCurrencyAmount'])
                ->setDeliver(false)
                ->setDescription(!empty($this->data['description']) ? $this->data['description'] : 'Transaction ID: ' . $this->data['transId'])
                ->setPassbackURL($this->data['redirectURL'], $this->data['callbackURL'])
                ->create_bill($this->data['api_key'], $this->data['mode']);
        $this->data['bill_id'] = $obj->getID();
        $this->data['url'] = $obj->getURL();
    }

    function goToURL() {
        $billplzDB = new BillplzDB;
        $billplzDB->save_id($this->data['bill_id'], $this->data['transId'], $_GET['redirecturl']);
        header("Location: " . $this->data['url']);
    }

}

$OrderBoxBillplz = new OrderBoxBillplz;
$OrderBoxBillplz->verifyChecksum();
$OrderBoxBillplz->goToURL();

