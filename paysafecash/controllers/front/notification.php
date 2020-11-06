<?php

class paysafecashNotificationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $payment_id = Tools::getValue('payment_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);
        $payment_status = Configuration::get('PS_OS_PAYMENT'); // Default value for a payment that succeed.
        $message = null; // You can add a comment directly into the order so the merchant will see it in the BO.
        $module_name = $this->module->displayName;
        $currency_id = (int)Context::getContext()->currency->id;

        $order_id = Order::getOrderByCartId((int)$cart->id);



    }
}
