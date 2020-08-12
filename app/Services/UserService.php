<?php

namespace App\Services;


use App\Repositories\UserRepository;
use App\Models\User;

class UserService
{

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->userRepository->getById($id);
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getCurrentAccountBalance(User $user)
    {
        return $this->userRepository->getCurrentAccountBalance($user);
    }

}
