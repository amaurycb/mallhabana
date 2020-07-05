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
}