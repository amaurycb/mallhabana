<?php

/**
 * Class CubacelBackController
 */
class AdminMallhabanaSupplyController extends ModuleAdminController {
     public function __construct() {
        parent::__construct();

        $this->_select = 'c.name as carrier_name';
        $this->_join = '
          JOIN '._DB_PREFIX_.'carrier c ON (c.id_carrier = a.id_carrier)
        ';
        //Filter list by order status
        $this->_where = 'AND a.current_state = 3';

        $this->bootstrap = true; 
        $this->table = Order::$definition['table'];
        $this->identifier = Order::$definition['primary']; 
        $this->className = Order::class;
        $this->allow_export = true;
        $this->lang = false; 
        $this->_defaultOrderBy = Order::$definition['primary'];

        $this->fields_list = array(
            'id_order' => array(
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
            'total_shipping' => array(
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
        //Custom action in datatable row    
        $this->addRowAction('printOrder');
        return parent::renderList();
    }
   
    public function viewAccess($disable = false) {
        if (version_compare(_PS_VERSION_, '1.7', '<='))
            return true;
        return parent::viewAccess($disable);
    }

    public function renderView() {
        return parent::renderView();
    } 

    public function processBulkprintDeliveryNotes(){
        $orders = $_POST['ordersBox'];
        $orderCollection = $this->getOrdersForPrint($orders);

        $pdf = new PDF($orderCollection, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);
        return $pdf->render(true);
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

    /**
     * Format button for custom datatable action row
     */
    public function displayPrintOrderLink($token = null, $id, $name = null) {
        $href = $this->context->link->getAdminLink('AdminMallhabanaSupply').'&'.$this->identifier.'='.$id.'&action=printOrder';
        return '<form action="$href" method="POST"><button type="submit" class="btn btn-default" title="Imprimir">
                    <i class="icon-print"></i> Imprimir
                </button></form>';
    }
}
