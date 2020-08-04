<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class AuthToken extends Model
{
    protected $fillable = ['token', 'client_id', 'active', 'type'];
}
