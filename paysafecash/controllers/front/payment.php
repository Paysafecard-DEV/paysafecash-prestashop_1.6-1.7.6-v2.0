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

class paysafecashPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'isoCode' => $this->context->language->iso_code,
            'chequeName' => $this->module->chequeName,
            'chequeAddress' => Tools::nl2br($this->module->address),
            'this_path' => $this->module->getPathUri(),
            'this_path_cheque' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));

        $this->setTemplate('payment_execution.tpl');
    }

    public function postProcess()
    {

        require_once(_PS_MODULE_DIR_ . $this->module->name . "/libs/PaymentClass.php");

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $payment_id = Tools::getValue('payment_id');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);
        $message = null;

        //$this->setTemplate('payment_execution.tpl');
        //return $this->display(__FILE__, 'views/templates/hook/payment_execution.tpl');
    }

    protected function displayError($message, $description = false)
    {

        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        array_push($this->errors, $this->module->l($message), $description);

        //return $this->setTemplate('module:paysafecash/views/templates/front/error.tpl');
    }

}
