<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class AdminMallhabanaConciliationController
 * Controlador para generar el documento de Conciliación.
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

    /**
     * La vista prnicipal contiene el listado de proveedores y dos selectores para la fecha de la conciliación
     */
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
     * Generar el reporete conciliación basado en el mes y año seleccionados.
     * Si se selecciona un proveedor en la vista, entonces se genera el reporte para dicho proveedor.
     * Si se seleccionan "Todos los Proveedores", entonces se genera el reporte con los datos de todos los proveedores.
     */
    public function postProcess() {
        if (Tools::isSubmit('submitConciliation')){
            try {
                $month = Tools::getValue('month');
                $provider = (int) Tools::getValue('provider');
                $year = (int)Tools::getValue('year');
                $data = $this->service->ordersAllProvidersByDate($month, $year, $provider);
                $orders = $data['orders'];
                $headers = $data['headers'];            
                $this->service->excel($headers, $orders);
                
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }      
        parent::postProcess();

    }

}
