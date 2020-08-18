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
        if (empty($idSupplier)) { 
            $suppliers =  Db::getInstance()->executeS('SELECT s.id_supplier FROM '._DB_PREFIX_.'supplier s');
            foreach($suppliers as $supplier) {
                $result[] = new Supplier($supplier["id_supplier"], 1);
            }
        } else {
            $result[] = new Supplier($idSupplier, 1);
        }
        return $result;
    }

    /**
     * Get carrier
     */
    public function getCarriers() {
        $result = [];
        $carriers = Db::getInstance()->executeS('SELECT c.id_carrier, c.name FROM '._DB_PREFIX_.'carrier c group by c.id_reference');
        foreach ($carriers as $carrier) {
            $result[] = new Carrier($carrier["id_carrier"]);
        }
        return $result;
    }

    /**
     * Conciliation column titles
     */
    public function conciliationHeaders() {
       return  
       ['id_order' => 'Id Order',
        'reference' => 'Referencia',
        'state_name' => 'Estado',
        'payment' => 'Método de pago',
        'municipio' => 'Municipio',
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
        ['id_order' => 'Id Orden',
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
    public function ordersAllProvidersByDate($month, $year, int $supplierId = null) {
        $supplierCondition = !empty($supplierId) ? " AND s.id_supplier = $supplierId" : "";
        $query = 'SELECT o.id_order as id_order, 
                    o.reference as reference,
                    osl.name as state_name,
                    o.payment,
                    st.name as municipio,
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
            INNER JOIN prstshp_state st ON (a.id_state = st.id_state) 
            LEFT JOIN prstshp_order_history AS oh ON (oh.id_order = o.id_order AND oh.id_order_state = 2)
            LEFT JOIN prstshp_currency AS cu ON (cu.id_currency = o.id_currency)
            LEFT JOIN prstshp_product AS p ON (p.id_product = od.product_id)
            LEFT JOIN prstshp_supplier AS s ON (s.id_supplier = p.id_supplier)
            LEFT JOIN
                (SELECT GROUP_CONCAT(prstshp_carrier.name) as carrier_name, id_order FROM prstshp_carrier INNER JOIN prstshp_orders ON (prstshp_carrier.id_carrier = prstshp_orders.id_carrier) GROUP BY prstshp_orders.id_order) AS carriers
                ON (carriers.id_order = o.id_order)
            WHERE o.current_state in (2,3,4,5)  AND YEAR(o.date_add) = "'.$year.'" AND MONTH(o.date_add) = "'.$month.'"'.$supplierCondition.'
            GROUP BY o.id_order';

        $result = [];
        $orders = Db::getInstance()->executeS($query);
        $suppliersFull = $this->getSuppliers($supplierId);
        $headers = $this->conciliationHeaders();

        foreach ($orders as $order) {
            $idOrder = $order['id_order'];
            foreach ($suppliersFull as $key => $supplier) {
                if (!empty($supplier->id)) {
                    $order = array_merge(array_slice($order, 0, 10), [
                        'supplier_total'.$key  => $this->getOrderTotalBySupplier( $idOrder, $supplier->id)
                    ], array_slice($order, 8));
                    $headers = array_merge(array_slice($headers, 0, 10), ['sp'.$key => $supplier->name], array_slice($headers, 8));
                }
            }
            $result[] = $order;
        }
        return ['orders' => $result, 'headers' => $headers];
    }

    public function getOrderTotalBySupplier ($idOrder, $idSupplier) {
        $query = 'SELECT FORMAT(SUM((od.original_wholesale_price * od.product_quantity)),2) as supplier_total
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order) 
            INNER JOIN prstshp_product AS p ON (p.id_product = od.product_id)
            INNER JOIN prstshp_supplier AS s ON (s.id_supplier = p.id_supplier AND s.id_supplier = '.$idSupplier.')
            WHERE od.id_order = '.$idOrder;
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

        $ownersIds = Db::getInstance()->executeS('SELECT DISTINCT po.id_owner, po.id_product, od.product_quantity, od.purchase_supplier_price  FROM prstshp_product_owner po
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
                $inserted[$ownerId['id_owner']][] = ['id_product' => $ownerId['id_product'], 'qty' => $ownerId['product_quantity']];
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
                    $detail = $this->getProductCustomization((int)$product['product_id'], $order->id_cart);
                    if (isset($detail['name']) && isset($detail['value'])) {
                        $detail = "</br><b>".$detail['name'].":</b> ".$detail['value']; 
                    } else {
                        $detail = "";
                    }

                    $htmlProduct = '<tr style="text-align: center;">
                                        <td style="width:25%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                '.$product['reference'].'
                                            </font>
                                        </td>
                                        <td style="width:35%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                <strong>'.$product['product_name'].'</strong></br>
                                                <b>Cantidad:</b> '. (int)$valueI['qty']." ".$detail.'
                                            </font>
                                        </td>
                                        <td style="width:20%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                <strong>'.number_format($product['purchase_supplier_price'], 2).'</strong>
                                            </font>
                                        </td>
                                        <td style="width:20%">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                <strong> '. number_format(((int)$valueI['qty']) * $product['purchase_supplier_price'], 2) .'</strong>
                                            </font>
                                        </td>
                                    </tr>';
                    $product_list[] = $htmlProduct;
                }
            }
            $product_list_html = '';
            if (count($product_list) > 0)
                $product_list_html = '<h3>Orden: '. $order->id_order.'</h3></br><table style="width:100%"><tr><th style="width:25%;border-bottom:1px solid #d6d4d4;">Referencia</th><th style="width:55%;border-bottom:1px solid #d6d4d4;">Productos</th><th style="width:20%;border-bottom:1px solid #d6d4d4;">Costo</th><th style="width:20%;border-bottom:1px solid #d6d4d4;">Total</th></tr>'.implode('', $product_list).'</table>';
            $mailParams = array(
                '{products}' => $product_list_html,
                '{employee}' => $employee->firstname.' '.$employee->lastname,
                '{customer_name}' => ' ',
                '{customer_mail}' => "",
                '{order_reference}' => $order->id_order
            );
            if($product_list_html != '')
                Mail::Send(
                    (int)$order->id_lang,
                    'employees_order',
                    sprintf(Mail::l('Productos de la orden %s', (int)$order->id_lang), $order->id_order),
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

    public function getOrdersByProviders ($supplier, $start, $end) {
        $data = Db::getInstance()->executeS('
        SELECT a.product_quantity_in_stock as available, SUM(a.product_quantity) AS qty, GROUP_CONCAT(CONCAT(a.id_order,"(", a.product_quantity, ")")) as orders, a.product_quantity_in_stock, a.product_id, a.product_name, a.product_reference, MAX(a.id_order_detail) as id_od
        FROM '._DB_PREFIX_.'order_detail a
        inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
        inner JOIN '._DB_PREFIX_.'product p ON (p.id_product = a.product_id)
        inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state) 
        WHERE os.id_order_state IN (2,3,4,5) AND p.id_supplier = '.(int)$supplier.' 
        AND DATE(o.date_add) >= "'.$start.'" AND DATE(o.date_add) <= "'.$end.'" GROUP BY a.product_id ORDER BY a.product_id');

        if (count(array_keys($data)) > 0 && array_keys($data)[0] == 'available') {
            $data = [$data];
        }
        $result = [];
        foreach ($data as $item) {
            $stock = Db::getInstance()->executeS('SELECT product_quantity_in_stock as stock from '._DB_PREFIX_.'order_detail where id_order_detail = '. $item['id_od']);
            $item["available"] = $stock;
            $result[] = $item;
        }
        return $result;
    }

    public function getOrdersByProvidersIDs ($supplier, $start, $end) {        
        $data = Db::getInstance()->executeS('
        SELECT DISTINCT a.id_order 
        FROM '._DB_PREFIX_.'order_detail a
        inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
        inner JOIN '._DB_PREFIX_.'product p ON (p.id_product = a.product_id)
        inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state) 
        WHERE os.id_order_state IN (2,3,4,5) AND p.id_supplier = '.(int)$supplier.' 
        AND DATE(o.date_add) >= "'.$start.'" AND DATE(o.date_add) <= "'.$end.'" 
        ORDER BY a.id_order ASC');
        
        $result = [];
        foreach ($data as $item) {            
            $result[] = $item['id_order'];
        } 
        return $result;      
    }

    public function getOrdersProductsByCarrier ($carrierId, $date) {
        return Db::getInstance()->executeS('
        SELECT  a.product_name,
                a.product_quantity,
                a.product_reference,
                a.product_id,
                a.id_order
        FROM '._DB_PREFIX_.'order_detail a
        inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
        inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state) 
        inner JOIN '._DB_PREFIX_.'order_carrier oc ON (oc.id_order = a.id_order) 
        WHERE os.id_order_state IN (2,3,4,5) AND oc.id_carrier = '.(int)$carrierId.' 
        AND DATE(o.date_add) = "'.$date.'"');
    }

    public function getProductCustomization ($id_product, $id_cart) {
        return Db::getInstance()->executeS('SELECT cd.value, cfl.name FROM prstshp_customization c
        INNER JOIN prstshp_customized_data cd ON cd.id_customization = c.id_customization
        INNER JOIN prstshp_customization_field cf ON cf.id_product = c.id_product
        INNER JOIN prstshp_customization_field_lang cfl ON cfl.id_customization_field = cf.id_customization_field
        WHERE cfl.id_lang = 1 AND c.id_product = '.(int)$id_product.' AND c.id_cart = '.(int)$id_cart);
    }

    public function getProductsByOrderAndSupplier ($supplier, $order) {        
        $data = Db::getInstance()->executeS('
        SELECT a.* 
        FROM '._DB_PREFIX_.'order_detail a
        inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
        inner JOIN '._DB_PREFIX_.'product p ON (p.id_product = a.product_id)
        inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state) 
        WHERE o.id_order = '.(int)$order.' AND p.id_supplier = '.(int)$supplier);
        
        return $data;      
    }

}