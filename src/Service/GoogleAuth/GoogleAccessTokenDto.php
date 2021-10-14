<?php

namespace Service\GoogleAuth;

class GoogleAccessTokenDto
{
    public function __construct(
        protected string $id,
        protected string $email,
        protected string $name,
        protected ?string $pictureUrl = null
    ) {

    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPictureUrl() : ?string
    {
        return $this->pictureUrl;
    }
}