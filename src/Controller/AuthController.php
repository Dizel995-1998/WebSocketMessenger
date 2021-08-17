<?php

namespace Controller;

use Entity\UserTable;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Rakit\Validation\Validator;
use Service\PasswordHash;

class AuthController
{
    // todo обращение к сервисному слою для получения токена
    /**
     * @throws BadRequest
     */
    public function getJwtToken(Request $request, Validator $validator, JwtToken $jwtToken, PasswordHash $passwordHash) : ResponseInterface
    {
        $validation = $validator->make(
            $request->get(),
            [
                'login' => 'required|alpha_dash',
                'password' => 'required|alpha_dash',
            ]
        );

        $validation->validate();

        if ($validation->fails()) {
            throw new BadRequest($validation->errors()->toArray());
        }

        // todo необходимо реализовать экранирование, возможность поиска по нескольким полям
        try {
            $user = UserTable::findByPropertyOrFail([
                'login' => $validation->getValue('login'),
                'passwordHash' => $passwordHash->hashPassword($validation->getValue('password'))
            ]);
        } catch (\RuntimeException $e) {
            throw new BadRequest('Incorrect fill password or login field');
        }

        $jwtToken->setPayload(['user_id' => $user->id]);

        // TODO возвращать необходимо жкземляры классов типа SuccessResponse, BadRequest и т.д, чтобы не хардкодить коды ответа
        return new SuccessResponse(['jwtToken' => (string) $jwtToken]);
    }
}