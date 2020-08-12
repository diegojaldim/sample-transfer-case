<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Factory\Transaction;
use App\Jobs\ProcessTransaction;

class TransactionService
{

    /**
     * @var ValidateService
     */
    protected $validateService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * TransactionService constructor.
     * @param ValidateService $validateService
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService,
        ValidateService $validateService
    ) {
        $this->userService = $userService;
        $this->validateService = $validateService;
    }

    /**
     * @param Request $request
     * @return void;
     */
    public function transfer(Request $request)
    {
        $data = $request->all();

        // Validating request
        $this->validateService->validateTransaction();

        $payer = $this->userService->getById($data['payer']);

        // Debit account value for current payer
        Transaction::debitForUser($payer->id, $data['value']);

        $transaction = new Transaction($data);

        // Dispatch job transaction
        ProcessTransaction::dispatch($transaction);

        return;
    }

}
