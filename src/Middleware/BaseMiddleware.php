<?php

namespace Middleware;

use Lib\Middleware\IMiddleware;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Rakit\Validation\Validator;

abstract class BaseMiddleware implements IMiddleware
{
    abstract function getValidationRules() : array;

    protected function getValidationMessages() : array
    {
        return [];
    }

    public function __construct(protected Validator $validator) {}

    /**
     * @throws BadRequest
     */
    public function handle(Request $request)
    {
        $validationRules = $this->getValidationRules();
        $validation = $this->validator->make(
            $request->get(),
            $validationRules,
            $this->getValidationMessages()
        );

        $validation->validate();

        if ($validation->fails()) {
            // fixme: hardcode, необходим класс констант HTTP кодов
            throw new BadRequest($validation->errors()->toArray(), 422);
        }
    }
}