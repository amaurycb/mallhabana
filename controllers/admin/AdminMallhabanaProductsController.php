<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaProductsController extends ModuleAdminController {
     public function __construct() {
        parent::__construct();

        $this->bootstrap = true; 
        $this->table = Product::$definition['table'];
        $this->identifier = Product::$definition['primary']; 
        $this->className = Product::class;
        $this->allow_export = true;
        $this->lang = false; 
        $this->_defaultOrderBy = Product::$definition['primary'];
        $this->service = new MallHabanaService();
        $this->addRowAction('edit');
        $this->pathToTpl = _PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/product.tpl';


        $this->_select = 'pl.name as pname';
        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->_orderBy = 'id_product';
        $this->_orderWay = 'DESC';

        if (!$this->context->employee->isSuperAdmin() && !$this->service->canViewAll($this->context->employee)) {
            $this->_join.= 'LEFT JOIN `' . _DB_PREFIX_ . 'product_owner` ow ON (ow.`id_product` = a.`id_product`)';
            $this->_where = 'AND ow.id_owner = '.(int)$this->context->employee->id;
        }

        $this->fields_list = array(
            'id_product' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->trans('Reference', array(), 'Admin.Global'),
            ),
            'pname' => array(
                'title' => 'Nombre',
            ),
            'quantity' => array(
                'title' => 'Cantidad',
                'havingFilter' => true,
            ),

        );

    }

    public function viewAccess($disable = false) {
        if (version_compare(_PS_VERSION_, '1.7', '<='))
            return true;
        return parent::viewAccess($disable);
    }

    /**
     * Print individual orders
     */
    public function postProcess() {
        if (Tools::isSubmit('submitUpdateQty')){
            try {
                $id_product = (int)Tools::getValue('id_product');
                $qty = (int)Tools::getValue('qty');
                $this->service->updateProductQuantity($id_product, $qty);
                return Tools::redirect(Context::getContext()->link->getAdminLink('AdminMallhabanaProducts'));

            } catch (PrestaShopException $e) {
                $this->errors[] = $e->getMessage();
            }
        } 
        return parent::postProcess();
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

    public function renderForm () {
        $product = new Product(Tools::getValue('id_product'));
      
        $this->context->smarty->assign([
            'product' => $product,
        ]);
        $this->content.=$this->context->smarty->fetch($this->pathToTpl);

        $this->context->smarty->assign([
           
            'content' => $this->content,
        ]);
        return parent::renderForm();
    }

}
