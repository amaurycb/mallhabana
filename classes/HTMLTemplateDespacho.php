<?php

class HTMLTemplateDespacho extends HTMLTemplate
{
	public $products;
	public $provider;

	public function __construct($products, $smarty)
	{
		$this->products = $products;
		$this->smarty = $smarty;

		// header informations
		$id_lang = Context::getContext()->language->id;
		$this->title ='Despacho';
		// footer informations
		$this->shop = new Shop(Context::getContext()->shop->id);
	}

	/**
	 * Returns the template's HTML content
	 * @return string HTML content
	 */
	public function getContent()
	{
		$this->smarty->assign([
			'products' => $this->products
		]);
		return $this->smarty->fetch(_PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/pdf_despacho.tpl');
	}

	/**
	 * Returns the template filename
	 * @return string filename
	 */
	public function getFilename()
	{
		return date('YmdHis').'-Despacho.pdf';
	}

	/**
	 * Returns the template filename when using bulk rendering
	 * @return string filename
	 */
	public function getBulkFilename()
	{
		return date('YmdHis').'-Despacho.pdf';
	}

	public function getFooter()
	{
		return $this->smarty->fetch(_PS_MODULE_DIR_ . 'mallhabana/views/templates/admin/footer.tpl');
	}
}