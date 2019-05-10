<?php

namespace App\Models;

use Mild\Database\Model;

class Paper extends Model
{
    /**
     * Set table name on the database
     *
     * @var string
     */
    protected $table = 'papers';
    /**
     * Set Primary key, it will work on relation 
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Set date format, it will if the attribute use a Carbon instance
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
    
    
}