<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Automattic\WooCommerce\Client;

class AsapWoUpdate
{
    private static $filename;

    public function __construct()
    {
        self::$filename = plugin_dir_path(__FILE__) . '/../storage/log_data.txt';
    }

    public function perform_update()
    {
        global $wpdb, $r_message, $wc_credentials, $filename;

        $sap_settings = self::get_sap_settings($wpdb);
        if (!$sap_settings) {
            return;
        }

        $companydb = $sap_settings['companydb'];
        $username = $sap_settings['username'];
        $server_ip = $sap_settings['server_ip'];
        $port = $sap_settings['server_port'];
        $password = $sap_settings['password'];

        $base_url = "https://{$server_ip}:{$port}";

        $sessionID = self::establish_sap_connection($base_url, $companydb, $username, $password);
        if (!$sessionID) {
            return;
        }

        $wc_credentials = self::get_woocommerce_credentials($wpdb);
        if (!$wc_credentials) {
            $r_message = "Error establishing connection: You have not linked the WooCommerce API.";
            return;
        }

        $woocommerce = new Client(
            home_url(),
            $wc_credentials['consumer_key'],
            $wc_credentials['consumer_secret'],
            [
                'version' => 'wc/v3',
                'verify_ssl' => true,
            ]
        );

        if (!$woocommerce) {
            echo 'Error: $woocommerce is null';
            return;
        }

        $batchProcessor = new BatchProcessor($woocommerce, $base_url, $sessionID, $companydb);
        $batchProcessor->process_products();

        $r_message = "The inventory was updated.";
    }

    private static function establish_sap_connection($base_url, $companydb, $username, $password)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "$base_url/b1s/v1/Login",
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{"CompanyDB": "' . $companydb . '", "UserName": "' . $username . '", "Password": "' . $password . '"}',
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            )
        );

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (curl_errno($curl) || $http_status !== 200) {
            $r_message = "Error connecting to the server. Check your network connection and try again.";
            return null;
        }

        $obj = json_decode($response);

        return isset($obj->SessionId) ? $obj->SessionId : null;
    }

    private static function get_woocommerce_credentials($wpdb)
    {
        if (!isset($wpdb)) {
            return array();
        }

        $result = $wpdb->get_row("SELECT consumer_key, consumer_secret FROM {$wpdb->prefix}asapwo_api LIMIT 1", ARRAY_A);

        if ($result) {
            return array(
                'website' => home_url(),
                'consumer_key' => $result['consumer_key'],
                'consumer_secret' => $result['consumer_secret'],
            );
        } else {
            return array();
        }
    }

    private static function get_sap_settings($wpdb)
    {
        if ($wpdb === null) {
            return false;
        }
        $table_name = $wpdb->prefix . 'asapwo_settings';

        $query = "SELECT * FROM $table_name WHERE 1 = 1";
        $sap_settings = $wpdb->get_row($query, ARRAY_A);

        return $sap_settings;
    }

    public static function perform_curl_request($url, $sessionID, $companydb)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Prefer: odata.maxpagesize=0',
                    'Cookie: B1SESSION=' . $sessionID . '; CompanyDB=' . $companydb,
                ),
            )
        );

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            print_r($error_msg);
            return null;
        }

        curl_close($curl);

        return $response;
    }

    public static function log_to_file($sku, $stock, $price)
    {
        $message = "Product updated - SKU: $sku, Stock: $stock, Price: $price";
        file_put_contents(self::$filename, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

class BatchProcessor extends AsapWoUpdate
{
    private $woocommerce;
    private $base_url;
    private $sessionID;
    private $companydb;

    public function __construct($woocommerce, $base_url, $sessionID, $companydb)
    {
        $this->woocommerce = $woocommerce;
        $this->base_url = $base_url;
        $this->sessionID = $sessionID;
        $this->companydb = $companydb;
    }

    public function process_products()
    {
        $page = 1;
        $productsPerPage = 100;

        set_time_limit(0);

        do {
            $wc_products = $this->woocommerce->get('products', ['per_page' => $productsPerPage, 'page' => $page]);

            if (count($wc_products) === 0) {
                break;
            }

            foreach ($wc_products as $wc_product) {
                $sku = $wc_product->sku;
                $product_id = $wc_product->id;

                $url = "$this->base_url/b1s/v1/Items?\$select=ItemCode,ItemName,QuantityOnStock,QuantityOrderedByCustomers,UpdateDate,UpdateTime,ItemPrices,ItemWarehouseInfoCollection&\$filter=ItemCode%20eq%20'$sku'";

                $response = $this->perform_curl_request($url, $this->sessionID, $this->companydb);

                if (!$response) {
                    continue;
                }

                $response_json = json_decode($response);
                $productdata = $response_json->value;

                if (empty($productdata)) {
                    continue;
                }

                try {
                    $item_prices = $productdata[0]->ItemPrices;
                    $price = $item_prices[0]->Price;

                    $warehouses_array = $productdata[0]->ItemWarehouseInfoCollection;

                    $warehouses = array_column($warehouses_array, 'WarehouseCode');
                    $MCH_key = array_search('MCH', $warehouses);
                    $SHOWROOM_key = array_search('SHOWROOM', $warehouses);

                    $MCH = $warehouses_array[$MCH_key];
                    $SHOWROOM = $warehouses_array[$SHOWROOM_key];

                    $stock = $MCH->InStock + $SHOWROOM->InStock - ($MCH->Committed + $SHOWROOM->Committed);

                    $woo_product = wc_get_product($product_id);

                    if ($woo_product) {
                        $woo_product->set_stock_quantity($stock);
                        $woo_product->set_regular_price($price);
                        $woo_product->set_price($price);
                        $woo_product->save();

                        $this->log_to_file($sku, $stock, $price);
                    }
                } catch (Exception $e) {
                    $txt = "$sku, Error updating: " . $e->getMessage();
                    $this->log_to_file($txt);
                }
            }

            $page++;

            if (count($wc_products) === $productsPerPage) {
                sleep(2);
            }

        } while (count($wc_products) > 0);

        set_time_limit(30);
    }
}
