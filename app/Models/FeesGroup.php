<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class FeesGroup extends Model
{
    
    protected $table = "fees_groups"; //table name
    protected $fillable = ['id','name'];
}
