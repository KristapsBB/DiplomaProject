<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\RegistrationForm;
use DiplomaProject\Models\User;

class Registration extends Controller
{
    public function default()
    {
        return $this->registrationPage();
    }

    public function registrationPage()
    {
        if ($this->isCurrUserLoggedIn()) {
            return $this->toUrl('/tenders/import-tenders');
        }

        $http = Core::getCurrentApp()->getHttp();
        $error = null;

        if ($http->isPost() && !empty($http->post('registration-form'))) {
            $formdata = $http->post('registration-form');
            $login    = $formdata['login'] ?? '';
            $email    = $formdata['email'] ?? '';
            $password = $formdata['password'] ?? '';

            $regform = new RegistrationForm($email, $login, $password);

            if ($regform->validateForm()) {
                $new_user = User::register($email, $login, $password);

                if (!$new_user->hasErrors()) {
                    return $this->showView('notice', [
                        'title'   => 'Registration success',
                        'message' => "You have successfully registered.
                        Wait for the administrator to activate your account.",
                    ]);
                } else {
                    $error = $new_user->getLastError();
                }
            } else {
                $error = $regform->getLastError();
            }
        }

        return $this->showView('registration-page', ['error' => $error]);
    }
}
