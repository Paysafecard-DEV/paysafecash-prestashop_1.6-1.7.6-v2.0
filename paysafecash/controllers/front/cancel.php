<?php

class paysafecashCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        $order_id = Order::getOrderByCartId((int) $cart->id);

        $this->displayError("Error");

        if ($order_id && ($secure_key == $customer->secure_key)) {
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {
            Tools::redirect('index.php?controller=order');
        }

    }

    protected function displayError($message, $description = false)
    {

        global $smarty;

        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        array_push($this->errors, $this->module->l($message), $description);

        $smarty->assign(array(
            'error_msg' => "Payment cancled by Customer",
        ));

        return $smarty->display(__FILE__, '/views/templates/front/error.tpl');
    }
}

