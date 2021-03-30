<?php

namespace mmo\sf\JWTAuthenticationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class LogoutAction
{
    public function __invoke(): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
