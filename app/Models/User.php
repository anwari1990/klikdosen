<?php

namespace App\Models;

use Mild\Database\Model;

class User extends Model
{
    /**
     * Set table name on the database
     *
     * @var string
     */
    protected $table = 'users';
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

    /**
     * @return true
     */
    public function isAdmin()
    {
        return $this->getAttribute('admin') === '1';
    }
    
}