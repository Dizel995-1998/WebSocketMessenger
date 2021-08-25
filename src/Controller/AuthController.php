<?php

namespace Controller;

use Entity\User;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\ServerInternalError;
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
            $user = User::findByPropertyOrFail([
                'login' => $validation->getValue('login'),
                'passwordHash' => $passwordHash->hashPassword($validation->getValue('password'))
            ]);
        } catch (\RuntimeException $e) {
            throw new BadRequest('Invalid login or password');
        }

        $jwtToken->setUserId($user->getId());

        return new SuccessResponse(['jwtToken' => (string) $jwtToken]);
    }

    /**
     * @throws BadRequest
     * @throws ServerInternalError
     */
    public function createUser(Request $request, Validator $validator, PasswordHash $passwordHash): SuccessResponse
    {
        $validation = $validator->make(
            $request->get(),
            [
                'login' => 'required|alpha_dash',
                'password' => 'required|alpha_dash',
                'name' => 'required|alpha_dash',
                'avatar' => 'alpha_dash'
            ]
        );

        $validation->validate();

        if ($validation->fails()) {
            throw new BadRequest($validation->errors()->toArray());
        }

        if (User::findByProperty(['login' => $request->get('login')])) {
            throw new BadRequest('User already exist');
        }

        $user = (new User())
            ->setName($request->get('name'))
            ->setLogin($request->get('login'))
            ->setPasswordHash($passwordHash->hashPassword($request->get('password')))
            ->setPictureUrl($request->get('avatar'));

        if (!$user->save()) {
            throw new ServerInternalError('Cannot create user');
        }

        return new SuccessResponse('ok');
    }
}