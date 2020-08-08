<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'value' => 'required',
            'payer' => 'required',
            'payee' => 'required',
        ]);

        if ($validator->fails()) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Invalid request',
                ], 400);
        }

        $payer = User::find($data['payer']);
        $payerAccount = $payer->currentAccountBalance()->first();

        $payee = User::find($data['payee']);
        $payeeAccount = $payee->currentAccountBalance()->first();

        $debit = $payerAccount->current_account_balance - $data['value'];
        $credit = $payerAccount->current_account_balance + $data['value'];

        $payerAccount->update([
            'current_account_balance' => $debit,
        ]);

        $payeeAccount->update([
            'current_account_balance' => $credit,
        ]);

        return response()
            ->json(['success' => true]);
    }

}
