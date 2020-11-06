<?php
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class AdminPaysafecashTransactionsController extends ModuleAdminControllerCore
{
    public function getContent()
    {
        return $this->display(__FILE__, 'views/templates/admin/transactions.tpl');
    }

    public function demoAction()
    {
        return $this->render('@Modules/your-module/templates/admin/demo.html.twig');
    }


}