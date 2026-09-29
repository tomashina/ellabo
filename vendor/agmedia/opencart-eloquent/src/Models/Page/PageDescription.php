<?php


namespace Agmedia\Models\Page;


use Illuminate\Database\Eloquent\Model;

class PageDescription extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'page_description';
    
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
    
}