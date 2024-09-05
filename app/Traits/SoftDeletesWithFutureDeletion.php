<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait SoftDeletesWithFutureDeletion
{
//    use SoftDeletes {
//        SoftDeletes::runSoftDelete as parentRunSoftDelete;
//    }
    use SoftDeletes {
        SoftDeletes::performDeleteOnModel as parentPerformDeleteOnModel;
    }
    protected function performDeleteOnModel()
    {
//        if ($this->trashed() && $this->deleted_at->isPast()) {
//            return $this->parentPerformDeleteOnModel();
//        }
    }

}
