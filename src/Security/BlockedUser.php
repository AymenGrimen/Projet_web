<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BlockedUser implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user)
    {
        // TODO: Implement checkPreAuth() method.
        $this->checkAuth($user);
    }

    public function checkPostAuth(UserInterface $user)
    {
        // TODO: Implement checkPostAuth() method.
        $this->checkAuth($user);
    }

    private function checkAuth(UserInterface $user){

        if(!$user instanceof User)
        {
            return;
        }
        if(str_contains($user->getEtat(),1))
        {
            throw new CustomUserMessageAccountStatusException('You are blocked. Please check your Email or contact support.');
        }
    }
}