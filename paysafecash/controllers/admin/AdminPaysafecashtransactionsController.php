<?php
//use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
require_once _PS_MODULE_DIR_ .'/paysafecash/models/Paysafecashtransaction.php';

class AdminPaysafecashtransactionsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->module = 'paysafecash';
        $this->bootstrap = true;
        $this->context = Context::getContext();
        //The following 2 lines are useful if you have to link your controller to a certain table for data grids
        $this->table = 'paysafecashtransaction';
        $this->className = 'Paysafecashtransaction';
        $this->allow_export = false;
        $this->_defaultOrderBy = 'a.transaction_time';
        $this->_defaultOrderWay = 'DESC';
        $this->identifier = 'id_paysafecashtransaction';
        $this->actions_available = array();

        parent::__construct();

        $this->fields_list = [
            'transaction_id' => ['title' => 'Transaction ID'],
            'order_id' => ['title' => 'Order ID'],
            'cart_id' => ['title' => 'Cart ID'],
            'status' => ['title' => 'Status'],
            'refunded_amount' => ['title' => 'Refunded Amount'],
            'transaction_time' => ['title' => 'Created','type'=>'datetime'],
        ];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Show Transaction'),
                'icon' => 'icon-cog'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Transaction ID'),
                    'name' => 'transaction_id',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Order ID'),
                    'name' => 'order_id',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Cart ID'),
                    'name' => 'cart_id',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Status'),
                    'name' => 'status',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Refunded Amount'),
                    'name' => 'refunded_amount',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Created'),
                    'name' => 'transaction_time',
                    'readonly' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Ok'),
            ]
        ];
        return parent::renderForm();
    }

}