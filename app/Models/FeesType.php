<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class FeesType extends Model
{
    
    protected $table = "fees_types"; //table name
    protected $fillable = ['id','fees_group_id','name'];
}
