<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AidaPost extends Model
{
    protected $primaryKey = "id";
    protected $fillable = ['approved', 'text'];
}
