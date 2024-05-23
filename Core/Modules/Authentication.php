<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;
use DiplomaProject\Core\Interfaces\DataBaseModelInterface;
use DiplomaProject\Core\Interfaces\UserInterface;
use DiplomaProject\Core\Module;

class Authentication extends Module
{
    /**
     * @var UserInterface&DataBaseModelInterface
     */
    private string $user_class = '';

    /**
     * time in minutes
     */
    private int $token_lifetime = 0;

    /**
     * @param int $lifetime token lifetime in minutes
     */
    protected function setTokenLifetime(int $lifetime)
    {
        $this->token_lifetime = $lifetime;
    }

    protected function setUserClass(string $user_class)
    {
        if (!class_exists($user_class)) {
            throw new \Exception("class '{$user_class}' not found");
        }

        if (
            !((new $user_class()) instanceof UserInterface)
            || !((new $user_class()) instanceof DataBaseModelInterface)
        ) {
            throw new \Exception(
                "The class {$user_class} should implement interface "
                . UserInterface::class . ' or ' . DataBaseModelInterface::class
            );
        }

        $this->user_class = $user_class;
    }


    /**
     * @return UserInterface&DataBaseModelInterface
     */
    public function getCurrentUser()
    {
        $token = Core::getCurrentApp()->getHttp()->getCookie('token');

        if (null === $token) {
            return $this->user_class::getGuest();
        }

        $user = $this->user_class::getUserByToken($token);

        if (empty($user)) {
            return $this->user_class::getGuest();
        }

        return $user;
    }

    /**
     * @return UserInterface&DataBaseModelInterface
     */
    public function getUserByLogin(string $login)
    {
        return $this->user_class::getUserByLogin($login);
    }

    /**
     * @return UserInterface&DataBaseModelInterface
     */
    public function getUserByToken(string $token)
    {
        return $this->user_class::getUserByToken($token);
    }

    public function authenticate(UserInterface & DataBaseModelInterface $user)
    {
        $new_token = $user->generateToken($this->token_lifetime);
        $user->setToken($new_token);
        $user->save();

        Core::getCurrentApp()->getHttp()->setCookie('token', $new_token, time() + $this->token_lifetime * 60);
    }

    public function logout(UserInterface & DataBaseModelInterface $user)
    {
        $user->setToken('');
        $user->save();

        Core::getCurrentApp()->getHttp()->setCookie('token', '');
    }
}
