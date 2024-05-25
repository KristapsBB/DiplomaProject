<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\User;

class Users extends Controller
{
    public function before(string $method_name)
    {
        /**
         * names of methods that can only be accessed by a POST request
         */
        $onlyPost = ['add', 'remove', 'edit', 'disable', 'enable', 'setPassWord', 'logout'];

        if (!$this->getCurrentUser()->can('edit_users')) {
            return $this->showView('error', ['error' => 'Access denied'], [], 403);
        }

        $http = Core::getCurrentApp()->getHttp();

        if (false !== array_search($method_name, $onlyPost) && !$http->isPost()) {
            return $this->showView('error', ['error' => 'Method Not Allowed'], [], 405);
        }

        return null;
    }

    private function getUser(array $formdata): User
    {
        if (empty($formdata['id'])) {
            return $this->toUrl('/users/edit-page', ['message' => "failed: parameter id is empty"]);
        }

        if (!is_numeric($formdata['id'])) {
            return $this->toUrl('/users/edit-page', ['message' => "failed: parameter id is invalid"]);
        }

        $id   = (int) $formdata['id'];
        $user = User::findOneBy('id', $id);

        if (empty($user)) {
            return $this->toUrl('/users/edit-page', [
                'message' => "failed: user with id '$id' not found"
            ]);
        }

        return $user;
    }

    private function saveUser(User $user)
    {
        if ($user->validate() && $user->save()) {
            return $this->toUrl('/users/edit-page', [
                'user[id]' => $user->id,
                'message'  => 'saved'
            ]);
        } else {
            if ($user->hasErrors()) {
                $message = 'failed: ' . $user->getLastError();
            } else {
                $message = 'no changes';
            }

            return $this->toUrl('/users/edit-page', [
                'user[id]' => $user->id,
                'message' => $message
            ]);
        }
    }

    public function default()
    {
        return $this->list();
    }

    public function list()
    {
        $http = Core::getCurrentApp()->getHttp();

        return $this->showView('user-list', [
            'user-list' => User::findAll(),
            'message' => $http->get('message') ?? '',
            'status'  => $http->get('status') ?? '',
        ]);
    }

    public function add()
    {
        $http = Core::getCurrentApp()->getHttp();
        $user_data = $http->post('new-user-form') ?? [];

        $new_user = User::register(
            $user_data['email'],
            $user_data['login'],
            $user_data['password'],
            User::DEFAULT_ACCESS_LEVEL
        );

        if (!$new_user->hasErrors()) {
            $message = 'user added successfully';
            $status  = 'complete';
        } else {
            $message = 'adding user failed: ' . $new_user->getLastError();
            $status  = 'error';
        }

        return $this->showView('user-list', [
            'user-list' => User::findAll(),
            'message' => $message,
            'status'  => $status,
        ]);
    }

    public function editPage()
    {
        $http = Core::getCurrentApp()->getHttp();
        $userdata = $http->get('user') ?? [];

        if (is_numeric($userdata['id'] ?? null)) {
            $id = (int) $userdata['id'];
            $user = User::findOneBy('id', $id);
        }

        return $this->showView('registration-edit-user', [
            'message' => $http->get('message') ?? '',
            'error' => '',
            'user' => $user ?? null,
        ]);
    }

    public function edit()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('edit-user-form') ?? [];

        $user = $this->getUser($formdata);
        $user->login = $formdata['login'];
        $user->email = $formdata['email'];

        $this->saveUser($user);
    }

    public function remove()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('user') ?? [];

        $curr_user = $this->getCurrentUser();
        $user = $this->getUser($formdata);

        if ($curr_user->getId() === $user->getId() && !$user->can('self-deleting')) {
            $message = "failed: forbidden to delete your account";
            $status = 'error';
        } else {
            $res = $user->delete();

            if ($res) {
                $message = "user '{$user->login}' removed";
                $status = 'complete';
            } else {
                $message = "'{$user->login}' removing failed";
                $status = 'error';
            }
        }

        return $this->toUrl('/users/list', [
            'user[id]' => $user->id,
            'message'  => $message,
            'status'   => $status,
        ]);
    }

    public function disable()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('edit-user-form') ?? [];

        $curr_user = $this->getCurrentUser();
        $user = $this->getUser($formdata);

        if ($curr_user->getId() === $user->getId() && !$user->can('self-disabling')) {
            return $this->toUrl('/users/edit-page', [
                'user[id]' => $user->id,
                'message' => 'failed: forbidden to disable your account',
            ]);
        }

        $user->setToken('auth', null);
        $user->status = User::STATUS_DISABLED;

        $this->saveUser($user);
    }

    public function enable()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('edit-user-form') ?? [];

        $user = $this->getUser($formdata);
        $user->status = User::STATUS_ENABLED;

        $this->saveUser($user);
    }

    public function setPassWord()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('edit-user-form') ?? [];

        $user = $this->getUser($formdata);
        $user->setPassword($formdata['password']);

        $this->saveUser($user);
    }

    public function logout()
    {
        $http = Core::getCurrentApp()->getHttp();
        $formdata = $http->post('edit-user-form') ?? [];

        $user = $this->getUser($formdata);

        Core::getCurrentApp()
            ->getAuthentication()
            ->logout($user);

        $this->saveUser($user);
    }
}
