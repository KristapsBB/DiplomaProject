<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Interfaces\DataBaseModelInterface;
use DiplomaProject\Core\Interfaces\UserInterface;
use DiplomaProject\Core\Response;
use DiplomaProject\Models\User;

class Controller
{
    public function showView(
        string $view_name,
        array $params = [],
        array $page_params = [],
        int $http_code = 200
    ): Response {
        $viewer = Core::getCurrentApp()->getViewer();
        $page_params['is_user_logged_in'] = $this->isCurrUserLoggedIn();
        $page_params['is_admin'] = $this->isAdmin();

        ob_start();
        $viewer->showLayout($view_name, $params, $page_params, $http_code);
        $body = ob_get_contents();
        ob_end_clean();

        return (new Response(
            Response::TYPE_HTML,
            body: $body,
            http_code: $http_code
        ));
    }

    public function toUrl(string $uri, array $get_params = [], int $http_code = 303)
    {
        $http = Core::getCurrentApp()->getHttp();

        $url = $http->generateUrl($uri, $get_params);
        $http->redirect($url, $http_code);

        return null;
    }

    public function sendJson(array $data, int $http_code = 200): Response
    {
        $body = json_encode($data, JSON_HEX_QUOT);

        return (new Response(
            Response::TYPE_JSON,
            body: $body,
            http_code: $http_code
        ));
    }

    public function sendFile(string $path, int $http_code = 200): Response
    {
        $headers = [
            ['Content-Description: File Transfer'],
            ['Content-Type: application/octet-stream'],
            ['Content-Disposition: attachment; filename=' . basename($path)],
            ['Content-Transfer-Encoding: binary'],
            ['Content-Length: ' . filesize($path)],
        ];

        $body = file_get_contents($path);

        return (new Response(
            Response::TYPE_FILE,
            body: $body,
            headers: $headers,
            http_code: $http_code
        ));
    }

    /**
     * If $user is empty, the current user will be used
     */
    public function isAdmin(null | (UserInterface & DataBaseModelInterface) $user = null): bool
    {
        if (null === $user) {
            $authentication = Core::getCurrentApp()->getAuthentication();
            $user = $authentication->getCurrentUser();
        }

        return ($user->validateToken('auth') && $user->isAdmin());
    }

    public function isCurrUserLoggedIn(): bool
    {
        $authentication = Core::getCurrentApp()->getAuthentication();
        $user = $authentication->getCurrentUser();

        return ($user->validateToken('auth'));
    }


    /**
     * @return User
     */
    public function getCurrentUser()
    {
        return Core::getCurrentApp()
            ->getAuthentication()
            ->getCurrentUser();
    }
}
