<?php

require_once __DIR__ . '/../config.php';

class BillplzDB {

    private $servername, $dbname, $username, $password, $conn;

    function __construct() {
        global $config;
        $this->servername = $config['db_server'];
        $this->dbname = $config['db_name'];
        $this->username = $config['db_username'];
        $this->password = $config['db_password'];
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        $this->check_table();
    }

    private function check_table() {
        $sql = "SELECT bill_id FROM payment WHERE 1";
        $result = $this->conn->query($sql);

        if (empty($result)) {
            $sql = "CREATE TABLE payment (
                    bill_id VARCHAR(30) NOT NULL,
                    transid VARCHAR(200) NOT NULL,
                    status VARCHAR (10) NOT NULL,
                    redirect_url VARCHAR(200) NOT NULL,
                    PRIMARY KEY  (bill_id)
                )";
            $this->conn->query($sql);
        }
    }

    function save_id($bill_id, $transId, $redirect_url) {
        $sql = "INSERT INTO payment (bill_id, transid, status, redirect_url) VALUES ('" . $bill_id . "', '" . $transId . "', 'unpaid', '" . $redirect_url . "')";

        $this->conn->query($sql);
        $this->conn->close();
    }

    function check_id($bill_id, $transId): array {
        $sql = "SELECT bill_id, transid, status, redirect_url FROM payment WHERE bill_id='" . $bill_id . "' AND transid='" . $transId . "'";
        $result = $this->conn->query($sql);

        // Status false mean no need to update to the OrderBox
        $status = ['status' => false];

        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'unpaid') {
                    $status['status'] = true;
                } else {
                    $this->conn->close();
                }
                $status['redirect_url'] = $row['redirect_url'];
                break;
            }
        } else {
            exit('Bill ID Not Found');
            $this->conn->close();
        }
        /*
         *  [ 'status' => true
         *    'redirect_url' => 'http'
         *  ]
         */
        return $status;
    }

    public function save_status($status, $bill_id) {
        $sql = "UPDATE payment SET status='" . $status . "' WHERE bill_id='" . $bill_id . "'";
        $this->conn->query($sql);
        $this->conn->close();
    }

    public function get_all_id(): array {
        $sql = "SELECT bill_id FROM payment";
        $result = $this->conn->query($sql);
        $bill_array = [];
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                $bill_array[] = $row['bill_id'];
            }
        } else {
            $this->conn->close();
        }
        return $bill_array;
    }

    public function delete_id($bill_id) {
        $sql = "DELETE FROM payment WHERE bill_id = '" . $bill_id . "'";
        $this->conn->query($sql);
    }

}
