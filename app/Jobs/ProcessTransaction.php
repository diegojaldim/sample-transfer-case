<?php

namespace App\Jobs;

use App\Exceptions\NotificationException;
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

    /**
     * @var Transaction
     */
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
     * Execute the Job
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        try {
            $this->transaction->transfer();
            Transaction::writeLn('SUCCESS');
        } catch (TransactionException $e) {
            // Revert transaction if fails
            $this->fail($e);
        } catch (NotificationException $e) {
            Transaction::writeLn($e->getMessage());
            Transaction::writeLn('SUCCESS');
        }
    }

    /**
     * @param TransactionException $e
     */
    public function fail(TransactionException $e)
    {
        Transaction::writeLn($e->getMessage());
        Transaction::writeLn('Failed to make a transfer, reverting transaction...');

        $data = $this->transaction->getData();
        Transaction::creditForUser($data['payer'], $data['value']);

        Transaction::writeLn('FAIL');
    }

}
