<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaOrdersController extends ModuleAdminController {
     public function __construct() {
        parent::__construct();

        $this->bootstrap = true; 
        $this->table = Order::$definition['table'];
        $this->identifier = Order::$definition['primary']; 
        $this->className = Order::class;
        $this->allow_export = true;
        $this->lang = false; 
        $this->_defaultOrderBy = Order::$definition['primary'];
        $this->service = new MallHabanaService();
        $this->addRowAction('view');
        $this->pathToTpl = _PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/order.tpl';

        // Get statuses
        $this->statuses = [];
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses[$status['id_order_state']] = $status['name'];
        }

        $this->_select = '
		a.id_currency,
		a.id_order AS id_pdf,
		CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		IF((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
		country_lang.name as cname,
		IF(a.valid, 1, 0) badge_success';

        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
		LEFT JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery
		LEFT JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
		LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int) $this->context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        if (!$this->context->employee->isSuperAdmin() && !$this->service->canViewAll($this->context->employee)) {
            $this->_join.= 'LEFT JOIN `' . _DB_PREFIX_ . 'order_owner` ow ON (ow.`id_order` = a.`id_order`)';
            $this->_where = 'AND ow.id_owner = '.(int)$this->context->employee->id;
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->trans('Reference', array(), 'Admin.Global'),
            ),
            'customer' => array(
                'title' => $this->trans('Customer', array(), 'Admin.Global'),
                'havingFilter' => true,
            ),

            'total_paid_tax_incl' => array(
                'title' => $this->trans('Total', array(), 'Admin.Global'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true,
            ),
            'payment' => array(
                'title' => $this->trans('Payment', array(), 'Admin.Global'),
            ),
            'osname' => array(
                'title' => $this->trans('Status', array(), 'Admin.Global'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'date_add' => array(
                'title' => $this->trans('Date', array(), 'Admin.Global'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );

        if (Country::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT DISTINCT c.id_country, cl.`name`
			FROM `' . _DB_PREFIX_ . 'orders` o
			' . Shop::addSqlAssociation('orders', 'o') . '
			INNER JOIN `' . _DB_PREFIX_ . 'address` a ON a.id_address = o.id_address_delivery
			INNER JOIN `' . _DB_PREFIX_ . 'country` c ON a.id_country = c.id_country
			INNER JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $this->context->language->id . ')
			ORDER BY cl.name ASC');

            $country_array = array();
            foreach ($result as $row) {
                $country_array[$row['id_country']] = $row['name'];
            }

            $part1 = array_slice($this->fields_list, 0, 3);
            $part2 = array_slice($this->fields_list, 3);
            $part1['cname'] = array(
                'title' => $this->trans('Delivery', array(), 'Admin.Global'),
                'type' => 'select',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname',
            );
            $this->fields_list = array_merge($part1, $part2);
        }
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

    public static function setOrderCurrency($echo, $tr) {
        $order = new Order($tr['id_order']);
        return Tools::displayPrice($echo, (int) $order->id_currency);
    }

    public function renderView () {
        $order = new Order(Tools::getValue('id_order'));
        $customer = new Customer($order->id_customer);
        $address = new Address($order->id_address_delivery, $this->context->language->id);
        $state = new State ($address->id_state,1);
        $country = new Country ($address->id_country,1);
        $history = $order->getHistory($this->context->language->id);
        $carrier = new Carrier($order->id_carrier, $order->id_lang);
        $payment = OrderPayment::getByOrderId($order->id);
        
        foreach ($history as &$order_state) {
            $order_state['text-color'] = Tools::getBrightness($order_state['color']) < 128 ? 'white' : 'black';
        }
        $this->context->smarty->assign([
            'order' => $order,
            'currency' => new Currency($order->id_currency),
            'customer' => $customer,
            'address' => $address,
            'products' => $this->getProducts($order),
            'history' => $history,
            'states' => OrderState::getOrderStates($this->context->language->id),
            'currentState' => $order->getCurrentOrderState(),
            'country' => $country,
            'state' => $state,
            'carrier' => $carrier,
            'payment' => $payment[0],
            'is_admin' => ($this->context->employee->isSuperAdmin() || $this->service->canViewAll($this->context->employee))
        ]);
        $this->content.=$this->context->smarty->fetch($this->pathToTpl);

        //printf("<pre>%s</pre>",print_r(parent::renderView(),true));
        $this->context->smarty->assign([
           
            'content' => $this->content,
        ]);
        return parent::renderView();
    }

    protected function getProducts($order) {
        $products = $order->getProducts();
        foreach ($products as &$product) {
            if ($product['image'] != null) {
                $name = 'product_mini_' . (int) $product['product_id'] . (isset($product['product_attribute_id']) ? '_' . (int) $product['product_attribute_id'] : '') . '.jpg';
                // generate image cache, only for back office
                $product['image_tag'] = ImageManager::thumbnail(_PS_IMG_DIR_ . 'p/' . $product['image']->getExistingImgPath() . '.jpg', $name, 45, 'jpg');
                if (file_exists(_PS_TMP_IMG_DIR_ . $name)) {
                    $product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_ . $name);
                } else {
                    $product['image_size'] = false;
                }
            }
            if (!$this->context->employee->isSuperAdmin() && !$this->service->canViewAll($this->context->employee)) {
                $data = Db::getInstance()->executeS("SELECT * FROM prstshp_product_owner WHERE id_product = ".(int)$product['product_id'] . " AND id_owner = ". (int)$this->context->employee->id);
                $product['owner'] = count($data) > 0;
            } else {
                $product['owner'] = false;
            }
        }
        ksort($products);
        return $products;
    }
}
