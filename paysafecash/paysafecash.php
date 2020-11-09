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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class paysafecash extends PaymentModule
{

    public function __construct()
    {
        $this->name = 'paysafecash';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        $this->author = 'Prepaid Services Company Ltd.';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Paysafecash');
        $this->description = $this->l('Paysafecash is a cash payment option. Generate a QR/barcode and pay at a nearby shop.More information and our payment points can be found at www.paysafecash.com');
        $this->confirmUninstall = $this->l('Do you really want to remove this application?');

        $this->limited_countries = array('LU','ES', 'CH', 'DK', 'PL', 'IE', 'RO', 'BG', 'BE', 'HR', 'LV', 'AT', 'SI', 'NL', 'SK', 'CZ', 'FR', 'MT', 'HI', 'IT', 'PT', 'CA', 'DE' );

        $this->limited_currencies = array('EUR', 'CHF', 'USD', 'GBP');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false)
        {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        if (!$this->installOrderState()) {
            return false;
        }

        Configuration::updateValue('PAYSAFECASH_API_KEY', "psc_msNxtfOZMm3AOaw3cf5JtLkpn1INwbn");
        Configuration::updateValue('PAYSAFECASH_WEBHOOK_KEY', "-----BEGIN PUBLIC KEY-----MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyi+kPdCLaEvTrSnOk987H7cIgSrtfXuGz+NcQDjux3L1VJlRULGSk053ZkclYrPKrpayCPPIygHpbfUCW8yKFe3iL3ekpGqyr7GTRkSGRq6Kg32BAd1O2rks9iix4X3B9fA+/js4Ybz7bvQtJbqWPehVCraXRTbqY7vYSker8FM4t0EzA45hcS1wjnSeGHxlfBTZLe7quUowVG+CroPgsUSQ2KgOgzyXSEcrbfz3vtQJSosRKCahGhmkVbkV4TjYu60K/Cw59xLYLtpphLbHAALwBD/s2bxgO/xe/q30CCISfP/qwyfA3IzT2HqRzDcdbBiDLSup6LSiPDrcZnGwWwIDAQAB-----END PUBLIC KEY-----");
        Configuration::updateValue('PAYSAFECASH_TEST_MODE', true);
        Configuration::updateValue('PAYSAFECASH_DATA_TAKEOVER_MODE', false);
        Configuration::updateValue('PAYSAFECASH_VARIABLE_TIMEOUT', 4200);
        Configuration::updateValue('PAYSAFECASH_DEBUG', false);

       include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('displayPayment') &&
            $this->registerHook('displayPaymentReturn') &&
            $this->registerHook('displayPaymentTop') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionProductCancel') &&
            $this->installTab();
    }
    public function installOrderState()
    {
        if (!Configuration::get('PAYSAFECASH_OS_WAITING')
            || !Validate::isLoadedObject(new OrderState(Configuration::get('PAYSAFECASH_OS_WAITING')))) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Awaiting for Paysafecash Payment';
            }
            $order_state->hidden = false;
            $order_state->logable = false;
            $order_state->delivery = false;
            $order_state->send_email = false;
            $order_state->color = '#7887e6';
            $order_state->invoice = false;
            if ($order_state->add()) {
                $source = _PS_MODULE_DIR_.'paysafecash/logo.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.png';
                copy($source, $destination);
            }
            Configuration::updateValue('PAYSAFECASH_OS_WAITING', (int) $order_state->id);
        }
        if (!Configuration::get('PAYSAFECASH_OS_PAID')
            || !Validate::isLoadedObject(new OrderState(Configuration::get('PAYSAFECASH_OS_PAID')))) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Paysafecash payed';
            }
            $order_state->hidden = false;
            $order_state->logable = true;
            $order_state->delivery = true;
            $order_state->send_email = false;
            $order_state->color = '#7887e6';
            $order_state->invoice = true;
            $order_state->add();

            Configuration::updateValue('PAYSAFECASH_OS_PAID', (int) $order_state->id);
        }

        if (!Configuration::get('PAYSAFECASH_OS_EXPIRED')
            || !Validate::isLoadedObject(new OrderState(Configuration::get('PAYSAFECASH_OS_EXPIRED')))) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Paysafecash expired';
            }
            $order_state->hidden = false;
            $order_state->logable = true;
            $order_state->delivery = false;
            $order_state->send_email = false;
            $order_state->color = '#ff0000';
            $order_state->invoice = false;
            $order_state->add();

            Configuration::updateValue('PAYSAFECASH_OS_EXPIRED', (int) $order_state->id);
        }
        return true;
    }

    public function uninstallOrderState()
    {
        $order_state = new OrderState(Configuration::get('PAYSAFECASH_OS_WAITING'));
        $order_state->delete();

        $order_state = new OrderState(Configuration::get('PAYSAFECASH_OS_PAID'));
        $order_state->delete();
    }
    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPaysafecashtransactions');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'AdminPaysafecashtransactions';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'Paysafecash Transactions';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPaysafecashtransactionsController');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }


    public function uninstall()
    {
        $this->uninstallOrderState();

        Configuration::deleteByName('PAYSAFECASH_TEST_MODE');
        Configuration::deleteByName('PAYSAFECASH_API_KEY');
        Configuration::deleteByName('PAYSAFECASH_WEBHOOK_KEY');
        Configuration::deleteByName('PAYSAFECASH_SUBMERCHANT_ID');
        Configuration::deleteByName('PAYSAFECASH_OS_WAITING');
        Configuration::deleteByName('PAYSAFECASH_OS_PAID');
        Configuration::deleteByName('PAYSAFECASH_DATA_TAKEOVER_MODE');
        Configuration::deleteByName('PAYSAFECASH_VARIABLE_TIMEOUT');
        Configuration::deleteByName('PAYSAFECASH_DEBUG');

        return parent::uninstall( ) &&
            $this->uninstallTab();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitPaysafecashModule')) == true) {
            $this->postProcess();
        }

        if (((bool)Tools::isSubmit('refundPaysafecashModule')) == true) {
            $payment = $_POST["payment_id"];
            $amount = $_POST["payment_amount"];
            $this->processRefund($payment, number_format($amount, 2, '.', ''));
        }

        $this->context->smarty->assign('token', Tools::getAdminTokenLite('AdminModules'));
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('fields_value', $this->getConfigFormValues());
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPaysafecashModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }


    protected function getConfigFormValues()
    {
        return array(
            'PAYSAFECASH_TEST_MODE' => Configuration::get('PAYSAFECASH_TEST_MODE'),
            'PAYSAFECASH_API_KEY' => Configuration::get('PAYSAFECASH_API_KEY'),
            'PAYSAFECASH_WEBHOOK_KEY' => Configuration::get('PAYSAFECASH_WEBHOOK_KEY'),
            'PAYSAFECASH_SUBMERCHANT_ID' => Configuration::get('PAYSAFECASH_SUBMERCHANT_ID'),
            'PAYSAFECASH_DATA_TAKEOVER_MODE' => Configuration::get('PAYSAFECASH_DATA_TAKEOVER_MODE'),
            'PAYSAFECASH_VARIABLE_TIMEOUT' => Configuration::get('PAYSAFECASH_VARIABLE_TIMEOUT'),
            'PAYSAFECASH_DEBUG' => Configuration::get('PAYSAFECASH_DEBUG'),
        );
    }

    public function hookPaymentOptions($params)
    {

        if (!$this->active) {
            return;
        }
        $formAction = $this->context->link->getModuleLink($this->name, 'validation', array(), true);
        $this->smarty->assign(['action' => $formAction]);
        $paymentForm = $this->fetch('module:paysafecash/views/templates/hook/payment_options.tpl');

        /**
         * Create a PaymentOption object containing the necessary data
         * to display this module in the checkout
         */
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->displayName)
            ->setCallToActionText($this->displayName)
            ->setAction($formAction)
            ->setForm($paymentForm);

        $payment_options = array(
            $newOption
        );

        return $payment_options;
    }


    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function processRefund($payment_id, $amount){
        $correlation_id = "";
        require_once(_PS_MODULE_DIR_ . "paysafecash/libs/RefundClass.php");

        $testmode = Configuration::get('PAYSAFECASH_TEST_MODE');

        if ($testmode == "1") {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        $pscrefund = new PaysafecardCashRefundController(Configuration::get('PAYSAFECASH_API_KEY'), $env);
        $paymentDetail = $pscrefund->getPaymentDetail($payment_id);

        $refunded     = $pscrefund->getRefundedAmount();
        if($amount > ($paymentDetail["card_details"][0]["amount"] - $refunded)){
            $this->context->controller->errors[] = $this->l('The refund is higher than the Transaction.');
            return false;
        }

        if ($paymentDetail == false || isset($paymentDetail['number'])) {

        } else if (isset($paymentDetail["object"])) {
            if ($paymentDetail["status"] == "SUCCESS") {
                $response = $pscrefund->captureRefund($payment_id, $amount, $paymentDetail["currency"], $paymentDetail["customer"]["id"], $correlation_id);
            } elseif ($paymentDetail["status"] == "REDIRECTED") {
                // successful got details, but is in invalid state -> no refund can be processed
            }
        }
    }


    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookPayment($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false){
            return false;
        }

        $this->smarty->assign('module_dir', $this->_path);
        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

       return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false)
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookActionOrderStatusUpdate($params) {

        if( !empty($params) && !empty($params['id_order']) ) {
            $order = new Order((int)$params['id_order']);
        }

        if( !empty($params) && !empty($params['newOrderStatus']) ) {
            $new_order_status = $params['newOrderStatus'];
        }

        if( $new_order_status->id == _PS_OS_REFUND_ ) {

            $afterpay_refund = $this->_constructRefundObject($order);

            //get the cart total since this would be Full Refund
            $cart = new Cart($order->id_cart);
            $order_total = round($cart->getOrderTotal(), 2, PHP_ROUND_HALF_UP);

            $payments = $order->getOrderPayments();
            $afterpay_transaction_id = $payments[0]->transaction_id;

            $currency = new CurrencyCore($order->id_currency);
            $currency_code = $currency->iso_code;

            $results = $afterpay_refund->doRefund($afterpay_transaction_id, number_format($order_total, 2, '.', ''), $currency_code);
            $this->_verifyRefund( $results );

        }
        return false;
    }
    public function hookActionProductCancel($params)
    {

    }


    public function hookDisplayPaymentReturn($params)
    {
        return $this->hookPaymentReturn($params);
    }

    public function displayList()
    {

        $fields_list = [
            'id' => ['title' => 'ID','class' => 'fixed-width-xs'],
            'transaction_id' => ['title' => 'Transaction ID'],
            'status' => ['title' => 'Transaction State'],
            'transaction_time' => ['title' => 'Created','type'=>'datetime'],
        ];

        $helper = new HelperList();

        $helper->shopLinkType = '';

        $helper->simple_header = false;

        $helper->actions = array('view');

        $helper->identifier = 'id_paysafecashtransaction';
        $helper->show_toolbar = true;
        $helper->title = 'Paysafecash Transactions';
        $helper->table = "paysafecashtransaction";

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name .'&diplom_name=' . $diplom_name;

        $query = 'SELECT * FROM `'._DB_PREFIX_.'paysafecashtransaction`;';
        $results = Db::getInstance()->ExecuteS($query);

        return $helper->generateList($results,$fields_list);
    }


}
