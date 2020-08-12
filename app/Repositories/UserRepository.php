<?php


namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return User::find($id);
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getCurrentAccountBalance(User $user)
    {
        $userAccount = $user->currentAccountBalance()->first();
        return $userAccount->current_account_balance;
    }

}
