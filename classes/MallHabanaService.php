<?php
class MallHabanaService {
    /**
     * Generar código QR para las facturas de las órdenes
     *  
     * @param string $text
     * @param boolean $getRaw
     * 
     * @return mixed
     */
    public function generateQr($text, $getRaw = false){
        clearstatcache();
      
        $img = $_SERVER['DOCUMENT_ROOT']."/img/codes/qr/".$text.".jpg";
        ////$url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$text."&choe=UTF-8";
        ////$raw = file_get_contents($url);


        $sizeQRcode = -1;                   
        $existFileQRcode = is_file($img) && file_exists($img);
        
        if($existFileQRcode)
         $sizeQRcode = filesize($img); 

        $validFileQRCode = $existFileQRcode && $sizeQRcode>'0' ? true : false;   

        $logger = new FileLogger(0);
        $urlLog = _PS_ROOT_DIR_."/log/despachoCarrierFixed.log";
        $logger->setFilename($urlLog);
                     
        if (!$validFileQRCode){ 
           $url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$text."&choe=UTF-8";
           $raw = file_get_contents($url);
           ////if (!file_exists($img)){ 
            if ($getRaw) {
                return $raw;  
            } else {
                file_put_contents($img, $raw); 
                $logger->logDebug("Codigo QR orden: ". $text." generado OK");   
            }    
        }      
        return true;
    }

    /**
     *  Generar código de barra para las facturas de las órdenes
     *  
     * @param string $text
     * @param boolean $getRaw
     * 
     * @return mixed
     */
    public function generateBarcode($text, $getRaw = false){
        clearstatcache();
        //$barcodeobj = new TCPDFBarcode($text, 'C39');
        $barcodeobj = new TCPDFBarcode($text, 'C128');
        // Assign to template
        // All other unnecessary variables not displayed      
        $barcode = $barcodeobj->getBarcodePngData(2, 40, array(0,0,0));

        ////$url = "http://barcodes4.me/barcode/c39/".$text.".jpg";
        //$img = $_SERVER['DOCUMENT_ROOT']."/img/codes/barcode/".$text.".jpg";
        $img = $_SERVER['DOCUMENT_ROOT']."/img/codes/barcode/".$text.".png";
        /*if (!file_exists($img)){ 
            $raw = file_get_contents($url);
            if ($getRaw) {
                return $raw;
            } else {           
                file_put_contents($img, $raw);  
            } 
        }*/

        $sizeBARcode = -1;                   
        $existFileBARcode = is_file($img) && file_exists($img);
        if($existFileBARcode)
           $sizeBARcode = filesize($img); 
      
        $validFileBARCode = $existFileBARcode && $sizeBARcode>'0' ? true : false;      

        $logger = new FileLogger(0);
        $urlLog = _PS_ROOT_DIR_."/log/despachoCarrierFixed.log";
        $logger->setFilename($urlLog);
          
        if (!$validFileBARCode){          
            ////if (!file_exists($img)){ 
            $raw = $barcode;
            if ($getRaw) {
                return $raw;
            } else {              
                file_put_contents($img, $barcode);  
            } 
        } 


        return true;
    }

    /**
     * Generar excel de Conciliación con las cabeceras correspondientes
     * 
     * @param mixed $orders
     * @return void
     */
    public function excelConciliation ($orders) {
        
        $timestamp = time();
        $filename = 'Export_' . $timestamp . '.xls';
        
        header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
        header("Content-Disposition: attachment; filename=$filename");
        
        echo implode("\t", array_values()) . "\n";

        foreach ($orders as $row) {
            echo utf8_decode(implode("\t", array_values($row))) . "\n";
        }        
        exit();
    }

    /**
     * Generar excel con los pendientes de confirmación.
     * 
     * Array con los títulos de las columnas
     * @param mixed $headers
     * 
     * Array con los datos de la conciliación
     * @param mixed $values
     * 
     * @return void
    */
    public function excel ($headers, $values) {
        $timestamp = time();
        $filename = 'Export_' . $timestamp . '.xls';
        
        header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
        header("Content-Disposition: attachment; filename=$filename");
        
        echo implode("\t", array_values($headers)) . "\n";

        foreach ($values as $row) {
            echo implode("\t", array_values($row)) . "\n";
            //echo utf8_decode(implode("\t", array_values($row))) . "\n";
        }        
        exit();
    }

