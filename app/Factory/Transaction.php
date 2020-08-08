<?php

namespace App\Factory;

use App\Models\User;

class Transaction
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * Transaction constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function transfer()
    {
        $payee = User::find($this->data['payee']);
        $payeeAccount = $payee->currentAccountBalance()->first();

        $credit = $payeeAccount->current_account_balance + $this->data['value'];

        $payeeAccount->update([
            'current_account_balance' => $credit,
        ]);

        return true;
    }

}
