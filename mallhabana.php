<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__. '/classes/MallHabanaService.php';

class Mallhabana extends Module {
    
    public function __construct() {
        $this->name = 'mallhabana';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Yolanda Mauri Pérez';
        $this->need_instance = 0;
        $this->ps_versions_compliancy =  ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->service = new MallHabanaService();

        parent::__construct();

        $this->displayName = $this->l('MallHabana');
        $this->description = $this->l('Funciones complementarias para MallHabana.com');

        $this->confirmUninstall = $this->l('¿Está seguro de que desea instalar el módulo?');

        if (!Configuration::get('MALLHABANA')) {
            $this->warning = $this->l('No name provided.');
        }
    }
    

    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
    
        // mkdir( _PS_ROOT_DIR_."/img/codes", 777);
        // mkdir( _PS_ROOT_DIR_."/img/codes/qr", 777);
        // mkdir( _PS_ROOT_DIR_."/img/codes/barcode", 777);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('displayPDFInvoice') &&    
            $this->registerHook('displayPDFSupplyOrder') &&      
            $this->registerHook('displayPDFDeliverySlip') &&              
            $this->registerHook('displayAdminOrder') &&                        
            $this->registerHook('displayPDFSupplyOrderForm') &&
            $this->registerHook('actionAdminOrdersListingFieldsModifier') &&
            $this->registerHook('HookDisplayBackOfficeHeader') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('displayBeforeCarrier') &&
            Configuration::updateValue('MALLHABANA', 'Funciones complementarias para MallHabana.com');
    }

    public function uninstall() { 
        // rmdir( _PS_ROOT_DIR_."/img/codes", 777);   
        // Configuration::deleteByName('NO_STOCK_REDIRECTION');
        // Configuration::deleteByName('NO_STOCK_MESSAGE');
        // Configuration::deleteByName('SITE_URL'); 
        return !(!parent::uninstall() || !Configuration::deleteByName('MALLHABANA')) ;
    }

    public function getContent() {
        $output = null;
        if (Tools::isSubmit('submit'.$this->name)) {            
            $mallhabana = Tools::getValue('MALLHABANA', true);
            if (!$mallhabana || empty($mallhabana)) {
                $output .= $this->displayError($this->l('Valor de configuración incorrecto.'));
            } else {
                foreach ($mallhabana as $key => $value) {
                    if (Validate::isGenericName($value)) {
                        Configuration::updateValue($key, strval($value));
                    }
                }
                $output .= $this->displayConfirmation($this->l('Configuraciones actualizadas'));
            }
        }
        $output .= $this->displayForm();
        return $output;
    }

    /**
     * Configuration form
     */
    public function displayForm() {
        // Get default language
        $defaultLang = (int) Configuration::get('"._DB_PREFIX_."LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'tinymce' => true,
            'legend' => [
                'title' => $this->l('Configuración de MallHabana'),
            ],
            'input' => [
                // [
                //     'type' => 'text',
                //     'label' => $this->l('Redirección Productos sin Stock'),
                //     'name' => 'MALLHABANA[NO_STOCK_REDIRECTION]',
                //     'required' => true,
                //     'cols' => 2,
                //     'prefix' => '<i class="icon-link"></i>'
                // ],
                // [
                //     'type' => 'text',
                //     'label' => $this->l('Mensaje Productos sin Stock'),
                //     'name' => 'MALLHABANA[NO_STOCK_MESSAGE]',
                //     'required' => true,
                //     'cols' => 2,
                // ],  
                [
                    'type' => 'text',
                    'label' => $this->l('Url base de la tienda'),
                    'name' => 'MALLHABANA[SITE_URL]',
                    'required' => true,
                    'cols' => 2,
                    'prefix' => '<i class="icon-link"></i>'
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value = [
            'MALLHABANA[NO_STOCK_REDIRECTION]'      => Configuration::get('NO_STOCK_REDIRECTION'),
            'MALLHABANA[NO_STOCK_MESSAGE]'          => Configuration::get('NO_STOCK_MESSAGE'),
            'MALLHABANA[SITE_URL]'                  => Configuration::get('SITE_URL')
        ];

        return $helper->generateForm($fieldsForm);
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader() {
        $this->context->controller->addJS($this->_path.'views/js/front.js');
    }

    public function hookActionOrderStatusPostUpdate($params) {
        $this->service->generateQr($params['id_order']);
        $this->service->generateBarcode($params['id_order']);
    }

    /**
     * Display Qr and Barcode on invoice PDF
     */
    public function hookDisplayPDFInvoice($params) {
        $idOrder = $params['object']->id_order;
        return $this->getSmartyVariablesPDF($idOrder);
    }

    /**
     * Display Qr and Barcode on invoice PDF
     */
    public function hookDisplayPDFOrderSlip($params) {
        $idOrder = $params['object']->id_order;
        return $this->getSmartyVariablesPDF($idOrder);
    }

    /**
     * Display Qr and Barcode on invoice PDF
     */
    public function hookDisplayPDFSupplyOrderForm($params) {
        $idOrder = $params['object']->id_order;
        return $this->getSmartyVariablesPDF($idOrder);
    }

    /**
     * Display Qr and Barcode on delivery PDF
     */
    public function hookDisplayPDFDeliverySlip($params) {
        $idOrder = $params['object']->id_order;
        return $this->getSmartyVariablesPDF($idOrder);
    }

    private function getSmartyVariablesPDF($idOrder) {
        $this->service->generateQr($idOrder);
        $this->service->generateBarcode($idOrder);
        $this->smarty->assign(
            [
                'url_code_qr'       => Configuration::get('SITE_URL').'img/codes/qr/'.$idOrder.".jpg",
                'url_code_barcode'  => Configuration::get('SITE_URL').'img/codes/barcode/'.$idOrder.".jpg",
                'id_order'          => $idOrder
            ]
        );
        return $this->display(__FILE__, 'order_reference_codes.tpl');
    } 

    private function installTab() {
        $languages = Language::getLanguages(false);

        //Main Parent menu
        if (!(int) Tab::getIdFromClassName('AdminMallhabana')) {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminMallhabana";
            foreach ($languages as $language) {
                $parentTab->name[$language['id_lang']] = 'MallHabana';
            }
            $parentTab->id_parent = 0;
            $parentTab->module = '';
            $parentTab->add();
        }

        if (!(int) Tab::getIdFromClassName('AdminMallhabanaOrders')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaOrders";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Pedidos');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'shopping_basket';
            $tab->module = $this->name;
            $tab->add();
        }

        if (!(int) Tab::getIdFromClassName('AdminMallhabanaProducts')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaProducts";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Products');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'receipt';
            $tab->module = $this->name;
            $tab->add();
        }

        if (!(int) Tab::getIdFromClassName('AdminMallhabanaConciliation')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaConciliation";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Conciliación');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'assessment';
            $tab->module = $this->name;
            $tab->add();
        }

        if (!(int) Tab::getIdFromClassName('AdminMallhabanaDespacho')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaDespacho";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Despacho Por Proveedor');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'assessment';
            $tab->module = $this->name;
            $tab->add();
        }

        if (!(int) Tab::getIdFromClassName('AdminMallhabanaDespachoCarrier')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaDespachoCarrier";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Despacho Por Transportista');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'assessment';
            $tab->module = $this->name;
            $tab->add();
        }
        
        if (!(int) Tab::getIdFromClassName('AdminMallhabanaPending')) {
            $parentTabID = Tab::getIdFromClassName('AdminMallhabana');
            $parentTab = new Tab($parentTabID);

            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminMallhabanaPending";
            $tab->name = array();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Pendientes confirmación');
            }
            $tab->id_parent = $parentTab->id;
            $tab->icon = 'get_app';
            $tab->module = $this->name;
            $tab->add();
        }
    }

    public function enable($force_all = false) {
        $this->installTab();
        return parent::enable($force_all);
    }

    public function disable($force_all = false) {
        return parent::disable($force_all) && $this->uninstallTab();
    }
    
    private function uninstallTab() {
        return true;
        $tabId = (int) Tab::getIdFromClassName('AdminMallhabana');
        if (!$tabId) {
            return true;
        }
        $tab = new Tab($tabId);
        return $tab->delete();
    }

    public function hookActionAdminOrdersListingFieldsModifier($params) {
        $params['fields']['total_shipping'] = array(
            'title' => $this->l('Transportación'),
            'align' => 'center',
        );     
        unset($params['fields']['new']);
        unset($params['fields']['pdf']);
        unset($params['fields']['owners']);

    }

    public function hookActionPaymentConfirmation($params) {
        $this->service->updateOrderOwner($params['id_order']);
    }

    public function HookDisplayBackOfficeHeader() {
        $this->context->controller->addJqueryUi('ui.datepicker');
    }

    public function hookDisplayBeforeCarrier ($params) {
        $products = $params['cart']->getProducts(true);
        $zona = $this->service->getZoneByAddresDelivery($params['cart']->id_address_delivery);
        $canDelivery = 0;
        if (!empty($zona)) {
            foreach ($products as $product) {
                $productObj = new Product($product['id_product'], 1);
                if($productObj->is_virtual == 1 || $this->service->canDeliveryToThisZone($productObj->id, $zona)) {
                    $canDelivery ++;
                }
            }
        } 
        echo '<input type="hidden" id="mallhabana_can_delivery" value="'. ((count($products) === $canDelivery) ? 1 : 0) . '"> 
        <script>
        document.ready(function(){
            if ($("#mallhabana_can_delivery").val() == "0") {
                $("div.delivery-options-list").html(\'<p class="alert alert-danger">Desafortunadamente, no disponemos de ningún transportista disponible para su dirección de envío.</p>\')
                $("#checkout-payment-step").remove();
            }
            if ($(".delivery-options-list p.alert.alert-danger").length > 0) {
                $("#checkout-payment-step").remove();
            }
        })
        </script>';
    }
   
}