<?php

class HTMLTemplateDespachoCarrier extends HTMLTemplate
{
	public $orders;
	public $provider;

	public function __construct($orders, $smarty)
	{
		$this->orders = $orders;
		$this->smarty = $smarty;

		// header info
		$id_lang = Context::getContext()->language->id;
		$this->title ='Despacho Transportista';
		// footer info
		$this->shop = new Shop(Context::getContext()->shop->id);
	}

	/**
	 * Returns the template's HTML content
	 * @return string HTML content
	 */
	public function getContent()
	{
		$this->smarty->assign([
			'orders' => $this->orders
		]);
		return $this->smarty->fetch(_PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/pdf_despacho_carrier.tpl');
	}

	/**
	 * Returns the template filename
	 * @return string filename
	 */
	public function getFilename()
	{
		return date('YmdHis').'-DespachoTransportista.pdf';
	}

	/**
	 * Returns the template filename when using bulk rendering
	 * @return string filename
	 */
	public function getBulkFilename()
	{
		return date('YmdHis').'-DespachoTransportista.pdf';
	}

	public function getFooter()
	{
		return $this->smarty->fetch(_PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/footer.tpl');
	}

	public function getHeader()
	{
		$this->smarty->assign(array(
			'title' => 'MallHabana.com',
			'logo_path' => Configuration::get('SITE_URL').'img/logo_invoice.jpg'
		));
 
		return $this->smarty->fetch(_PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/header.tpl');
	}
}