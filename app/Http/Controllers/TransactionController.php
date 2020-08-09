<?php

namespace App\Http\Controllers;

use App\Factory\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessTransaction;

class TransactionController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function make(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'value' => 'required|numeric',
            'payer' => 'required|numeric',
            'payee' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Invalid request',
                ], 400);
        }

        $payer = User::find($data['payer']);
        $payee = User::find($data['payee']);

        if (empty($payer)) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Payer does not exists',
                ], 400);
        }

        if (empty($payee)) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Payee does not exists',
                ], 400);
        }

        if ($payer->type === User::CUSTOMER_TYPE_PJ) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'PJ can\'t make a transaction',
                ], 400);
        }

        if ($payer->id === $payee->id) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'You can\'t make a transaction for yourself',
                ], 400);
        }

        if ($data['value'] <= 0) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'The value must be greater than 0!',
                ], 400);
        }

        $payerAccount = $payer->currentAccountBalance()->first();

        if ($payerAccount->current_account_balance < $data['value']) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Insufficient founds :(',
                ], 400);
        }

        Transaction::debitForUser($payer->id, $data['value']);

        $transaction = new Transaction($data);

        // Dispatch job transaction
        ProcessTransaction::dispatch($transaction);

        return response()
            ->json(['success' => true]);
    }

}
