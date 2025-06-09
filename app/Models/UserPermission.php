<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions'; // Set the correct table name

    protected $fillable = ['user_id', 'menu_id']; // Define fillable columns

    // Define the relationship with the Menu model
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
