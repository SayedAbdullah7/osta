<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = ['deletable_id', 'deletable_type','user_id', 'user_type', 'requested_at', 'deleted_at'];

    protected $dates = ['requested_at', 'deleted_at'];


    /**
     * Get the parent deletable model (user or provider).
     */
    public function deletable()
    {
        return $this->morphTo();
    }
}
