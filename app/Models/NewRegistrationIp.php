<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class NewRegistrationIp extends Model
{
    
    protected $table = "new_registration_ips"; //table name
    protected $fillable = [
        'ip','status' 
       
      ];
      
}
