<?php


namespace Agmedia\Services;


use Agmedia\Helpers\Log;
use Agmedia\Models\Product\Product;

class ImportXML
{
    
    /**
     * @var \SimpleXMLElement
     */
    public $xml;
    
    /**
     * @var array
     */
    public $diff_codes;
    
    /**
     * @var array
     */
    public $specials;
    
    
    /**
     * ImportXML constructor.
     *
     * @param $xml
     */
    public function __construct($xml)
    {
        $this->xml = $xml;
    }
    
    
    /**
     * @param string $node
     *
     * @return \Illuminate\Support\Collection
     */
    public function collect($node = 'PRODUCT')
    {
        return collect(json_decode(json_encode($this->xml), true)[$node]);
    }
    
    
    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function setDiffCodes()
    {
        $loaded = $this->collect()->where('Raspolozivo', '>', 0)->where('Aktivno', 'D')->where('WebView', 'D');
        
        $loaded_codes = $loaded->pluck('Sifra')->flatten();
        $exist_codes  = Product::pluck('model')->flatten();
        
        $this->diff_codes = $loaded_codes->diff($exist_codes);
        
        return $this->diff_codes;
    }
    
    
    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function setSpecialCodes()
    {
        $loaded = $this->collect();
    
        foreach ($loaded->all() as $item) {
            if ($item['MlpCijenaOld'] > $item['MlpCijena']) {
                $item['MlpOsnovicaOld'] = number_format($item['MlpCijenaOld'] / 1.25, 2, '.', '');
                $this->specials[] = $item;
            }
        }
    
        Log::stockPile(count($this->specials), 'specials2');
        Log::stockPile($this->specials, 'specials2');
        
        return $this->specials;
    }
    
    
    /**
     * @param $item
     *
     * @return bool
     */
    public function passesSetConstrains($item)
    {
        if (in_array($item->Sifra, $this->diff_codes->all())) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @param $item
     *
     * @return array
     */
    public function setProductImportData($item)
    {
        return [
            'sku'      => (string) $item->Sifra,
            'ean'      => (string) $item->BarCode,
            'name'     => (string) $item->Naziv,
            'messure'  => $this->setUnit((string) $item->JedMjere),
            'quantity' => (string) $item->Raspolozivo,
            // VP/neto
            'price'    => (string) $item->MlpOsnovica,
            // MP/bruto
            'price_mp' => (string) $item->MlpCijena,
            'state'    => (string) $item->DrzavaPorijeklo,
            'min'      => (string) $item->KolPaket,
            'image'    => 'image/catalog/products_2020/' . (string) $item->Sifra . '.jpg',
        ];
    }
    
    
    /**
     * @param $str
     *
     * @return int|null
     */
    private function setUnit($str)
    {
        if ($str == 'KG') { return 1; }
        if ($str == 'KOM') { return 2; }
        if ($str == 'KPL') { return 3; }
        if ($str == 'M') { return 4; }
        if ($str == 'PAR') { return 5; }
        
        return null;
    }
}
