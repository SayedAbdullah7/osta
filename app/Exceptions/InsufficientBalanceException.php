<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $minBalance;

    public function __construct($minBalance)
    {
        $this->minBalance = $minBalance;
        parent::__construct("Insufficient balance. Minimum required balance is $minBalance");
    }

    public function report()
    {
        // You can log the error here if needed
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'required_balance' => $this->minBalance,
        ], 400);
    }
}
