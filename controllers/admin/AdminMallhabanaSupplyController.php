<?php

/**
 * Class CubacelBackController
 */
class AdminMallhabanaSupplyController extends ModuleAdminController {
     public function __construct() {
        parent::__construct();

        $this->_select = 'o.reference as reference, c.name as carrier_name';
        $this->_join = '
          JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
          JOIN '._DB_PREFIX_.'carrier c ON (c.id_carrier = a.id_carrier)
        ';
        //Filter list by order status
        $this->_where = 'AND o.current_state = 3';

        $this->bootstrap = true; 
        $this->table = OrderCarrierCore::$definition['table'];
        $this->identifier = OrderCarrierCore::$definition['primary']; 
        $this->className = OrderCarrierCore::class;
        $this->allow_export = true;
        $this->lang = false; 
        $this->_defaultOrderBy = OrderCarrierCore::$definition['primary'];

        $this->fields_list = array(
            'id_order_carrier' => array(
                'title' => $this->module->l('ID'), 
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->module->l('Order'),
                'align' => 'text-center',
                // 'class' => 'fixed-width-lg'
            ),
            'date_add' => array(
                'title' => $this->module->l('Fecha de la Orden'),
                'align' => 'text-center',
                'type'=>'datetime',
                // 'class' => 'fixed-width-xs'
            ),            
            'carrier_name' => array(
                'title' => $this->module->l('Transportista'),
                'align' => 'text-center',
                // 'class' => 'fixed-width-lg'
            ),
            'shipping_cost_tax_incl' => array(
                'title' => $this->module->l('Costo de transportación'),
                'align' => 'text-right',
                'type' => 'price',
                // 'class' => 'fixed-width-md'
            )
        );

        $this->bulk_actions = [
            'printDeliveryNotes' => [
              'text' => 'Imprimir',
              'icon' => 'icon-print'
            ],
          ];
    }

    public function renderForm() {
        return parent::renderForm();
    }

    public function renderList() {     
        $this->addRowAction('view');
        return parent::renderList();
    }
   
    public function viewAccess($disable = false) {
        if (version_compare(_PS_VERSION_, '1.7', '<='))
            return true;
        return parent::viewAccess($disable);
    }

    public function renderView()
    {
        return parent::renderView();
    } 

    protected function processBulkprintDeliveryNotes(){
        $ordersCarrier = $_POST['order_carrierBox'];
        $orderCollection = $this->getOrdersForPrint($ordersCarrier);

        $pdf = new PDF($orderCollection, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);
        return $pdf->render(true);
    }

    /**
     * Format data for printing document
     */
    private function getOrdersForPrint($orderCarrier) {
        $order_invoice_list = Db::getInstance()->executeS('
            SELECT oi.*
            FROM `' . _DB_PREFIX_ . 'orders` o 
            LEFT JOIN `' . _DB_PREFIX_ . 'order_invoice` oi ON (o.`id_order` = oi.`id_order`)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (o.`id_order` = oc.`id_order`)
            WHERE oc.id_order_carrier IN ('.implode(",", $orderCarrier).')
            ORDER BY oi.delivery_date ASC
        ');

        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }
}
