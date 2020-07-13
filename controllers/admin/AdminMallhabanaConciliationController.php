<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaConciliationController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;
        $this->pathToTpl = _PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/config_conciliation.tpl';
        $this->service = new MallHabanaService();
    }

    public function initContent() {
        parent::initContent();
        $suppliers = $this->service->getSuppliers();
        $this->context->smarty->assign([
            'suppliers' => $suppliers
        ]);
        $this->content.=$this->context->smarty->fetch($this->pathToTpl);
        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    } 

     /**
     * Print individual orders
     */
    public function postProcess() {
        if (Tools::isSubmit('submitConciliation')){
            try {
                $month = Tools::getValue('month');
                $provider = Tools::getValue('provider');
                $year = (int)Tools::getValue('year');
                $orders = $this->service->ordersByProviderAndDate($month, $year, $provider);
                $headers = $this->service->conciliationHeaders();
                $this->service->excel($headers, $orders);
                
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }      
        parent::postProcess();

    }

}
