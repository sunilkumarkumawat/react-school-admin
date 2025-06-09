<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $table = "menus"; // Table name

    // Define the relationship with user_permissions table
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'menu_id'); // menu_id is the foreign key
    }
    public function submenus()
{
    return $this->hasMany(Menu::class, 'parent_id'); // Assuming 'parent_id' links submenus
}
}
