<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model {
    protected $perPage = 100;

    protected $fillable = [
        'keyword',
        'industry_id',
        'money_rank',
        'admin_accepted'
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
