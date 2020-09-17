<?php
class WebserviceRequest extends WebserviceRequestCore {
    public static function getResources() { 
        $resources = parent::getResources();
        $resources['product_carrier'] = array('description' => 'Product Carrier', 'class' => 'ProductCarrier');
        ksort($resources);
        return $resources;
    }
}