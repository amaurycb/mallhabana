<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaSupplyController extends ModuleAdminController {
     public function __construct() {
        parent::__construct();

        // Get statuses
        $this->statuses = [];
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            if (in_array($status['id_order_state'], [2,3,4,20])) {
                $this->statuses[$status['name']] = $status['name'];
            }
        }

        // Get suppliers
        $this->carriers = [];
        $carriers = Db::getInstance()->executeS('SELECT id_carrier, name FROM '._DB_PREFIX_.'carrier group by id_reference');
        foreach($carriers as $carrier) {
            $this->carriers[$carrier['id_carrier']] =  $carrier['name'];
        }
       
        $this->_select = ' a.id_carrier, c.name as carrier_name, osl.name as state_name, os.color';
        $this->_join = '
          JOIN '._DB_PREFIX_.'carrier c ON (c.id_carrier = a.id_carrier)
          LEFT JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = a.current_state)
          LEFT JOIN '._DB_PREFIX_.'order_state_lang osl ON (osl.id_order_state = a.current_state AND osl.id_lang = 1)
        ';

        //Filter list by order status
        $this->_where = 'AND a.current_state IN (2,3,4,20) GROUP BY a.id_order';
     
        $this->bootstrap = true; 
        $this->table = Order::$definition['table'];
        $this->identifier = Order::$definition['primary']; 
        $this->className = Order::class;
        $this->allow_export = true;
        $this->lang = false; 
        $this->_defaultOrderBy = Order::$definition['primary'];
        $this->service = new MallHabanaService();

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->module->l('ID'), 
                'align' => 'text-center',
                'class' => 'fixed-width-md',
            ),
            'date_add' => array(
                'title' => $this->module->l('Fecha de la Orden'),
                'type' => 'date',
                'filter_key' => 'a!date_add',
                'align' => 'text-center',
                'class' => 'fixed-width-md',
                'search' => true   
            ),            
            'carrier_name' => array(
                'title' => $this->module->l('Transportista'),
                'align' => 'text-center',
                'havingFilter' => true,
                'class' => 'fixed-width-lg',
                'type' => 'select',
                'class' => 'fixed-width-lg',
                'list' => $this->carriers,
                'filter_key' => 'id_carrier',
                'filter_type' => 'number',
                'order_key' => 'id_carrier'
            ),
            'total_shipping' => array(
                'title' => $this->module->l('Costo de transportación'),
                'align' => 'text-center',
                'type' => 'price',
                'class' => 'fixed-width-xs'
            ),     
            'state_name' => array(
                'title' => $this->module->l('Estado'),
                'align' => 'text-center',
                'havingFilter' => true,
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses,
                'filter_key' => 'state_name',
                'filter_type' => 'text',
                'order_key' => 'state_name'
            )
        );

        $this->bulk_actions = [
            'printDeliveryNotes' => [
              'text' => 'Imprimir',
              'icon' => 'icon-print'
            ]
          ];
    }

    /**
     * Add action print into order list
     */
    public function renderList() { 
        $this->addRowAction('printOrder');
        return parent::renderList();
    }
   
    public function viewAccess($disable = false) {
        if (version_compare(_PS_VERSION_, '1.7', '<='))
            return true;
        return parent::viewAccess($disable);
    }

    /**
     * Bulk action for printing pdf
     */
    public function processBulkprintDeliveryNotes(){
        $orders = $_POST['ordersBox'];
        return $this->renderPdf($orders);
    }

    /**
     * Render pdf
     */
    private function renderPdf ($orders) {        
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
        $href = $this->context->link->getAdminLink('AdminMallhabanaSupply').'&action=printOrder';
        return '<form id="order'.$id.'" action="'.$href.'" method="POST">
                <input type="hidden" value="'.$id.'" name="'.$this->identifier.'">
                <input type="hidden" value="1" name="onlyOne">
                <button type="submit" class="btn btn-default" title="Imprimir">
                    <i class="icon-print"></i> Imprimir
                </button></form>';
    }

    /**
     * Print individual orders
     */
    public function postProcess() {
        if (Tools::getValue('action') == 'printOrder'){
            try {
                $idOrder = (int)Tools::getValue('id_order');
                $orders = Tools::isSubmit('submitBulkprintDeliveryNotesorders') && !empty($_POST['ordersBox']) ? $_POST['ordersBox'] : $idOrder;
                if( count($orders) > 0) {
                    $this->updateStatus($orders);
                    return $this->renderPdf($orders);
                }
            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        }      
        parent::postProcess();

    }

    /**
     * Update ordes status
     */
    private function updateStatus(array $orders) {
        foreach ($orders as $order) {
            $objOrder = new Order((int)$order);
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $history->changeIdOrderState(4, (int)($objOrder->id)); //order status=3
            $history->add();
        }
    }
    
    /**
     * Disable add button
     */
    public function initToolbar() {
        parent::initToolbar();
        unset( $this->toolbar_btn['new'] );
    }
}
