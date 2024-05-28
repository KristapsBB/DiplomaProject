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
        $attempts_before_blocking = 3;

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

            $user = $auth->getUserByLogin($login_form->login);

            if (!empty($user) && !$user->isUnblocked($attempts_before_blocking)) {
                return $this->showView('login', [
                    'error' => 'the maximum number of attempts has been exceeded, try again later',
                ]);
            }

            if ($login_form->validateForm($attempts_before_blocking)) {
                $user->unblock();
                $auth->authenticate($user);

                return $this->toUrl($page_after_logging_in);
            } else {
                $user?->tryBlock($attempts_before_blocking, 15);
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
