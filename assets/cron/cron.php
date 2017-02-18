<?php

// php -q /home/<username>/public_html/path/assets/cron/cron.php >/dev/null 2>&1

require_once __DIR__ . '/../../includes/billplz.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config.php';
global $config;

$billplzDB = new BillplzDB;

$bills_id = $billplzDB->get_all_id();
$billplz = new DeleteBill($config['api_key'], $config['mode']);
if (!empty($bills_id)) {
    foreach ($bills_id as $bill_id) {
        if ($billplz->prepare()->setInfo($bill_id)->process()->checkBill()) {
            $billplzDB->delete_id($bill_id);
        }
    }
}

class DeleteBill {

    var $api_key, $mode, $id, $objdelete, $objcheck;

    public function __construct($api_key, $mode) {
        
        $this->api_key = $api_key;
        $this->mode = $mode;
        $this->objdelete = new curlaction;
        $this->objcheck = new billplz;
    }

    public function prepare() {
        $this->objdelete->setAPI($this->api_key)->setAction('DELETE');
        return $this;
    }

    public function setInfo($id) {
        //this->id is saved for checkBill() function
        $this->id = $id;
        $this->objdelete->setURL($this->mode, $id);
        return $this;
    }

    public function process() {
        $this->objdelete->curl_action('');
        return $this;
    }

    public function checkBill() {
        $data = $this->objcheck->check_bill($this->api_key, $this->id, $this->mode);
        if (isset($data['state'])) {
            // Hidden dah buang. Paid tak boleh buang
            if ($data['state'] == 'hidden' || $data['state'] == 'paid') {
                // True maksudnya dah buang
                return true;
            }
            // False maknya tak buang
            return false;
        } else {
            return false;
        }
    }

}
