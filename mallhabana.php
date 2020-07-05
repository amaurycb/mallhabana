<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__. '/classes/Service.php';

class Mallhabana extends Module {
    
    public function __construct() {
        $this->name = 'mallhabana';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Yolanda Mauri Pérez';
        $this->need_instance = 0;
        $this->ps_versions_compliancy =  ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->service = new Service();

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
    
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayLeftColumnProduct') &&
            $this->registerHook('filterProductSearch') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            Configuration::updateValue('MALLHABANA', 'Funciones complementarias para MallHabana.com');
    }

    public function uninstall() {      
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
                [
                    'type' => 'text',
                    'label' => $this->l('Redirección Productos sin Stock'),
                    'name' => 'MALLHABANA[NO_STOCK_REDIRECTION]',
                    'required' => true,
                    'cols' => 2,
                    'prefix' => '<i class="icon-link"></i>'
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Mensaje Productos sin Stock'),
                    'name' => 'MALLHABANA[NO_STOCK_MESSAGE]',
                    'required' => true,
                    'cols' => 2,
                ]
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
            'MALLHABANA[NO_STOCK_MESSAGE]'          => Configuration::get('NO_STOCK_MESSAGE')
        ];

        return $helper->generateForm($fieldsForm);
    }

    //Show aditional info on product view
    public function hookDisplayLeftColumnProduct($params) { 
        $languageId = (int)($params['cookie']->id_lang);
        try {         
            $product = new Product(Tools::getValue('id_product'));
            $hasQty = $product->quantity > 0;

            $attributes = $product->getAttributesResume($languageId);
            foreach ($attributes as $attribute) {
                $id_product_attribute = $attribute['id_product_attribute'];
                $qty = StockAvailable::getQuantityAvailableByProduct((int) Tools::getValue('id_product'), $id_product_attribute);
                $hasQty = $hasQty ? : $qty > 0;
            }

            if (!$hasQty) {
                $this->warning[] = Tools::displayError("<b>".$product->name[$languageId]."</b>. ".Configuration::get('NO_STOCK_MESSAGE'));
                $this->redirectWithNotifications(Configuration::get('NO_STOCK_REDIRECTION'));
            }

            //Specify another product data
            //die('Disponible para toda Cuba');
        } catch (Exception $e) {
           $this->logger->logDebug($e->getMessage()); 
            return false;
        }         
    }

    /**
     * Redirect with messages
     */
    private function redirectWithNotifications()
    {
        $notifications = json_encode(array(
            'error' => $this->errors,
            'warning' => $this->warning,
            'success' => $this->success,
            'info' => $this->info,
        ));

        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        } elseif (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['notifications'] = $notifications;
        } else {
            setcookie('notifications', $notifications);
        }

        return call_user_func_array(array('Tools', 'redirect'), func_get_args());
    }

    //Filter product search
    public function hookFilterProductSearch($params) {
        // $searchVariables = $params['searchVariables'];
        $products = $params['searchVariables']['result']->getProducts();
        $filter = [];

        foreach($products as $product) {
            if ((int) $product['quantity'] > 0) {
                $filter[] = $product;
            }
        }
        $params['searchVariables']['result']->setProducts($filter)->setTotalProductsCount(count($filter));
        return $params['searchVariables'];
        // var_dump($searchVariables);die;

        // return $params['searchVariables']['result'];
        // $this->logger->logDebug($params); 
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

   
}