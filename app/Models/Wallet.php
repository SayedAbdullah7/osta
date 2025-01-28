<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Bavix\Wallet\Models\Wallet as BavixWallet;

class Wallet extends BavixWallet
{
    use HasFactory;
}
