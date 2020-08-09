<?php

namespace App\Factory;

use App\Models\User;
use App\Exceptions\TransactionException;
use App\Exceptions\NotificationException;
use GuzzleHttp\Client;

class Transaction
{

    /**
     * @const string
     */
    const TRANSACTION_SERVICE_URL = 'https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6';

    /**
     * @const string
     */
    const NOTIFICATION_SERVICE_URL = 'https://run.mocky.io/v3/b19f7b9f-9cbf-4fc6-ad22-dc30601aec04';

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
     * @throws NotificationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transfer()
    {
        try {

            self::writeLn('Making a transaction...');

            // Consult external payment service
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
            } catch (\Exception $e) {
                throw new TransactionException();
            }

            self::creditForUser($this->data['payee'], $this->data['value']);

            self::writeLn('Sending notification...');

            // Send notification from an external service
            try {
                $client = new Client();
                $response = $client->request('GET', self::NOTIFICATION_SERVICE_URL);

                $statusCode = $response->getStatusCode();
                $content = $response->getBody()->getContents();
                $jsonResponse = json_decode($content, true);

                if (
                    !isset($jsonResponse['message']) ||
                    $jsonResponse['message'] !== 'Enviado' ||
                    $statusCode != 200
                ) {
                    throw new NotificationException();
                } else {
                    self::writeLn('Notification sent');
                }

            } catch (\Exception $e) {
                throw new NotificationException();
            }

        } catch (TransactionException $e) {

            throw new TransactionException('Transaction service failed');

        } catch (NotificationException $e) {

            throw new NotificationException('Fail to send notification');

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

    /**
     * @param string $str
     */
    public static function writeLn($str = '')
    {
        echo '- ' . $str . PHP_EOL;
    }

}
