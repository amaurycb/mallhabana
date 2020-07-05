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
}