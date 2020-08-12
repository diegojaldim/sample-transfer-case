<?php

namespace App\Http\Controllers;

use \Illuminate\Http\JsonResponse;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    /**
     * @var TransactionService
     */
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function make(Request $request)
    {
        try {
            $this->transactionService->transfer($request);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['message' => 'success']);
    }

}
