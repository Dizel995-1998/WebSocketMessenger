<?php

namespace Controller;

use Entity\UserTable;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function me(Request $request): ResponseInterface
    {
        $jwtToken = JwtToken::parse($request->getHeaderLine('authorization'));
        return new SuccessResponse(UserTable::findByPrimaryKeyOrFail($jwtToken->getUserId()));
    }

    public function profile($id): ResponseInterface
    {
        return new SuccessResponse(UserTable::findByPrimaryKeyOrFail($id));
    }
}