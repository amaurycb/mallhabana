<?php
class MallHabanaService {
    /**
     * Generate QR code
     *  
     * @param string $text
     * @param boolean $getRaw
     * 
     * @return mixed
     */
    public function generateQr($text, $getRaw = false){
        $url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$text."&choe=UTF-8";
        $img = $_SERVER['DOCUMENT_ROOT']."/img/codes/qr/".$text.".jpg";
        $raw = file_get_contents($url);
        if (!file_exists($img)){ 
            if ($getRaw) {
                return $raw;
            } else {
                file_put_contents($img, $raw);  
            }    
        }       
        return true;
    }

    /**
     * Generate Barcode
     *  
     * @param string $text
     * @param boolean $getRaw
     * 
     * @return mixed
     */
    public function generateBarcode($text, $getRaw = false){
        $url = "http://barcodes4.me/barcode/c39/".$text.".jpg";
        $img = $_SERVER['DOCUMENT_ROOT']."/img/codes/barcode/".$text.".jpg";
        if (!file_exists($img)){ 
            $raw = file_get_contents($url);
            if ($getRaw) {
                return $raw;
            } else {           
                file_put_contents($img, $raw);  
            } 
        }
        return true;
    }

    /**
     * Get Destiny info by product id
     *  
     * @param int $idProduct
     * 
     * @return string
     */
    public function getDestinyInfo($idProduct){
        $carriers = (Db::getInstance())->executeS((new DbQuery())
        ->from('product_carrier', 'pc')
        ->innerJoin('carrier', 'c', 'c.id_reference = pc.id_carrier_reference')
        ->where("c.active = 1")
        ->where("c.deleted = 0")
        ->where("pc.id_product = ".(int)$idProduct));
        
        if (count($carriers) == 0) {
             $carriers = (Db::getInstance())->executeS((new DbQuery())
            ->from('carrier', 'c')
            ->where("c.active = 1")        
            ->where("c.deleted = 0"));
        }

        $destiniesAvalilable = $this->getZoneByCarrier($carriers);
        return implode(', ', $destiniesAvalilable);
    }

    private function getZoneByCarrier($carriers) {
        $zones = [];
        foreach ($carriers as $carrier) {           
            $zonesAvailables = (Db::getInstance())->executeS((new DbQuery())
                            ->from('carrier_zone', 'cz')
                            ->innerJoin('zone', 'z', 'z.id_zone=cz.id_zone')
                            ->where('cz.id_carrier = ' . (int)$carrier['id_carrier'])
                            ->where("z.active = 1"));

            if (count($zonesAvailables) > 0) {
                foreach ($zonesAvailables as $zone) {
                    $zones[] = $zone['name'];
                }
            }
        }
        return array_unique($zones);
    }

     /**
     * Redirect with messages
     */
    public function redirectWithNotifications(array $messages, $url) {
        $notifications = json_encode($messages);

        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        } elseif (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['notifications'] = $notifications;
        } else {
            setcookie('notifications', $notifications);
        }

