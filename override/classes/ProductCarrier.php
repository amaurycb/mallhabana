<?php 
/**
 * Modelo para acceder al dato de los transportistas de un producto.
 */
class ProductCarrier extends ObjectModel {	
    public $id_product;
	public $id_carrier;
	public static $definition = [
        'table' => 'product_carrier',
        'primary' => 'id_product',
        'fields' =>[
        'id_product' => array('type' => self::TYPE_INT, 'required' => true),
        'id_carrier_reference' => array('type' => self::TYPE_INT,  'required' => true)
    ]];
	protected $webserviceParameters = array();
}