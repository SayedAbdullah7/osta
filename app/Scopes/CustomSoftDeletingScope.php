<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CustomSoftDeletingScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
//        $builder->where($model->getQualifiedDeletedAtColumn(), '<', Carbon::now());
//        $builder->whereNotNull($model->getQualifiedDeletedAtColumn());
//        $builder->whereNull($model->getQualifiedDeletedAtColumn())->orWhere($model->getQualifiedDeletedAtColumn(), '>', Carbon::now());
    }
}

