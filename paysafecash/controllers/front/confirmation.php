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

        exec('echo "Confirm: Create Order" >> ' . getcwd() . '/modules/paysafecash/log.log');

        $query = 'SELECT `transaction_id` FROM `' . _DB_PREFIX_ . "paysafecashtransaction` WHERE `cart_id` = '".$cart->id."' ORDER BY `transaction_time` DESC LIMIT 1;";
        $sql_q = Db::getInstance()->executeS($query);
        $payment_id = $sql_q[0]["transaction_id"];
        $response = $pscpayment->retrievePayment($payment_id);
        $transaction_status = false;

        if ($response == false) {
            return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
        } else if (isset($response["object"])) {
            if ($response["status"] == "SUCCESS") {
                if (Order::getOrderByCartId((int)($cart->id)) == null) {
                    $transaction_status = true;
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
            }else{
                if (Order::getOrderByCartId((int)($cart->id)) == null) {
                    $transaction_status = true;
                    $message = "Payment: ";
                    $module_name = $this->module->displayName;
                    $payment_status = Configuration::get('PAYSAFECASH_OS_WAITING');
                    $secure_key = Context::getContext()->customer->secure_key;
                    $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);
                }
            }
        }

        if ($transaction_status && ($secure_key == $customer->secure_key)) {
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {
            Tools::redirect('index.php?controller=order');
        }

    }
}

