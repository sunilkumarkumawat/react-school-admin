<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions'; // Set the correct table name

    protected $fillable = ['user_id', 'permission']; // Define fillable columns

  
}
