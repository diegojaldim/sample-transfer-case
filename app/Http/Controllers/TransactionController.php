<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function make(Request $request)
    {
        $data = $request->all();

        return response()
            ->json(['success' => true]);
    }

}