    /**
     * Obtener el listado de proveedores para ser mostrados en la vista de Despacho por Proveedor
     * 
     * @param mixed $idSupplier
     * 
     * @return mixed
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
     * Obtener el listado de transportistas para ser mostrados en la vista de Despacho por Transportistas
     * 
     * @return mixed
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
     * Título de las columnas del excel de la conciliación.
     * 
     * @return mixed
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
     * ConciliationAccounting column titles
     */
    public function conciliationAccountingHeaders() {
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
         'precio_costo_total' => 'Precio Costo Total',     
         'paid_without_shipping' => 'Precio Venta Total',        
         'carrier_name' => 'Transportista',
         'shipping' => 'Total Transportación',
         'embalaje' => 'Embalaje',
         'gain' => 'Utilidad',
         'currency' => 'Moneda',
         'rate' => 'Cambio'];
     }

    /**
     * Obtener las órdenes por proveedor, para el excel de la conciliación. 
     * 
     * @param mixed $state
     * @return mixed 
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
            WHERE o.current_state in ('.implode(',',$state).') AND o.date_add >= "2020-08-09"
            GROUP BY o.id_order';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Título de las columnas del excel de las órdenes pendientes de connfirmación.
     * @return mixed
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
     * Listado de órdenes por poveedor, mes y año, para exportar el excel.
     * @param string $month
     * @param string $year
     * @param int $supplierId
     * @return mixed 
     * 
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
            WHERE o.current_state in (2,3,4,5,20)  AND YEAR(o.date_add) = "'.$year.'" AND MONTH(o.date_add) = "'.$month.'"'.$supplierCondition.'
            GROUP BY o.id_order';

        $result = [];
        $orders = Db::getInstance()->executeS($query);
        $suppliersFull = $this->getSuppliers($supplierId);
        $headers = $this->conciliationHeaders();

        /**
         * Formatear el arreglo órdenes. 
         * Insertar cada columna de proveedor a partir de la columna número 10.
         */
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


     //Custom Bradis
     /**
     * get orders by providerAccounting and date
     */
    public function ordersAllProvidersAccountingByDate($month, $year, int $supplierId = null) {
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
                    FORMAT(o.total_products,2) AS PrecioVentaTotal,                    
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
        $headers = $this->conciliationAccountingHeaders();
          
        foreach ($orders as $order) {
            $totalPriceCost = 0;
            /*foreach ($order->getProducts() as $product) {
                //$totalPriceCost+= $product->
                $temp_product = $product;
            }*/
             

            $idOrder = $order['id_order'];
            //$order['totalPriceCost'] = $this->getOrderTotalPriceCost( $idOrder);
          
            $order = array_merge(
                array_slice($order, 0, 10),
                ['totalPriceCost' => $this->getOrderTotalPriceCost( $idOrder)], 
                array_slice($order, 10));

            //$stop = 0;
            foreach ($suppliersFull as $key => $supplier) {
                //$stop++;
                if (!empty($supplier->id)) {
                    //$arraySliceOrder = array_slice($order, 0, 12);
                    //$arraySliceOrder2 = array_slice($order, 12);
                    $order = array_merge(
                        array_slice($order, 0, 12),
                        ['supplier_total'.$key  => $this->getOrderTotalBySupplier( $idOrder, $supplier->id)
                        ], array_slice($order, 12));
                  
                    $headers = array_merge(
                        array_slice($headers, 0, 12), 
                        ['spc'.$key => $supplier->name.'(PrecioCoste)'],
                        array_slice($headers, 12));
                        
                    //Custom Brakus
                   $order = array_merge(
                        array_slice($order, 0, 12),
                        ['supplier_total_sell'.$key  => $this->getOrderTotalBySupplierSell( $idOrder, $supplier->id)
                        ], array_slice($order, 12));
                    $headers = array_merge(
                        array_slice($headers, 0, 12), 
                        ['spv'.$key => $supplier->name.'(PrecioVenta)'],
                        array_slice($headers, 12));
                    
                }
                /*if ($stop==30)
                break;*/
            }
            $result[] = $order;
        }
        return ['orders' => $result, 'headers' => $headers];
    }

    /**
     * Obtener monto total de una orden por proveedor.
     * @param int $idOrder
     * @param int $idSupplier
     * 
     * @return string
     */
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

    //Custom Bradis
    public function getOrderTotalBySupplierSell ($idOrder, $idSupplier) {
        $query = 'SELECT FORMAT(SUM((od.original_product_price * od.product_quantity)),2) as supplier_total
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order) 
            INNER JOIN prstshp_product AS p ON (p.id_product = od.product_id)
            INNER JOIN prstshp_supplier AS s ON (s.id_supplier = p.id_supplier AND s.id_supplier = '.$idSupplier.')
            WHERE od.id_order = '.$idOrder;
        $data = Db::getInstance()->executeS($query);
        return !empty($data[0]['supplier_total']) ? $data[0]['supplier_total'] : '0.00';
    }

    public function getOrderTotalPriceCost ($idOrder) {
        $query = 'SELECT FORMAT(SUM((od.original_wholesale_price * od.product_quantity)),2) as total_price_cost
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order) 
            INNER JOIN prstshp_product AS p ON (p.id_product = od.product_id)          
            WHERE od.id_order = '.$idOrder;
        $data = Db::getInstance()->executeS($query);
        return !empty($data[0]['total_price_cost']) ? $data[0]['total_price_cost'] : '0.00';
    }

    /**
     * Obtener el id de la orden 
     * @param int $id_cart
     * 
     * @return mixed
     */
    public function getOrderByCartId ($id_cart) {
        $query = 'SELECT id_order
                FROM prstshp_orders AS o
                WHERE o.id_cart = '.$id_cart;
        return Db::getInstance()->executeS($query);
    }

    /**
     * Actualizar el propietario de la orden según los proveedores de los productos que contiene 
     * Notificar por correo a los propietarios implicados
     * 
     * TODO: Notificación email no se envía 
     * @param int $orderId
     * 
     * @return void
     */
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
            //TODO: uncomment and fix send email funcionality
            // if (count($product_list) > 0)
            //     $product_list_html = '<h3>Orden: '. $order->id_order.'</h3></br><table style="width:100%"><tr><th style="width:25%;border-bottom:1px solid #d6d4d4;">Referencia</th><th style="width:55%;border-bottom:1px solid #d6d4d4;">Productos</th><th style="width:20%;border-bottom:1px solid #d6d4d4;">Costo</th><th style="width:20%;border-bottom:1px solid #d6d4d4;">Total</th></tr>'.implode('', $product_list).'</table>';
            // $mailParams = array(
            //     '{products}' => $product_list_html,
            //     '{employee}' => $employee->firstname.' '.$employee->lastname,
            //     '{customer_name}' => ' ',
            //     '{customer_mail}' => "",
            //     '{order_reference}' => $order->id_order
            // );
            // if($product_list_html != '')
            //     Mail::Send(
            //         (int)$order->id_lang,
            //         'employees_order',
            //         sprintf(Mail::l('Productos de la orden %s', (int)$order->id_lang), $order->id_order),
            //         $mailParams,
            //         $employee->email,
            //         $employee->firstname.' '.$employee->lastname,
            //         null, null, null, null, dirname(__FILE__).'/views/mails/', false, (int)$order->id_shop
            // );
        }

    }

    /**
     * Obtener los productos de un propietario
     * @param int $id_owner
     * @return mixed
     */
    public function getProductOwnerByOwner ($id_owner) {
        $query = 'SELECT p.*
                FROM prstshp_product_owner AS po
                INNER JOIN prstshp_product p ON p.id_product = po.id_product
                WHERE po.id_owner = '.$id_owner;
        return Db::getInstance()->executeS($query);
    }

    /**
     * Obtener las órdenes de un proveedor
     * @param int $supplier
     * @param int $start
     * @param int $end
     */

    public function getOrdersByProviders ($supplier, $start, $end, $orders = []) {
        $condition = count($orders) > 0 ? " AND a.id_order IN (".implode(",",$orders).") " : "";
        $dates = (!empty($start) && !empty($end)) ? ' AND DATE(o.date_add) >= "'.$start.'" AND DATE(o.date_add) <= "'.$end.'" ' : "";

        $sqlIncreaseGroupConcatLimit ="SET SESSION group_concat_max_len = 1000000";
        $executed = Db::getInstance()->executeS($sqlIncreaseGroupConcatLimit);

        $data = Db::getInstance()->executeS('
        SELECT sav.quantity as available, SUM(a.product_quantity) AS qty, GROUP_CONCAT(CONCAT(a.id_order,"(", a.product_quantity, ")")) as orders, a.product_quantity_in_stock, a.product_id, a.product_name, a.product_reference, a.original_wholesale_price, MAX(a.id_order_detail) as id_od
        FROM '._DB_PREFIX_.'order_detail a
        inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
        inner JOIN '._DB_PREFIX_.'product p ON (p.id_product = a.product_id)
        inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state)
        LEFT JOIN  '._DB_PREFIX_.'stock_available sav ON (sav.`id_product` = p.`id_product` AND sav.`id_product_attribute` = 0 AND sav.id_shop = 1  AND sav.id_shop_group = 0 )  
        WHERE os.id_order_state IN (2,3,4,5) AND ((p.id_supplier = '.(int)$supplier.$dates.') '.$condition.') GROUP BY a.product_id ORDER BY a.product_id');

        if (count(array_keys($data)) > 0 && array_keys($data)[0] == 'available') {
            $data = [$data];
        }
        return $data;
    }

    /**
     * Obtener los IDs de las órdenes de uno proveedor
     * @param int $supplier
     * @param int $start
     * @param int $end
     */
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

     /**
     * Obtener los datos de los productos de uno o varios transportistas.
     * @param mixed $carrierId
     * @param string $date
     * @param mixed $orders
     */
    public function getOrdersProductsByCarrier ($carrierId, $date, $orders = []) {
        $condition = count($orders) > 0 ? " OR a.id_order IN (".implode(",",$orders).") " : "";
        $carriers = implode(',', $carrierId);
        return Db::getInstance()->executeS('
                    SELECT  a.product_name,
                    a.product_quantity,
                    a.product_reference,
                    a.product_id,
                    a.id_order,
                    o.invoice_date
            FROM '._DB_PREFIX_.'order_detail a
            inner JOIN '._DB_PREFIX_.'orders o ON (o.id_order = a.id_order)
            inner JOIN '._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state) 
            inner JOIN '._DB_PREFIX_.'order_carrier oc ON (oc.id_order = a.id_order) 
            WHERE os.id_order_state IN (2,3,4,5) AND ((oc.id_carrier IN ('.$carriers.') 
            AND DATE(o.date_add) = "'.$date.'") '.$condition.')');
    }

    /**
     * Obtener los datos personalización de un producto en un carro de compra.
     * @param int $id_product
     * @param int $id_cart
     */
    public function getProductCustomization ($id_product, $id_cart) {
        return Db::getInstance()->executeS('SELECT cd.value, cfl.name FROM prstshp_customization c
        INNER JOIN prstshp_customized_data cd ON cd.id_customization = c.id_customization
        INNER JOIN prstshp_customization_field cf ON cf.id_product = c.id_product
        INNER JOIN prstshp_customization_field_lang cfl ON cfl.id_customization_field = cf.id_customization_field
        WHERE cfl.id_lang = 1 AND c.id_product = '.(int)$id_product.' AND c.id_cart = '.(int)$id_cart);
    }

    /**
     * Obtener los datos de la orden según el transportista transportista.
     * @param int $supplier
     * @param int $order
     * 
     * @return mixed
     */
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

    /**
     * Obtener la zona de entraga según el detalle de address_delivery de la orden.
     * @param int $idAddressDelivery
     * 
     * @return int
     * 
     */
    public function getZoneByAddresDelivery ($idAddressDelivery) {
        $data =  Db::getInstance()->executeS('
        SELECT c.id_zone
        FROM '._DB_PREFIX_.'address a
        INNER JOIN '._DB_PREFIX_.'country c ON (c.id_country = a.id_country)
        WHERE a.id_address = '.(int)$idAddressDelivery.' AND a.active = 1');
        return isset($data[0]) ? $data[0]['id_zone'] : null;
    }

    /**
     * Verificar si un producto puede ser llavado a una zona determinada.
     * @param int $idProduct
     * @param int $idZone
     * 
     * @return boolean
     */
    public function canDeliveryToThisZone ($idProduct, $idZone) {
        $data =  Db::getInstance()->executeS('
        SELECT c.id_carrier
        FROM '._DB_PREFIX_.'carrier c
        INNER JOIN '._DB_PREFIX_.'product_carrier pc ON (c.id_reference = pc.id_carrier_reference)
        INNER JOIN '._DB_PREFIX_.'delivery d ON (d.id_carrier = c.id_carrier)
        WHERE pc.id_product = '.(int)$idProduct.' AND d.id_zone = '.(int)$idZone. ' AND c.active = 1 AND c.deleted = 0 ');
        return (isset($data[0]['id_carrier']));
    }

     /**
     * Verificar si el usuario autenticado puede visualizar la totalidad de contenidos.
     * Los roles permiditos son: SUPERADMIN, CONTABLE y ADMIN.
     * @param object $employee
     * 
     * @return boolean
     */
    public function canViewAll($employee) {
        return in_array((int)$employee->id_profile, [1,7, 8]);
    }

     /**
     * Actualizar el stock del producto
     * @param int $id_product
     * @param int $int
     * 
     * @return void
     */
    public function updateProductQuantity($id_product, $qty) {
        Db::getInstance()->execute('
            update '._DB_PREFIX_.'stock_available psa 
            join '._DB_PREFIX_.'product p on psa.id_product = p.id_product 
            set psa.quantity = '.$qty.'   
            where psa.id_product = '.$id_product);
        Db::getInstance()->execute('
            update '._DB_PREFIX_.'product p 
            set p.quantity = '.$qty.'   
            where p.id_product = '.$id_product);
    }
}
