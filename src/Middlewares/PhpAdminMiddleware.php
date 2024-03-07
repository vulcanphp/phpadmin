<?php

namespace VulcanPhp\PhpAdmin\Middlewares;

use VulcanPhp\InputMaster\Request;
use VulcanPhp\InputMaster\Response;
use VulcanPhp\PhpRouter\Security\Interfaces\IMiddleware;

class PhpAdminMiddleware implements IMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (auth()->isGuest()) {
            redirect(auth_url('login'));
        } elseif (auth()->hasRoles(config('phpadmin.require_auth')) === false) {
            auth()->attemptLogout();
            redirect(auth_url('login'));
        }
    }
}
