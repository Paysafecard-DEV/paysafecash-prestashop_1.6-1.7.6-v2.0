<?php

use phpseclib\Crypt\RSA;

class paysafecashConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        require_once(_PS_MODULE_DIR_ . $this->module->name . "/vendor/autoload.php");
        require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $message = null;

        $cart = new Cart((int)$cart_id);
        $currency_id = $cart->id_currency;
        $payment_status = Configuration::get('PAYSAFECASH_OS_PAID');

        $cart_id = Tools::getValue('cart_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        $message = null;

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
        $debugmode = Configuration::get('PAYSAFECASH_DEBUG');

        if ($testmode == "1") {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        $pscpayment = new PaysafecardCashController(Configuration::get('PAYSAFECASH_API_KEY'), $env);

        $query = 'SELECT `transaction_id` FROM `' . _DB_PREFIX_ . "paysafecashtransaction` WHERE `cart_id` = '".$cart->id."' ORDER BY `transaction_time` DESC LIMIT 1;";
        $sql_q = Db::getInstance()->executeS($query);
        $payment_id = $sql_q[0]["transaction_id"];
        $response = $pscpayment->retrievePayment($payment_id);
        $transaction_status = false;

        if ($debugmode == "1") {
            Logger::AddLog("paysafecash Confirmation: " .json_encode($response), 1);
        }

        if ($response == false) {
            return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
        } else if (isset($response["object"])) {
            if ($response["status"] == "SUCCESS") {

                $order->addOrderPayment($order->total_paid, null, $payment_id);

                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->setFieldsToUpdate(["transaction_id" => $payment_id]);
                $history->changeIdOrderState((int)Configuration::get('PAYSAFECASH_OS_PAID'), (int)$order->id);
                $history->add(true);

                return $this->display(__FILE__, 'module:paysafecash/views/templates/front/webhook.tpl');
            }
            if ($response["status"] == "REDIRECTED") {
                $transaction_status = true;
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

