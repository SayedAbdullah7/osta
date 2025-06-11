<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;
use Illuminate\Support\Carbon;

trait NewSoftDeletes
{
    use BaseSoftDeletes;

    public static function bootSoftDeletes()
    {
        static::addGlobalScope('softDeleted', function (Builder $builder) {
            $builder->where(function ($query) {
                $query->whereNull('deleted_at')
                    ->orWhere('deleted_at', '>', Carbon::now());
            });
        });

        static::restoring(function (Model $model) {
            $model->deleted_at = null;
        });
    }

    public function trashed()
    {
        return !is_null($this->deleted_at) && $this->deleted_at <= Carbon::now();
    }

    public function forceDelete()
    {
        return parent::forceDelete();
    }

    public function restore()
    {
        $this->deleted_at = null;
        $this->save();
    }

    public function softDelete()
    {
        $this->deleted_at = Carbon::now();
        $this->save();
    }

    public function scopeWithTrashed($query)
    {
        return $query->withoutGlobalScope('softDeleted');
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->withoutGlobalScope('softDeleted')
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', Carbon::now());
    }
}
