<?php

require_once dirname(__FILE__) . '/../../classes/MallHabanaService.php';

/**
 * Class CubacelBackController
 */
class AdminMallhabanaPendingController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;
        $this->service = new MallHabanaService();
    }

    public function initContent() {
        parent::initContent();
        $orders = $this->service->ordersBySate([3]);        
        $headers = $this->service->pendingHeaders();
        $this->service->excel($headers, $orders);
    }


}
