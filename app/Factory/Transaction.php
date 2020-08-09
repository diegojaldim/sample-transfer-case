<?php

namespace App\Factory;

use App\Models\User;
use App\Exceptions\TransactionException;
use GuzzleHttp\Client;

class Transaction
{

    /**
     * @const string
     */
    const TRANSACTION_SERVICE_URL = 'https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6';

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

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     * @throws TransactionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transfer()
    {
         try {

            $client = new Client();
            $response = $client->request('GET', self::TRANSACTION_SERVICE_URL);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();
            $jsonResponse = json_decode($content, true);

            if (
                !isset($jsonResponse['message']) ||
                $jsonResponse['message'] !== 'Autorizado' ||
                $statusCode != 200
            ) {
                throw new TransactionException();
            }

            self::creditForUser($this->data['payee'], $this->data['value']);

        } catch (\Exception $e) {
            throw new TransactionException();
        }

        return true;
    }

    /**
     * @param $userId
     * @param $value
     */
    public static function creditForUser($userId, $value)
    {
        $user = User::find($userId);
        $account = $user->currentAccountBalance()->first();

        $credit = $account->current_account_balance + $value;

        $account->update([
            'current_account_balance' => $credit,
        ]);
    }

    /**
     * @param $userId
     * @param $value
     */
    public static function debitForUser($userId, $value)
    {
        $user = User::find($userId);
        $account = $user->currentAccountBalance()->first();

        $credit = $account->current_account_balance - $value;

        $account->update([
            'current_account_balance' => $credit,
        ]);
    }

}
