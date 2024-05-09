<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\LoginForm;

class Authentication extends Controller
{
    public function login()
    {
        $error = null;

        $authentication = Core::getCurrentApp()->getAuthentication();
        $curr_user = $authentication->getCurrentUser();

        if ($curr_user->validateToken()) {
            return $this->toUrl('/admin-panel/tender-list');
        }

        $http = Core::getCurrentApp()->getHttp();

        if (!empty($http->post('login-form')) && $http->isPost()) {
            $form_data = $http->post('login-form');

            $login_form = new LoginForm();
            $login_form->login = $form_data['login'];
            $login_form->password = $form_data['password'];

            if ($login_form->validateForm()) {
                $user = $authentication->getUserByLogin($login_form->login);
                $authentication->authenticate($user);

                return $this->toUrl('/admin-panel/tender-list');
            }

            $error = $login_form->getLastError();
        }

        return $this->showView('login', [
            'error' => $error,
        ]);
    }

    public function logout()
    {
        $http = Core::getCurrentApp()->getHttp();
        $authentication = Core::getCurrentApp()->getAuthentication();

        if ($http->isPost() && $this->isCurrUserLoggedIn()) {
            $curr_user = $authentication->getCurrentUser();
            $authentication->logout($curr_user);
        }

        return $this->toUrl('/login');
    }

    public function default()
    {
        return $this->login();
    }
}
