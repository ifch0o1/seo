<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidaSentence extends Model
{
    protected $perPage = 100;
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = array('admin_accepted');
    
    public function save(array $options = [])
    {
        // If no author has been assigned, assign the current user's id as the author of the post
        if (!$this->author_id && Auth::user()) {
            $this->author_id = Auth::user()->id;
        }

        parent::save();
    }
}
