# Billplz for Orderbox
Accept Payment using Billplz for Orderbox

# Server Requirements
* PHP 7.0 or higher (Tested with 7.0 & 7.1)
* MySQL/MariaDB Compatible for Database

# Other Requirements
* 32 Bit Key from OrderBox
* Billplz API Key
* Billplz Collection ID
* Database Name, Username & Password

# Installation

1. Download this Repo
2. Upload to working directory
3. Set the configuration in file config.php
4. On Orderbox Custom Payment Page:
  4.1. Set Gateway URL to: https://yourdomain/assets/paymentpage.php
  4.2. Gateway Name: Billplz (Cimb Clicks, Maybank2u, & FPX)
  4.3. Enable Gateway for ALL
  4.4. Checksum Algorithm: MD5
5. Set up Cron Jobs
  5.1. Set to Once A Month: 
  ```
  php -q /home/<username>/public_html/path/assets/cron/cron.php >/dev/null 2>&1
  ```
# Donation

Please support this project by donation: www.wanzul.net/donate


