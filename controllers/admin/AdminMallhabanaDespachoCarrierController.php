<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';
require_once dirname(__FILE__) . '/../../classes/HTMLTemplateDespachoCarrier.php';

/**
 * Class AdminMallhabanaDespachoCarrierController
 * Controlador para generar el despacho por transportistas.
 */
class AdminMallhabanaDespachoCarrierController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;
        $this->pathToTpl = _PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/despacho_carrier.tpl';
        $this->service = new MallHabanaService();
    }

    /**
     * La vista prnicipal contiene el listado de transportistas y la fecha.
     * Opcionalmente puede incluirse los ids de otras órdenes que quien ser incluidas en el reporte
     */
    public function initContent() {
        parent::initContent();
        $carriers = $this->service->getCarriers();
        $this->context->smarty->assign([
            'carriers' => $carriers
        ]);
        $this->content.=$this->context->smarty->fetch($this->pathToTpl);
        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    } 

     /**
     * Generar el documento de despaacho
     */
    public function postProcess() {
        $date = Tools::getValue('date_query');
        $carrierId = (int) Tools::getValue('carrier');
        $carrier = new Carrier($carrierId, 1);
        $carrierByReference = Db::getInstance()->executeS('SELECT c.id_carrier FROM '._DB_PREFIX_.'carrier c WHERE c.id_reference = '.$carrier->id_reference);
        $carriersID = [];
        foreach ($carrierByReference as $c) {
            $carriersID[] = $c['id_carrier'];
        }
        $ordersIds = !empty(Tools::getValue('orders')) ? explode(",", Tools::getValue('orders')) : [];
        if (Tools::isSubmit('submitDespachoCarrier')){
        try {
            $fullProducts = $this->service->getOrdersProductsByCarrier($carriersID, $date, $ordersIds);
            $products = [];
            $orders = [];
            $customers = [];
            foreach ($fullProducts as $p) {
                $order = new Order($p['id_order'],1);
                $orders[$p['id_order']] = [$order];
                
                $detail = $this->service->getProductCustomization((int)$p['product_id'], $order->id_cart);
                if (!empty($detail)) {
                    $detail = " (Detalle: ".$detail[0]['value']. ")"; 
                } else {
                    $detail = "";
                }
                $p['product_name'] =  $p['product_name'] .$detail;
                
                $products[$p['id_order']][] = $p;

                $customer = new Customer($order->id_customer,1);
                $address = new Address($order->id_address_delivery,1);
                $state = new State ($address->id_state,1);
                $country = new Country ($address->id_country,1);
                $customers[$p['id_order']] = [
                    'name' => $customer->firstname. " " .  $customer->lastname,
                    'destiny' => $address->firstname . " " . $address->lastname,
                    'phone' => $address->phone,
                    'ci' => $address->dni,
                    'address' => $address->address1. ", Entre: ". $address->address2. ", ". $address->city. ", ". $state->name. ", ". $country->name,
                    'url_code_qr'       => Configuration::get('SITE_URL').'img/codes/qr/'.$p['id_order'].".jpg",
                    'url_code_barcode'  => Configuration::get('SITE_URL').'img/codes/barcode/'.$p['id_order'].".jpg",
                    'id_order'          => $p['id_order']
                ];
            } 

            $this->context->smarty->assign([
                'carrier' => $carrier->name,
                'genDate' => $date,
                'date' => $date,
                'products' => $products,
                'orders' => $orders,
                'customers' => $customers                    
            ]);

            $pdf = new PDF($orders, 'DespachoCarrier', Context::getContext()->smarty);
            $pdf->render();
                
        } catch (PrestaShopException $e) {
            $this->errors[] = $e->getMessage();
        }
        }    
        parent::postProcess();

    }

}
