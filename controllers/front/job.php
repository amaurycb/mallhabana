<?php
/**
 * Cron job for disable out of stock products.
 * 
 */

class MallHabanaJobModuleFrontController extends ModuleFrontController {
    
    public function postProcess() {
        Db::getInstance()->execute('UPDATE prstshp_product_shop SET active=0 WHERE id_product IN (SELECT id_product FROM prstshp_stock_available WHERE quantity< 4)');
        // Db::getInstance()->execute('UPDATE prstshp_product_shop SET active=0 WHERE id_product IN (SELECT id_product FROM prstshp_stock_available WHERE quantity<0)');
        // Db::getInstance()->execute('UPDATE prstshp_product_shop SET active=0 WHERE id_product IN (SELECT id_product FROM prstshp_product WHERE quantity=0);');
        die('done');
    }
 }