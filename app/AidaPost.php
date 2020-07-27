<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidaPost extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $primaryKey = "id";
    protected $fillable = ['approved', 'text'];
}
