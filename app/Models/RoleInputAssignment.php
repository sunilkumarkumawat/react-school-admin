<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleInputAssignment extends Model
{
    // use SoftDeletes;

    protected $table = "role_input_assignments"; // Table name

    protected $fillable = [
        
        'admin_id',
        'branch_id',
        'role_id',
       'input_id'
    ];

}
