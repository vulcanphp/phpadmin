<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Middlewares;

use VulcanPhp\PhpRouter\Http\Request;
use VulcanPhp\PhpRouter\Http\Response;
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
