<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Middlewares;

use VulcanPhp\InputMaster\Request;
use VulcanPhp\InputMaster\Response;
use VulcanPhp\PhpRouter\Security\Interfaces\IMiddleware;

class AuthMiddleware implements IMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (auth()->isLogged()) {
            redirect(config('auth.redirect_in'));
        }
    }
}
