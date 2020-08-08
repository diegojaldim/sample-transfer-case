<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
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

        $debit = $payerAccount->current_account_balance - $data['value'];
        $payerAccount->update([
            'current_account_balance' => $debit,
        ]);

        $transaction = new Transaction($data);

        try {
            ProcessTransaction::dispatch($transaction);
        } catch (\Exception $e) {
            // @todo update payer account balance

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Something went wrong. Try again later',
                ], 400);
        }

        return response()
            ->json(['success' => true]);
    }

}
