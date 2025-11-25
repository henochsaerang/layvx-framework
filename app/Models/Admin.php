<?php

namespace App\Models;

use App\Core\Model;

class Admin extends Model
{
    /**
     * The database table associated with the model.
     *
     * @var string
     */
    protected static $table = 'admins';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected static $primaryKey = 'id_admin'; // Example if your PK is not 'id'

    /**
     * The columns that are mass assignable.
     * This is a security feature to prevent unwanted data from being saved
     * when using methods like create() or update().
     *
     * @var array
     */
    protected static $fillable = [
        'nama', 
        'email', 
        'password',
        // Add other fillable columns from your 'admins' table here
    ];
}
