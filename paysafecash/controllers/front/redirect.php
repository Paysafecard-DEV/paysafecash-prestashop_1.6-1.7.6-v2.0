<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class paysafecashRedirectModuleFrontController extends ModuleFrontController
{
    public $version = '2.1.0';
    public $ssl = true;
    public $display_column_left = false;

    public function postProcess()
    {
        if (Tools::getValue('action') == 'error') {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {
            $this->context->smarty->assign(array(
                'cart_id' => Context::getContext()->cart->id,
                'secure_key' => Context::getContext()->customer->secure_key,
            ));

            require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

            $cart = $this->context->cart;
            $currency = new Currency($cart->id_currency);
            $address = new Address($this->context->cart->id_address_invoice);
            $customer = new Customer($address->id_customer);
            $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');
            $debugmode = Configuration::get('PAYSAFECASH_TEST_MODE');

            if ($testmode == "1") {
                $env = "TEST";
            } else {
                $env = "PRODUCTION";
            }

            $pscpayment = new PaysafecardCashController(Configuration::get('PAYSAFECASH_API_KEY'), $env);
            $success_url = Context::getContext()->link->getModuleLink(
                'paysafecash',
                'confirmation',
                array('cart_id' => $cart->id,
                    'secure_key' => Context::getContext()->customer->secure_key),
                null,
                null,
                Configuration::get('PS_SHOP_DEFAULT')
            );
            $failure_url = Context::getContext()->link->getModuleLink(
                'paysafecash',
                'cancel',
                array('cart_id' => $cart->id,
                    'secure_key' => Context::getContext()->customer->secure_key,
                    'action' => 'error'),
                null,
                null,
                Configuration::get('PS_SHOP_DEFAULT')
            );
            $notification_url = Context::getContext()->link->getModuleLink(
                'paysafecash',
                'webhook',
                array('cart_id' => $cart->id,
                    'secure_key' => Context::getContext()->customer->secure_key),
                false,
                false,
                Configuration::get('PS_SHOP_DEFAULT')
            );
            $notification_url = str_replace("http", "https", $notification_url);

            $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            $time_limit = 4000;

            if (Configuration::get('PAYSAFECASH_DATA_TAKEOVER_MODE') == true) {
                Logger::AddLog(json_encode($this->context->cart), 1);
                $customer_data = ["first_name" => $address->firstname,
                    "last_name" => $address->lastname,
                    "address1" => $address->address1,
                    "postcode" => $address->postcode,
                    "city" => $address->city,
                    "phone_number" => $address->phone,
                    "email" => $customer->email,
                ];
            } else {
                $customer_data = array();
            }

            $ordertotal = $cart->getOrderTotal(true, Cart::BOTH);

            if ($ordertotal >= 1000) {
                $this->context->smarty->assign(array(
                    'error_msg' => $this->l("The amount is too high, please lower your cart amount to 1000.00 max."),
                ));
                return $this->setTemplate('module:paysafecash/views/templates/front/redirect.tpl');
            }
            $response = $pscpayment->initiatePayment($ordertotal, $currency->iso_code, md5(Context::getContext()->customer->email), $ip, $success_url, $failure_url, $notification_url, $customer_data, $time_limit, $correlation_id = "", $country_restriction = "", $kyc_restriction = "", $min_age = "", $shop_id = "Presta: " . _PS_VERSION_ . " | " . $this->version, Configuration::get('PAYSAFECASH_SUBMERCHANT_ID'));

            if ($debugmode == "1") {
                Logger::AddLog("Prestashop IniLog: ". json_encode($pscpayment->getError()), 1);
            }

            if (isset($response["object"])) {

                $this->context->smarty->assign(array(
                    'redirect_url' => $response["redirect"]['auth_url']
                ));

                $message = "Payment: ";
                $module_name = $this->module->displayName;
                $payment_status = Configuration::get('PAYSAFECASH_OS_WAITING');
                $secure_key = Context::getContext()->customer->secure_key;
                $this->module->validateOrder((int)$cart->id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), (int)$currency->id, false, $secure_key);
                $order_id = Order::getOrderByCartId((int)$cart->id);
                $query = 'INSERT INTO `' . _DB_PREFIX_ . "paysafecashtransaction` (`transaction_id`, `transaction_time`, `order_id`, `cart_id`, `status`) VALUES ( '" . $response["id"] . "', '" . $cart->date_upd . "', '" . $order_id . "', '" . $cart->id . "', '" . $response["status"] . "'); ";
                Db::getInstance()->Execute($query);

                Tools::redirect($response["redirect"]['auth_url']);

            } else {
                $this->context->smarty->assign(array(
                    'error_msg' => "Transaction could not be initiated due to connection problems. If the problem persists, please contact our support.",
                ));
            }

            return $this->setTemplate('module:paysafecash/views/templates/front/redirect.tpl');

        }
    }

    protected function displayError($message, $description = false)
    {

        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('module:paysafecash/views/templates/front/error.tpl');
    }
}