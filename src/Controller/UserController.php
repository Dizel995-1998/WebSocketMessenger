<?php

namespace Controller;

use Entity\User;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function me(Request $request): ResponseInterface
    {
        $jwtToken = JwtToken::parse($request->getHeaderLine('authorization'));
        return new SuccessResponse(User::findByPrimaryKeyOrFail($jwtToken->getUserId()));
    }

    public function profile($id): ResponseInterface
    {
        return new SuccessResponse(User::findByPrimaryKeyOrFail($id));
    }
}