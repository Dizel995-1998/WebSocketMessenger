<?php

namespace Middleware;

abstract class UserMiddleware extends BaseMiddleware
{
    const LOGIN_RULE = 'required|email|min:6';
    const NAME_RULE = 'required|alpha_spaces|min:4';
    const PASSWORD_RULE = 'required|alpha_dash|min:8';
    const STATUS_RULE = 'alpha_spaces';
}