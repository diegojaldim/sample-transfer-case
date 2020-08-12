<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Models\User;

class ValidateService
{
    /**
     * @const string
     */
    const MESSAGE_PAYER_NOT_EXISTS = 'Payer does not exists';

    /**
     * @const string
     */
    const MESSAGE_PAYEE_NOT_EXISTS = 'Payee does not exists';

    /**
     * @const string
     */
    const MESSAGE_PJ_TRANSACTION = 'PJ can\'t make a transaction';

    /**
     * @const string
     */
    const MESSAGE_TRANSACTION_YOURSELF = 'You can\'t make a transaction for yourself';

    /**
     * @const string
     */
    const MESSAGE_TRANSACTION_VALUE_EMPTY = 'The value must be greater than 0';

    /**
     * @const string
     */
    const MESSAGE_INSUFFICIENT_FOUND = 'Insufficient found';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var array
     */
    protected $rules = [
        'transaction' => [
            'value' => 'required|numeric',
            'payer' => 'required|numeric',
            'payee' => 'required|numeric',
        ]
    ];

    /**
     * ValidateService constructor.
     * @param Request $request
     * @param UserService $userService
     */
    public function __construct(Request $request, UserService $userService)
    {
        $this->request = $request;
        $this->userService = $userService;
    }

    /**
     * @return void
     */
    public function validateTransaction()
    {
        $request = $this->request->all();

        $validator = Validator::make($request, $this->rules['transaction']);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $payer = $this->userService->getById($request['payer']);
        $payee = $this->userService->getById($request['payee']);

        if (empty($payer)) {
            throw new InvalidArgumentException(
                self::MESSAGE_PAYER_NOT_EXISTS
            );
        }

        if (empty($payee)) {
            throw new InvalidArgumentException(
                self::MESSAGE_PAYEE_NOT_EXISTS
            );
        }

        if ($payer->type === User::CUSTOMER_TYPE_PJ) {
            throw new InvalidArgumentException(
                self::MESSAGE_PJ_TRANSACTION
            );
        }

        if ($payer->id === $payee->id) {
            throw new InvalidArgumentException(
                self::MESSAGE_TRANSACTION_YOURSELF
            );
        }

        if ($request['value'] <= 0) {
            throw new InvalidArgumentException(
                self::MESSAGE_TRANSACTION_VALUE_EMPTY
            );
        }

        $payerAccount = $this->userService->getCurrentAccountBalance($payer);

        if ($payerAccount < $request['value']) {
            throw new InvalidArgumentException(
                self::MESSAGE_INSUFFICIENT_FOUND
            );
        }

        return;
    }

}
