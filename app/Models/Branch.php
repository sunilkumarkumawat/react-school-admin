<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Branch extends Model
{
      use SoftDeletes;
    protected $table = "branches"; //table name
  protected $guarded = [];
}
