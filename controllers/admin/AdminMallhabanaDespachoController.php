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
        $month = ((int)Tools::getValue('month') < 10) ? "0".Tools::getValue('month') : Tools::getValue('month');
        $supplier = (int) Tools::getValue('provider');
        $year = (int)Tools::getValue('year');
        $day = ((int)Tools::getValue('day') < 10) ? "0".Tools::getValue('day') : Tools::getValue('day');
        
        if (Tools::isSubmit('submitDespacho')){
           try {
               $data = $this->service->getOrdersByProviders($supplier, $year."-".$month."-".$day);
                $orders = $this->service->getOrdersByProvidersIDs($supplier, $year."-".$month."-".$day);
                $supplier = new Supplier($supplier, 1);

                $this->context->smarty->assign([
                    'provider' => $supplier->name,
                    'genDate' =>  $year."-".$month."-".$day,
                    'orders' =>  implode(", ", $orders)
                ]);

                $pdf = new PDF($data, 'Despacho', Context::getContext()->smarty);
                $pdf->render();
                
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }      
        else if (Tools::isSubmit('submitDespachoFullOrder')){
            try {
                $orders = $this->service->getOrdersByProvidersIDs($supplier, $year."-".$month."-".$day);
                $orderCollection = $this->getOrdersForPrint($orders);
                $pdf = new PDF($orderCollection, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);
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
