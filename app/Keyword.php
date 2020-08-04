<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model {
    protected $perPage = 300;

    protected $fillable = [
        'keyword',
        'industry_id',
        'money_rank',
        'admin_accepted'
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public static function set_client($client_id) {
        $client = Client::findOrFail($client_id);

        return self::where("industry_id", $client->industry_id);
    }
}
