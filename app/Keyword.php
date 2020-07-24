<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model {
    protected $fillable = [
        'keyword',
        'industry_id',
        'money_rank',
        'admin_accepted'
    ];

    public $timestamps = true;

    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
