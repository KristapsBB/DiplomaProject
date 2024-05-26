<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\LoginForm;

class Authentication extends Controller
{
    public function login()
    {
        $page_after_logging_in = '/tenders/import-tenders';
        $auth = Core::getCurrentApp()->getAuthentication();

        $curr_user = $auth->getCurrentUser();
        if ($curr_user->validateToken('auth')) {
            return $this->toUrl($page_after_logging_in);
        }

        $http = Core::getCurrentApp()->getHttp();

        if (!empty($http->post('login-form')) && $http->isPost()) {
            $form_data = $http->post('login-form');

            $login_form = new LoginForm(
                $form_data['login'],
                $form_data['password']
            );

            if ($login_form->validateForm()) {
                $auth->authenticate(
                    $auth->getUserByLogin($login_form->login)
                );

                return $this->toUrl($page_after_logging_in);
            }

            $error = $login_form->getLastError();
        }

        return $this->showView('login', [
            'error' => $error ?? '',
        ]);
    }

    public function logout()
    {
        $http = Core::getCurrentApp()->getHttp();
        $auth = Core::getCurrentApp()->getAuthentication();

        if ($http->isPost() && $this->isCurrUserLoggedIn()) {
            $auth->logout(
                $auth->getCurrentUser()
            );
        }

        return $this->toUrl('/login');
    }

    public function default()
    {
        return $this->login();
    }
}
