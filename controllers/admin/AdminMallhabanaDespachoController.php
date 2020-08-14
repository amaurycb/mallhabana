<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';
require_once dirname(__FILE__) . '/../../classes/HTMLTemplateDespacho.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaDespachoController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;
        $this->pathToTpl = _PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/despacho.tpl';
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
        $start_date = Tools::getValue('start_date');
        $end_date = Tools::getValue('end_date');
        $supplier = (int) Tools::getValue('provider');
        
        if (Tools::isSubmit('submitDespacho')){
           try {
               $data = $this->service->getOrdersByProviders($supplier, $start_date, $end_date);
                $orders = $this->service->getOrdersByProvidersIDs($supplier, $start_date, $end_date);
                $supplier = new Supplier($supplier, 1);

                $this->context->smarty->assign([
                    'provider' => $supplier->name,
                    'genDate' =>  "Desde ".  $start_date." hasta ". $end_date,
                    'orders' =>  implode(", ", $orders)
                ]);

                $pdf = new PDF($data, 'Despacho', Context::getContext()->smarty);
                $pdf->render();
                
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }      
        parent::postProcess();

    }

        /**
     * Format data for printing document
     */
    private function getOrdersForPrint($orders) {
        $order_invoice_list = Db::getInstance()->executeS('
            SELECT oi.*
            FROM `' . _DB_PREFIX_ . 'orders` o 
            LEFT JOIN `' . _DB_PREFIX_ . 'order_invoice` oi ON (o.`id_order` = oi.`id_order`)
            WHERE o.id_order IN ('.implode(",", $orders).')
            ORDER BY oi.delivery_date ASC
        ');

        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }

}
