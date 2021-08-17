<?php

namespace Controller;

use Entity\UserTable;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\ValidationError;
use Psr\Http\Message\ResponseInterface;
use Rakit\Validation\Validator;
use Service\PasswordHash;

class AuthController
{
    // todo обращение к сервисному слою для получения токена
    /**
     * @throws ValidationError
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
        $user = UserTable::findByPropertyOrFail('login', $validation->getValue('login'));

        if ($user->passwordHash != $passwordHash->hashPassword($validation->getValue('password'))) {
            throw new BadRequest('Некорректный пароль');
        }

        $jwtToken->setPayload(['user_id' => $user->id]);

        // TODO возвращать необходимо жкземляры классов типа SuccessResponse, FailResponse, BadRequest и т.д, чтобы не хардкодить коды ответа
        return new \Lib\Response\JsonResponse(200, ['jwtToken' => (string) $jwtToken]);
    }
}