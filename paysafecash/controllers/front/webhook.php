<?php

use phpseclib\Crypt\RSA;

class paysafecashWebhookModuleFrontController extends ModuleFrontController
{
    public $version = '2.0.0';
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

        $payment_str = file_get_contents("php://input");
        $cart_id = Tools::getValue('cart_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);
        $message = null;
        $order_id = Order::getOrderByCartId((int)$cart->id);
        $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');
        $debugmode = Configuration::get('PAYSAFECASH_DEBUG');
        $signatur_check = 0;
        if (isset(apache_request_headers()["Authorization"][2])) {
            $signature = str_replace('"', '', str_replace('signature="', '', explode(",", apache_request_headers()["Authorization"])[2]));
            $rsa = new RSA();
            $rsa->loadKey("-----BEGIN RSA PUBLIC KEY-----\n" . str_replace(" ", "", Configuration::get('PAYSAFECASH_WEBHOOK_KEY')) . "\n-----END RSA PUBLIC KEY-----");
            $pubkey = openssl_pkey_get_public($rsa->getPublicKey());
            $signatur_check = openssl_verify($payment_str, base64_decode($signature), $pubkey, OPENSSL_ALGO_SHA256);
        }else{
            $signature = null;
        }

        if ($signatur_check == 1) {
            if ($debugmode == "1") {
                Logger::AddLog("paysafecash WEBHOOK: Signature OK", 1);
            }
        } else {
            if ($debugmode == "1") {
                Logger::AddLog("paysafecash WEBHOOK: Signature Error", 1);
                Logger::AddLog("paysafecash WEBHOOK: Header->" . json_encode(apache_request_headers()), 1);
                Logger::AddLog("paysafecash WEBHOOK: Signaturer->" . $signature, 1);
                Logger::AddLog("paysafecash WEBHOOK: Key->" . Configuration::get('PAYSAFECASH_WEBHOOK_KEY'), 1);

            }
        }
        if(isset($pubkey)){
            openssl_free_key($pubkey);
        }

        if ($debugmode == "1") {
            Logger::AddLog("paysafecash WEBHOOK Content: " . json_encode($payment_str), 1);
        }

        $cart = new Cart((int)$cart_id);
        $currency_id = $cart->id_currency;

        $payment_str = json_decode($payment_str);
        $payment_id = $payment_str->data->mtid;

        $order_id = Order::getOrderByCartId((int)$cart->id);
        $order = new Order($order_id);

        $skip_validation = false;

        if (file_exists(getcwd() . "/modules/paysafecash/skipverification")) {
            $skip_validation = true;
            if ($debugmode == "1") {
                Logger::AddLog("paysafecash: webhook validation disabled", 1);
            }
        }

        if ($signatur_check == 1 || $skip_validation == true) {
            if ($payment_str->eventType == "PAYMENT_CAPTURED") {
                if ($debugmode == "1") {
                    Logger::AddLog("paysafecash WEBHOOK: PAYMENT_CAPTURED", 1);
                }
                if ($skip_validation) {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    if ($ip == "194.1.158.23") {
                        require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

                        $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');

                        if ($testmode == "1") {
                            $env = "TEST";
                        } else {
                            $env = "PRODUCTION";
                        }

                        $pscpayment = new PaysafecardCashController(Configuration::get('PAYSAFECASH_API_KEY'), $env);
                        $response = $pscpayment->retrievePayment($payment_id);
                        if ($debugmode == "1") {
                            Logger::AddLog(json_encode($response), 1);
                        }

                        if ($response == false) {
                            return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
                        } else if (isset($response["object"])) {
                            if ($response["status"] == "SUCCESS") {
                                $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";
                                $results = Db::getInstance()->execute($query);

                                if (Order::getOrderByCartId((int)($cart->id)) == null) {
                                    exec('echo "Confirm: Create Order" >> ' . getcwd() . '/modules/paysafecash/log.log');
                                    $message = "Payment: ";
                                    $module_name = $this->module->displayName;
                                    $payment_status = Configuration::get('PAYSAFECASH_OS_PAID');
                                    $secure_key = Context::getContext()->customer->secure_key;
                                    $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);
                                }

                                $order->addOrderPayment($order->total_paid, null, $payment_id);

                                $history = new OrderHistory();
                                $history->id_order = (int)$order->id;
                                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                                $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), (int)$order->id);
                                $history->add(true);

                                return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
                            }
                        }
                    } else {
                        if ($debugmode == "1") {
                            Logger::AddLog("paysafecash WEBHOOK: IP validation failed!", 1);
                        }
                    }

                } else {
                    $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `"._DB_PREFIX_."_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";
                    $results = Db::getInstance()->execute($query);
                    if ($debugmode == "1") {
                        Logger::AddLog("paysafecash Tansactions Update " . print_r($results, true), 1);
                    }
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                    $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), $order);
                    $history->add(true);
                    $history->save();

                    if ($debugmode == "1") {
                        Logger::AddLog("paysafecash WEBHOOK Order: " . json_encode($order), 1);
                    }
                    if ($debugmode == "1") {
                        Logger::AddLog("paysafecash WEBHOOK History: " . print_r($history, true), 1);
                    }
                }
            }
            if ($payment_str->eventType == "PAYMENT_EXPIRED") {
                if ($debugmode == "1") {
                    Logger::AddLog("paysafecash WEBHOOK: PAYMENT_EXPIRED", 1);
                }
                $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'EXPIRED' WHERE `"._DB_PREFIX_."_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";
                $results = Db::getInstance()->execute($query);
                $history = new OrderHistory();

                $query = 'SELECT `order_id` FROM `' . _DB_PREFIX_ . "paysafecashtransaction` WHERE `transaction_id` = '" . $payment_id . "' ORDER BY `transaction_time` DESC LIMIT 1;";
                $sql_q = Db::getInstance()->executeS($query);
                $order_id = $sql_q[0]["order_id"];
                if ($debugmode == "1") {
                    Logger::AddLog("paysafecash WEBHOOK: PAYMENT_EXPIRED ID:" . $order_id, 1);
                }

                $history->id_order = $order_id;
                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                $history->changeIdOrderState(6, $history->id_order);
                $history->add(true);
                $history->addWithemail();
                $history->save();
            }
        }

        $this->setTemplate('webhook.tpl');
    }
}

