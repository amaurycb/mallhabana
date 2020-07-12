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
        ->innerJoin('carrier', 'c', 'c.id_carrier=pc.id_carrier')
        ->where("c.active = 1")
        ->where("pc.id_product = '$idProduct'"));

        if (count($carriers) > 0) {
             $carriers = (Db::getInstance())->executeS((new DbQuery())
            ->from('carrier', 'c')
            ->where("c.active = 1"));
        }
        $destiniesAvalilable = $this->getZoneByCarrier($carriers);
        return implode(', ', $destiniesAvalilable);
    }

    private function getZoneByCarrier($carriers) {
        $zones = [];
        foreach ($carriers as $carrier) {           
            $zonesAvailables = (Db::getInstance())->executeS((new DbQuery())
                            ->from('carrier_zone', 'cz')
                            ->innerJoin('zone', 'cf', 'cf.id_zone=cz.id_zone')
                            ->where('cz.id_carrier = ' . $carrier['id_carrier']));
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
     * get orders by provider and date
     */
    public function ordersByProviderAndDate( $month,  $year, $provider) {
        $query = 'SELECT o.reference as reference, 
                    osl.name as state_name,
                    o.payment,
                    CONCAT (c.firstname, " ", c.lastname) as client,
                    CONCAT (a.firstname, " ", a.lastname) as destiny,
                    o.date_add as created_at,
                    oh.date_add as aproved,
                    FORMAT(o.total_paid,2) AS paid,
                    s.name as supplier_name,
                    FORMAT(SUM(od.product_price * od.product_quantity),2) as supplier_total,
                    carriers.carrier_name,
                    FORMAT(o.total_shipping, 2) as shipping,
                    "Pendiente" as embalaje,
                    "Pendiente" as gain,
                    cu.iso_code as currency,
                    FORMAT(o.conversion_rate, 2) AS rate 
            FROM prstshp_order_detail AS od
            INNER JOIN prstshp_orders AS o ON (o.id_order = od.id_order)
            INNER JOIN prstshp_order_state_lang osl ON (osl.id_order_state = o.current_state AND osl.id_lang = 1  ) 
            INNER JOIN prstshp_customer c ON (c.id_customer = o.id_customer) 
            INNER JOIN prstshp_address a ON (a.id_address = o.id_address_delivery) 
            LEFT JOIN prstshp_order_history AS oh ON (oh.id_order = o.id_order AND oh.id_order_state = 2)
            INNER JOIN prstshp_supplier AS s ON (s.id_supplier = od.product_supplier_reference AND od.product_supplier_reference = "'.$provider.'" )
            LEFT JOIN prstshp_currency AS cu ON (cu.id_currency = o.id_currency)
            LEFT JOIN
                (SELECT GROUP_CONCAT(prstshp_carrier.name) as carrier_name, id_order FROM prstshp_carrier INNER JOIN prstshp_orders ON (prstshp_carrier.id_carrier = prstshp_orders.id_carrier) GROUP BY prstshp_orders.id_order) AS carriers
                ON (carriers.id_order = o.id_order)
            WHERE o.current_state in (2,3,4,5)  AND YEAR(o.date_add) = "'.$year.'" AND MONTH(o.date_add) = "'.$month.'"
            GROUP BY o.id_order';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Print Conciliation
     */
    public function excelConciliation ($orders) {
        $timestamp = time();
        $filename = 'Export_' . $timestamp . '.xls';
        
        header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
        header("Content-Disposition: attachment; filename=$filename");
        
        echo implode("\t", array_values($this->translateKeys())) . "\n";

        foreach ($orders as $row) {
            echo implode("\t", array_values($row)) . "\n";
        }        
        exit();
    }

    /**
     * Get suppliers
     */
    public function getSuppliers() {
        $result = [];
        $suppliers = new Collection('ProductSupplier');
        $suppliers->groupBy('id_supplier');
        
        foreach($suppliers as $supplier) {
            $result[] = new Supplier($supplier->id_supplier, $this->context->language->id);
        }
        return $result;
    }

    /**
     * Translate excel column titles
     */
    private function translateKeys() {
       return  
       ['reference' => 'Referencia',
        'state_name' => 'Estado',
        'payment' => 'Método de pago',
        'client' => 'Cliente',
        'destiny' => 'Destinatario',
        'created_at' => 'Creado el',
        'aproved' => 'Aprobado el',
        'paid' => 'Total pagado',
        'supplier_name' => 'Proveedor',
        'supplier_total' => 'Total Proveedor',
        'carrier_name' => 'Transportista',
        'shipping' => 'Total Transportación',
        'embalaje' => 'Embalaje',
        'gain' => 'Utilidad',
        'currency' => 'Moneda',
        'rate' => 'Cambio'];
    }

}