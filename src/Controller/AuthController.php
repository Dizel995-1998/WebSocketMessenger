<?php

namespace Controller;

use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\ValidationError;
use Psr\Http\Message\ResponseInterface;
use Rakit\Validation\Validator;

class AuthController
{
    // todo обращение к сервисному слою для получения токена
    public function getJwtToken(Request $request, Validator $validator, JwtToken $jwtToken) : ResponseInterface
    {
        $validation = $validator->make(
            $request->get(),
            [
                'login' => 'required|alpha',
                'password' => 'required|alpha',
            ]
        );

        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationError($validation->errors()->toArray());
        }

        // обращение к БД

        $jwtToken->setPayload([
            'user_id' => '12312313',
            'some_information_about_user' => '....'
        ]);

        return new \Lib\Response\JsonResponse(200, [], ['jwtToken' => (string) $jwtToken]);
    }
}