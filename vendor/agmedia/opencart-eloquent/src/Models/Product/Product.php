<?php


namespace Agmedia\Models\Product;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Product extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'product';
    
    /**
     * @var string
     */
    protected $primaryKey = 'product_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'product_id'
    ];
    
    /**
     * Relations methods
     */
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(ProductOption::class, 'product_id', 'product_id')->with('value');
    }
    
    /**
     * Query scope methods
     */
    
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeSkus($query)
    {
        return ProductOption::where('product_id', $this->product_id)->pluck('sku');
    }
    
    
    /**
     * @param $product
     * @param $languages
     * @param $images
     *
     * @return \Illuminate\Support\Collection
     */
    public function make($product)
    {
        $response = [
            'sku'                 => $product['sku'],
            'upc'                 => '',
            'ean'                 => '',
            'jan'                 => '',
            'isbn'                => '',
            'mpn'                 => '',
            'location'            => $product['state'],
            'price'               => $product['price'],
            'tax_class_id'        => 9,
            'quantity'            => $product['quantity'],
            'minimum'             => $product['min'],
            'subtract'            => 1,
            'stock_status_id'     => 6,
            'shipping'            => 1,
            'date_available'      => Carbon::now()->subDay()->format('Y-m-d'),
            'length'              => '',
            'width'               => '',
            'height'              => '',
            'length_class_id'     => 1,
            'weight'              => '',
            'weight_class_id'     => 1,
            'status'              => 1,
            'sort_order'          => 0,
            'manufacturer'        => '',
            'manufacturer_id'     => 0,
            'category'            => '',
            'filter'              => '',
            'download'            => '',
            'related'             => '',
            'image'               => $product['image'],
            'points'              => '',
            'product_store'       => [0 => 0],
            'product_description' => $this->getDescription($product),
            'product_image'       => '',
            'product_layout'      => [0 => ''],
            'product_category'    => [0 => 108],
            'mennyisegi_egyseg_id'=> $product['messure']
        ];
        
        $response['model'] = $product['sku'];
        
        return $response;
    }
    
    
    /**
     * @param $language
     *
     * @return array
     */
    private function getDescription($product)
    {
        return [
            2 => [
                'name'             => $product['name'],
                'description'      => $product['name'],
                'meta_title'       => $product['name'], // NAZIV_ODJELA
                'custom_alt'       => '',
                'custom_h1'        => '',
                'custom_h2'        => '',
                'custom_imgtitle'  => '',
                'meta_description' => $product['name'],
                'meta_keyword'     => $product['name'],
                'tag'              => '',
            ]
        ];
    }
    
}