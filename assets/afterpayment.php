<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/billplz.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config.php';


$bill_id = isset($_GET['billplz']['id']) ? $_GET['billplz']['id'] : exit('Bye');
$transId = $_GET['transId'];

$billplzDB = new BillplzDB;
$db = $billplzDB->check_id($bill_id, $transId);
$bill_status = $db['status'];
$redirectUrl = $db['redirect_url'];

$sellingCurrencyAmount = $_GET['sellingcamount'];
$accountingCurrencyAmount = $_GET['accountingcamount'];

global $config;

if ($bill_status) {

    $billplz = new billplz();
    $data = $billplz->check_bill($config['api_key'], $bill_id, $config['mode']);

    if ($data['paid']) {
        // Save to Database
        $billplzDB->save_status('paid', $bill_id);
        $status = 'Y';
    } else {
        $status = 'N';
    }
} else if (!$bill_status) {
    exit('Already Mark As Paid');
}

srand((double) microtime() * 1000000);
$rkey = rand();
$checksum = generateChecksum($transId, $sellingCurrencyAmount, $accountingCurrencyAmount, $status, $rkey, $config['key']);

?>
<h1>PLEASE WAIT AND DON'T CLOSE THIS WINDOW</h1>
<form method='post' name = "billplz_payment_form" action = "<?php echo $redirectUrl; ?>" id="billplz_payment_form">
    <input type = "hidden" name = "transid" value = "<?php echo $transId; ?>">
    <input type = "hidden" name = "status" value = "<?php echo $status; ?>">
    <input type = "hidden" name = "rkey" value = "<?php echo $rkey; ?>">
    <input type = "hidden" name = "checksum" value = "<?php echo $checksum; ?>">
    <input type = "hidden" name = "sellingamount" value = "<?php echo $sellingCurrencyAmount; ?>">
    <input type = "hidden" name = "accountingamount" value = "<?php echo $accountingCurrencyAmount; ?>">

    <input type = "submit" value = "Click here to Continue"><BR>
    <script>document.billplz_payment_form.submit();</script>
</form>