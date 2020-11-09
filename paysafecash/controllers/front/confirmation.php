<?php

use phpseclib\Crypt\RSA;

class paysafecashConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }
        if (Tools::getValue('action') == "error") {
            //Tools::redirect('index.php?controller=order');
        }

        require_once(_PS_MODULE_DIR_ . $this->module->name . "/vendor/autoload.php");
        require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $payment_id = Tools::getValue('payment_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);
        $message = null;


        $order_id = Order::getOrderByCartId((int)$cart->id);
        $order = new Order($order_id);
        $cart = new Cart((int)$cart_id);
        $currency_id = $cart->id_currency;
        $payment_status = Configuration::get('PAYSAFECASH_OS_PAID');

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

        $rsa = new RSA();
        $rsa->loadKey("-----BEGIN RSA PUBLIC KEY-----\n" . str_replace(" ", "", Configuration::get('PAYSAFECASH_WEBHOOK_KEY')) . "\n-----END RSA PUBLIC KEY-----");
        $pubkey = openssl_pkey_get_public($rsa->getPublicKey());
        $signatur_check = openssl_verify($payment_str, base64_decode($signature), $pubkey, OPENSSL_ALGO_SHA256);

        openssl_free_key($pubkey);

        $cart = new Cart((int)$cart_id);
        $currency_id = $cart->id_currency;

        $payment_str = json_decode($payment_str);
        $payment_id = $payment_str->data->mtid;
        $order_id = Order::getOrderByCartId((int)$cart->id);
        $order = new Order($order_id);

        $skip_validation = false;

        if (file_exists(getcwd() . "/modules/paysafecash/skipverification")) {
            $skip_validation = true;
        }

        $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');

        if ($testmode == "1") {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        $pscpayment = new PaysafecardCashController(Configuration::get('PAYSAFECASH_API_KEY'), $env);

        if ($signatur_check == 1 || $skip_validation == true) {
            if ($payment_str->eventType == "PAYMENT_CAPTURED") {
                if ($skip_validation) {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    if ($ip == "194.1.158.23") {
                        $response = $pscpayment->retrievePayment($payment_id);

                        if ($response == false) {
                            return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
                        } else if (isset($response["object"])) {
                            if ($response["status"] == "SUCCESS") {
                                $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";
                                Db::getInstance()->execute($query);

                                if (Order::getOrderByCartId((int)($cart->id)) == null) {

                                    $message = "Payment: ";
                                    $module_name = $this->module->displayName;
                                    $payment_status = Configuration::get('PAYSAFECASH_OS_PAID');
                                    $secure_key = Context::getContext()->customer->secure_key;
                                    $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);
                                } else {

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
                    }

                } else {
                    $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'SUCCESS' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";
                    $results = Db::getInstance()->execute($query);
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                    $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), $history->id_order);
                    $history->add(true);
                    $history->save();
                }
            }
            if ($payment_str->eventType == "PAYMENT_EXPIRED") {
                $query = 'UPDATE `' . _DB_PREFIX_ . "paysafecashtransaction` SET `status` = 'EXPIRED' WHERE `prstshp_paysafecashtransaction`.`transaction_id` = '" . $payment_id . "';";;
                $results = Db::getInstance()->execute($query);
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_EXPIRED'), $history->id_order);
                $history->add(true);
                $history->save();
            }
        }

        if ($order_id && ($secure_key == $customer->secure_key)) {
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {
            Tools::redirect('index.php?controller=order');
        }

    }
}

