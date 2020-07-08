<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Keyword extends Model {
    protected $fillable = [
        'keyword',
        'industry_id',
        'money_rank',
        'admin_accepted'
    ];
}
