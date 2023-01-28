<?php

// TODO : cleanup all api URIs 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entities\DatabaseConnectionCredentials;


class CookieController extends AbstractController
{
    private function init_env(): DatabaseConnectionCredentials
    {
        return new DatabaseConnectionCredentials(
            $this->getParameter('app.dbhost'),
            $this->getParameter('app.dbuser'),
            $this->getParameter('app.dbpass'),
            $this->getParameter('app.dbname'),
        );
    }

    #[Route('/api/cookies', name: 'checkCookiesOnPageLoad', methods: ['GET'])]
    public function checkCookiesOnPageLoad(): JsonResponse
    {
        if(count($_COOKIE) > 0) {
            return $this->json($_COOKIE);
          } else {
            return $this->json("no bitches");
          }
    }
}