<?php

use phpseclib\Crypt\RSA;

class paysafecashWebhookModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function postProcess()
    {
        require_once(_PS_MODULE_DIR_ . $this->module->name . "/vendor/autoload.php");

        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }
        if (Tools::getValue('action') == "error") {
            Tools::redirect('index.php?controller=order');
        }
        if (!function_exists('apache_request_headers')) {
            function apache_request_headers()
            {
                $headers = array();
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }

        $signature = str_replace('"', '', str_replace('signature="', '', explode(",", apache_request_headers()["Authorization"])[2]));
        $payment_str = file_get_contents("php://input");
        $cart_id = Tools::getValue('cart_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        $message = null;

        $order_id = Order::getOrderByCartId((int)$cart->id);

        exec('echo "Header: '.print_r(apache_request_headers(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
        exec('echo "Signature: '.print_r($signature, true).'" >> '.getcwd().'/modules/paysafecash/log.log');

        $rsa = new RSA();
        $rsa->loadKey("-----BEGIN RSA PUBLIC KEY-----\n" . str_replace(" ", "", Configuration::get('PAYSAFECASH_WEBHOOK_KEY')) . "\n-----END RSA PUBLIC KEY-----");
        $pubkey = openssl_pkey_get_public($rsa->getPublicKey());
        $signatur_check = openssl_verify($payment_str, base64_decode($signature), $pubkey, OPENSSL_ALGO_SHA256);

        exec('echo "RSA: '.print_r($rsa->getPublicKey(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
        exec('echo "Webhook: '.print_r(Configuration::get('PAYSAFECASH_WEBHOOK_KEY'), true).'" >> '.getcwd().'/modules/paysafecash/log.log');

        openssl_free_key($pubkey);

        $cart = new Cart((int)$cart_id);
        $currency_id = $cart->id_currency;

        $payment_str = json_decode($payment_str);
        $payment_id = $payment_str->data->mtid;

        exec('echo "Signature: '.print_r($payment_str, true).'" >> '.getcwd().'/modules/paysafecash/log.log');
        exec('echo "API: '.print_r(Configuration::get('PAYSAFECASH_API_KEY'), true).'" >> '.getcwd().'/modules/paysafecash/log.log');


        //$order = Order::getByCartId($cart_id);

        $order_id = Order::getOrderByCartId((int) $cart->id);
        $order = new Order($order_id);

        $skip_validation = false;

        if(file_exists(getcwd()."/modules/paysafecash/skipverification")){
            exec('echo "SKIP Verification" >> '.getcwd().'/modules/paysafecash/log.log');
            $skip_validation = true;
        }else{
            exec('echo "Start normal Verification" >> '.getcwd().'/modules/paysafecash/log.log');
        }



        if ($signatur_check == 1 || $skip_validation == true) {
            if ($payment_str->eventType == "PAYMENT_CAPTURED") {
                if($skip_validation){
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    if($ip == "194.1.158.23"){
                        exec('echo "Payment start capture without verification: '.print_r($payment_str, true).'" >> '.getcwd().'/modules/paysafecash/log.log');
                        require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

                        $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');

                        if ($testmode == "1") {
                            $env = "TEST";
                        } else {
                            $env = "PRODUCTION";
                        }

                        $pscpayment = new PaysafecardCashController(Configuration::get('PAYSAFECASH_API_KEY'), $env);
                        $response      = $pscpayment->retrievePayment( $payment_id );

                        if ( $response == false ) {
                            return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
                        } else if ( isset( $response["object"] ) ) {
                            if ( $response["status"] == "SUCCESS" ) {
                                $query = 'UPDATE `'._DB_PREFIX_."paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '".$payment_id."';";
                                exec('echo "QUERY: '.base64_encode(print_r($query, true)).'" >> '.getcwd().'/modules/paysafecash/log.log');
                                $results = Db::getInstance()->execute($query);
                                exec('echo "Payment Captured without verification" >> '.getcwd().'/modules/paysafecash/log.log');
                                exec('echo "ORDER ID: '.(int)$order->id.'" >> '.getcwd().'/modules/paysafecash/log.log');
                                exec('echo "ORDER: '.print_r($order, true).'" >> '.getcwd().'/modules/paysafecash/log.log');

                                if (Order::getOrderByCartId((int)($cart->id)) == null) {
                                    exec('echo "Confirm: Create Order" >> ' . getcwd() . '/modules/paysafecash/log.log');
                                    $message = "Payment: ";
                                    $module_name = $this->module->displayName;
                                    $payment_status = Configuration::get('PAYSAFECASH_OS_PAID');
                                    $secure_key = Context::getContext()->customer->secure_key;
                                    $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);
                                } else {
                                    exec('echo "Confirm: order" >> ' . getcwd() . '/modules/paysafecash/log.log');
                                }

                                $order->addOrderPayment($order->total_paid, null, $payment_id);

                                $history = new OrderHistory();
                                $history->id_order = (int)$order->id;
                                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                                $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), (int)$order->id);
                                $history->add(true);


                                exec('echo "HISTORY: '.print_r($history->save(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
                                return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
                            }
                        }
                    }

                }else{
                    $query = 'UPDATE `'._DB_PREFIX_."paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '".$payment_id."';";
                    exec('echo "QUERY: '.base64_encode(print_r($query, true)).'" >> '.getcwd().'/modules/paysafecash/log.log');
                    $results = Db::getInstance()->execute($query);
                    exec('echo "Payment Captured: '.print_r($payment_str, true).'" >> '.getcwd().'/modules/paysafecash/log.log');
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                    $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), $history->id_order);
                    $history->add(true);
                    $history->save();
                }
            }if ($payment_str->eventType == "PAYMENT_EXPIRED") {
                $query = 'UPDATE `'._DB_PREFIX_."paysafecashtransaction` SET `status` = 'EXPIRED' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '".$payment_id."';";
                exec('echo "QUERY: '.base64_encode(print_r($query, true)).'" >> '.getcwd().'/modules/paysafecash/log.log');
                $results = Db::getInstance()->execute($query);
                exec('echo "Payment Captured: '.print_r($payment_str, true).'" >> '.getcwd().'/modules/paysafecash/log.log');
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_EXPIRED'), $history->id_order);
                $history->add(true);
                $history->save();
            }
        }
        return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
    }
}

