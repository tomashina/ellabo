<?php


namespace Agmedia\Models\Page;


use Illuminate\Database\Eloquent\Model;

class PageDocuments extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'page_docs';
    
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