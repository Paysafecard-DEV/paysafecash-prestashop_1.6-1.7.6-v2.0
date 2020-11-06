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
        $this->_defaultOrderBy = 'a.id_paysafecashtransaction';
        $this->_defaultOrderWay = 'DESC';
        $this->identifier = 'id_paysafecashtransaction';

        $this->fields_list = [
            'id' => ['title' => 'ID','class' => 'fixed-width-xs'],
            'transaction_id' => ['title' => 'Transaction ID'],
            'order_id' => ['title' => 'Order ID'],
            'status' => ['title' => 'Status'],
            'transaction_time' => ['title' => 'Created','type'=>'datetime'],
        ];

        if(isset($_GET["updatepaysafecashtransaction"])){
            $this->displayUpdatepaysafecashtransactionAction();
        }

        exec('echo "QUERY: '.print_r( parent::getFromClause(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
    }
    public function initContent()
    {
        exec('echo "QUERY: '.print_r( parent::getFromClause(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
    }

    protected function getFromClause() {
        exec('echo "QUERY: '.print_r( parent::getFromClause(), true).'" >> '.getcwd().'/modules/paysafecash/log.log');
        return str_replace(_DB_PREFIX_, '', parent::getFromClause());

    }

    public function displayUpdatepaysafecashtransactionAction()
    {
        /**/
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'paysafecash/views/templates/admin/transactions.tpl');
        $this->context->smarty->assign(array(
            "content" => $content,
        ));


        //return $this->display(_PS_MODULE_DIR_ . 'paysafecash/views/templates/admin/transactions.tpl');


        //print_r($GLOBALS);
        //return $this->template(__FILE__, 'views/templates/admin/transactions.tpl');
    }

    public function ajaxProcess()
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'paysafecashtransaction`;';
        exec('echo "QUERY: '.print_r( $query, true).'" >> '.getcwd().'/modules/paysafecash/log.log');
        echo Tools::jsonEncode(array(
            'data'=> Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query),
            'fields_display' => $this->fieldsDisplay
        ));
    }

}