<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';
require_once dirname(__FILE__) . '/../../classes/HTMLTemplateDespacho.php';
require_once dirname(__FILE__) . '/../../classes/HTMLTemplateDespachoProvider.php';

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
        $data = $this->service->getOrdersByProviders($supplier, $start_date, $end_date);
        $ordersIds = $this->service->getOrdersByProvidersIDs($supplier, $start_date, $end_date);
        $supplier = new Supplier($supplier, 1);

        if (Tools::isSubmit('submitDespacho')){
           try {  
                $this->context->smarty->assign([
                    'provider' => $supplier->name,
                    'genDate' =>  "Desde ".  $start_date." hasta ". $end_date,
                    'orders' =>  implode(", ", $ordersIds)
                ]);

                $pdf = new PDF($data, 'Despacho', Context::getContext()->smarty);
                $pdf->render();
                
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        } 
        else if (Tools::isSubmit('submitOrders')){
            try {
                $products = [];
                $orders = [];
                $customers = [];     
                foreach ($ordersIds as $oId) {    
                    $totalOrder = 0;    
                    $fullProducts = $this->service->getProductsByOrderAndSupplier($supplier->id, $oId);         
                    $order = new Order($oId,1);
                    $orders[$oId] = [$order];  
                    foreach ($fullProducts as $p) {     
                        $detail = $this->service->getProductCustomization((int)$p['product_id'], $order->id_cart);
                        if (isset($detail[0])) {
                            $detail = " (Detalle: ".$detail[0]['value']. ")"; 
                        } else {
                            $detail = "";
                        }
                        $p['product_name'] =  $p['product_name'] .$detail;                
                        $products[$oId][] = $p;              
                        $totalOrder += ($p["original_wholesale_price"] * $p["product_quantity"]);  
                    }    
                    $customer = new Customer($order->id_customer,1);
                    $address = new Address($order->id_address_delivery,1);
                    $state = new State ($address->id_state,1);
                    $country = new Country ($address->id_country,1);
                   
                    $customers[$oId] = [
                        'name' => $customer->firstname. " " .  $customer->lastname,
                        'destiny' => $address->firstname . " " . $address->lastname,
                        'phone' => $address->phone,
                        'address' => $address->address1. ", Entre: ". $address->address2. ", ". $address->city. ", ". $state->name. ", ". $country->name,
                        'url_code_qr'       => Configuration::get('SITE_URL').'img/codes/qr/'.$oId.".jpg",
                        'url_code_barcode'  => Configuration::get('SITE_URL').'img/codes/barcode/'.$oId.".jpg",
                        'id_order'          => $oId,
                        'totalOrder'        => $totalOrder
                    ];       
                
                }
                // $supplier = new Supplier($provider, 1);
                $this->context->smarty->assign([
                    'supplier' => $supplier->name,
                    'genDate' => date('Y-m-d'),
                    'date' => date('Y-m-d'),
                    'products' => $products,
                    'orders' => $orders,
                    'customers' => $customers                    
                ]);

                $pdf = new PDF($orders, 'DespachoProvider', Context::getContext()->smarty);
                return $pdf->render();
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }         
        parent::postProcess();

    }

    /**
     * Despacho proveedores
     */
    private function bySupplier($ordersIds, $provider) {  
           
        
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
