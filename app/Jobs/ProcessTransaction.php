<?php

namespace App\Jobs;

use App\Exceptions\TransactionException;
use App\Factory\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;

    /**
     * ProcessTransaction constructor.
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->transaction->transfer();
        } catch (TransactionException $e) {
            $this->failed();
        }
    }

    public function failed()
    {
        $data = $this->transaction->getData();
        Transaction::creditForUser($data['payer'], $data['value']);
        //@todo notify client
    }

}
