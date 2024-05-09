<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Interfaces\DataBaseModelInterface;
use DiplomaProject\Core\Interfaces\UserInterface;
use DiplomaProject\Core\Models\UrlParams;
use DiplomaProject\Core\Models\ViewParams;

class Controller
{
    public function showView(
        string $view_name,
        array $params = [],
        array $page_params = [],
        int $http_code = 200
    ): ViewParams {
        $view_params = new ViewParams($view_name, $http_code);
        $view_params->params = $params ?? [];
        $view_params->page_params = $page_params ?? [];
        $view_params->is_user_logged_in = $this->isCurrUserLoggedIn();

        return $view_params;
    }

    public function toUrl(
        string $uri,
        array $get_params = [],
        int $http_code = 303
    ): UrlParams {
        $url_params = new UrlParams($uri, $http_code);
        $url_params->params = $get_params;

        return $url_params;
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

        return ($user->validateToken() && $user->isAdmin());
    }

    public function isCurrUserLoggedIn(): bool
    {
        $authentication = Core::getCurrentApp()->getAuthentication();
        $user = $authentication->getCurrentUser();

        return ($user->validateToken());
    }
}
