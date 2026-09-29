<?php


namespace Agmedia\Models\Page;


use Agmedia\Helpers\Config;
use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'page';
    
    /**
     * @var string
     */
    protected $primaryKey = 'page_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'page_id'
    ];
    
    /**
     * Relations methods
     */
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function description()
    {
        return $this->hasOne(PageDescription::class, 'page_id', 'page_id')->where('language_id', Config::getLanguage());
    }
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(PageDocuments::class, 'page_id', 'page_id');
    }
    
}