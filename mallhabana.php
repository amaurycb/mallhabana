<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mallhabana extends Module {
    
    public function __construct() {
        $this->name = 'mallhabana';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Yolanda Mauri Pérez';
        $this->need_instance = 0;
        $this->ps_versions_compliancy =  ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

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
            // $this->registerHook('header') &&
            // $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('filterProductSearch') &&
            $this->registerHook('loadProductPage') &&
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
        try {         
            $product = new Product(Tools::getValue('id_product'));
            //Redirect if there is not stock
            if ($product->quantity == 0) {
                setcookie("mallhabana_stock_redirection", str_replace(" ", "&nbsp", Configuration::get('NO_STOCK_MESSAGE')), time()+ 8 );
                Tools::redirect(Configuration::get('NO_STOCK_REDIRECTION'));
            }
            //Specify another product data
            //die('Disponible para toda Cuba');
        } catch (Exception $e) {
           $this->logger->logDebug($e->getMessage()); 
            return false;
        }         
    }

    //Filter product search
    public function hookFilterProductSearch($params) {
        $this->logger->logDebug($params); 
    }
    
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader() {
        $this->context->controller->addJS($this->_path.'views/js/front.js');
    }

   
}