        return call_user_func_array(array('Tools', 'redirect'), [$url]);
    }

    /**
     * Print Conciliation
     */
    public function excelConciliation ($orders) {
        
        $timestamp = time();
        $filename = 'Export_' . $timestamp . '.xls';
        
        header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
        header("Content-Disposition: attachment; filename=$filename");
        
        echo implode("\t", array_values()) . "\n";

        foreach ($orders as $row) {
            echo implode("\t", array_values($row)) . "\n";
        }        
        exit();
    }

    public function excel ($headers, $values) {
        $timestamp = time();
        $filename = 'Export_' . $timestamp . '.xls';
        
        header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
        header("Content-Disposition: attachment; filename=$filename");
        
        echo implode("\t", array_values($headers)) . "\n";

        foreach ($values as $row) {
            echo implode("\t", array_values($row)) . "\n";
        }        
        exit();
    }

    /**
     * Get suppliers
     */
    public function getSuppliers($idSupplier = null) {
        $result = [];
        $suppliers = new Collection('ProductSupplier');
        $suppliers->groupBy('id_supplier');
        if (empty($idSupplier)) {            
            foreach($suppliers as $supplier) {
                $result[] = new Supplier($supplier->id_supplier, $this->context->language->id);
            }
        } else {
            $result[] = new Supplier($idSupplier, $this->context->language->id);
        }
        return $result;
    }

    /**
     * Conciliation column titles
     */
    public function conciliationHeaders() {
       return  
       ['reference' => 'Referencia',
        'state_name' => 'Estado',
        'payment' => 'Método de pago',
        'client' => 'Cliente',
        'destiny' => 'Destinatario',
        'created_at' => 'Creado el',
        'aproved' => 'Aprobado el',
        'paid' => 'Total pagado',
        'carrier_name' => 'Transportista',
        'shipping' => 'Total Transportación',
        'embalaje' => 'Embalaje',
        'gain' => 'Utilidad',
        'currency' => 'Moneda',
        'rate' => 'Cambio'];
    }

    /**
     * get orders by provider and date
     */
    public function ordersBySate($state) {
        $query = 'SELECT o.reference as reference, 
                    o.date_add as created_at,
                    cu.iso_code as currency,
                    FORMAT(o.total_paid,2) AS paid,
                    carriers.carrier_name,
                    CONCAT (c.firstname, " ", c.lastname) as client,
                    CONCAT (a.firstname, " ", a.lastname) as destiny
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order)
            LEFT JOIN prstshp_customer c ON (c.id_customer = o.id_customer) 
            LEFT JOIN prstshp_address a ON (a.id_address = o.id_address_delivery) 
            LEFT JOIN prstshp_supplier AS s ON (s.id_supplier = od.product_supplier_reference)
            LEFT JOIN prstshp_currency AS cu ON (cu.id_currency = o.id_currency)
            LEFT JOIN
                (SELECT GROUP_CONCAT(prstshp_carrier.name) as carrier_name, id_order FROM prstshp_carrier INNER JOIN prstshp_orders ON (prstshp_carrier.id_carrier = prstshp_orders.id_carrier) GROUP BY prstshp_orders.id_order) AS carriers
                ON (carriers.id_order = o.id_order)
            WHERE o.current_state in ('.implode(',',$state).')
            GROUP BY o.id_order';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Pending column titles
     */
    public function pendingHeaders() {
        return  
        ['reference' => 'Referencia',
        'created_at' => 'Creado el',
        'currency' => 'Moneda',
        'paid' => 'Total pagado',
        'carrier_name' => 'Transportista',
        'client' => 'Cliente',
        'destiny' => 'Destinatario'];
     }

     /**
     * get orders by provider and date
     */
    public function ordersAllProvidersByDate($month, $year, $supplierId = 0) {
        $supplierCondition = !empty($supplierCondition) ? " AND s.id_supplier = $supplierId" : "";
        $query = 'SELECT o.reference as reference, 
                    osl.name as state_name,
                    o.payment,
                    CONCAT (c.firstname, " ", c.lastname) as client,
                    CONCAT (a.firstname, " ", a.lastname) as destiny,
                    o.date_add as created_at,
                    oh.date_add as aproved,
                    FORMAT(o.total_paid,2) AS paid,
                    carriers.carrier_name,
                    FORMAT(o.total_shipping, 2) as shipping,
                    "Pendiente" as embalaje,
                    FORMAT(o.total_paid - SUM(od.total_price_tax_excl) - o.total_shipping, 2) as gain,
                    cu.iso_code as currency,
                    FORMAT(o.conversion_rate, 2) AS rate 
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order)
            INNER JOIN prstshp_order_state_lang osl ON (osl.id_order_state = o.current_state AND osl.id_lang = 1  ) 
            INNER JOIN prstshp_customer c ON (c.id_customer = o.id_customer) 
            INNER JOIN prstshp_address a ON (a.id_address = o.id_address_delivery) 
            LEFT JOIN prstshp_order_history AS oh ON (oh.id_order = o.id_order AND oh.id_order_state = 2)
            LEFT JOIN prstshp_currency AS cu ON (cu.id_currency = o.id_currency)
            LEFT JOIN prstshp_supplier AS s ON (s.id_supplier = od.product_supplier_reference '.$supplierCondition.')
            LEFT JOIN
                (SELECT GROUP_CONCAT(prstshp_carrier.name) as carrier_name, id_order FROM prstshp_carrier INNER JOIN prstshp_orders ON (prstshp_carrier.id_carrier = prstshp_orders.id_carrier) GROUP BY prstshp_orders.id_order) AS carriers
                ON (carriers.id_order = o.id_order)
            WHERE o.current_state in (2,3,4,5)  AND YEAR(o.date_add) = "'.$year.'" AND MONTH(o.date_add) = "'.$month.'"
            GROUP BY o.id_order';

        $result = [];
        $orders = Db::getInstance()->executeS($query);
        $suppliersFull = $this->getSuppliers($supplierId);
        $headers = $this->conciliationHeaders();

        foreach ($orders as $order) {
            $idOrder = explode('-',$order['reference']);
            foreach ($suppliersFull as $key => $supplier) {
                $order = array_merge(array_slice($order, 0, 8), [
                    'supplier_total'.$key  => $this->getOrderTotalBySupplier($idOrder[0], $supplier->id_supplier)
                ], array_slice($order, 8));
                $headers = array_merge(array_slice($headers, 0, 8), ['sp'.$key => $supplier->name], array_slice($headers, 8));
            }
            $result[] = $order;
        }
        return ['orders' => $result, 'headers' => $headers];
    }

    public function getOrderTotalBySupplier ($idOrder, $idSupplier) {
        $query = 'SELECT 
                    FORMAT(SUM(od.product_price * od.product_quantity),2) as supplier_total
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order) 
            INNER JOIN prstshp_supplier AS s ON (s.id_supplier = od.product_supplier_reference AND s.id_supplier = '.$idSupplier.')
            WHERE od.id_order = '.$idOrder.'  GROUP BY s.id_supplier';
        $data = Db::getInstance()->executeS($query);
        return !empty($data[0]['supplier_total']) ? $data[0]['supplier_total'] : '0.00';
    }

    public function getOrderByCartId ($id_cart) {
        $query = 'SELECT id_order
                FROM prstshp_orders AS o
                WHERE o.id_cart = '.$id_cart;
        return Db::getInstance()->executeS($query);
    }

    public function updateOrderOwner($orderId) {
        Db::getInstance()->execute('DELETE FROM prstshp_order_owner WHERE id_order = '.(int)$orderId);
        Db::getInstance()->execute('UPDATE prstshp_orders SET owners = "" WHERE id_order = '.(int)$orderId);

        $ownersIds = Db::getInstance()->executeS('SELECT DISTINCT po.id_owner, po.id_product, od.product_quantity  FROM prstshp_product_owner po
                        INNER JOIN prstshp_order_detail od ON od.product_id = po.id_product
                        WHERE od.id_order = '.(int)$orderId);
        $ownerNames = [];
        $inserted = [];
        $order = new Order($orderId);
        foreach ($ownersIds as $ownerId) {
            $employee = new Employee((int)$ownerId['id_owner']);
            if(!in_array($ownerId['id_owner'], array_keys($inserted))) {                
                Db::getInstance()->execute('INSERT INTO prstshp_order_owner (id_order, id_owner) VALUES ('.(int)$orderId.', '.(int)$ownerId['id_owner'].')');
                $ownerNames[] = $employee->firstname.' '.$employee->lastname;
                $inserted[$ownerId['id_owner']] = ['id_product' => $ownerId['id_product'], 'qty' => $ownerId['product_quantity']];
            }
        }

        // if(count(array_unique($ownerNames)) > 0) {
            $owners = implode(", ", array_unique($ownerNames));
            Db::getInstance()->execute('UPDATE prstshp_orders SET owners = "'.$owners.'" WHERE id_order = '.(int)$orderId);
        // }
        
        foreach($inserted as $keyI => $valueI){
            $employee = new Employee((int)$keyI);
            $product_list = array();
            foreach ($order->getProducts() as $product) {
                if((int)$product['product_id'] == (int)$valueI['id_product']) {

                    $htmlProduct = '<tr style="text-align: center;">
                                        <td style="width:25%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                '.$product['reference'].'
                                            </font>
                                        </td>
                                        <td style="width:75%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                <strong>'.$product['product_name'].' (Cantidad: '. (int)$valueI['qty'].')</strong>
                                            </font>
                                        </td>
                                    </tr>';
                    $product_list[] = $htmlProduct;
                }
            }
            $product_list_html = '';
            if (count($product_list) > 0)
                $product_list_html = '<table style="width:100%"><tr><th style="width:25%;border-bottom:1px solid #d6d4d4;">Referencia</th><th style="width:75%;border-bottom:1px solid #d6d4d4;">Productos</th></tr>'.implode('', $product_list).'</table>';
            $mailParams = array(
                '{products}' => $product_list_html,
                '{employee}' => $employee->firstname.' '.$employee->lastname,
                '{customer_name}' => ' ',
                '{customer_mail}' => "",
                '{order_reference}' => $order->reference
            );
            if($product_list_html != '')
                Mail::Send(
                    (int)$order->id_lang,
                    'employees_order',
                    sprintf(Mail::l('MallHabana. Productos de la orden %s', (int)$order->id_lang), $order->reference),
                    $mailParams,
                    $employee->email,
                    $employee->firstname.' '.$employee->lastname,
                    null, null, null, null, dirname(__FILE__).'/views/mails/', false, (int)$order->id_shop
            );
        }

    }

    public function getProductOwnerByOwner ($id_owner) {
        $query = 'SELECT p.*
                FROM prstshp_product_owner AS po
                INNER JOIN prstshp_product p ON p.id_product = po.id_product
                WHERE po.id_owner = '.$id_owner;
        return Db::getInstance()->executeS($query);
    }

}