<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    
    protected $table = "roles"; //table name
    protected $fillable = ['admin_id','branch_id','name', 'description'];
}
