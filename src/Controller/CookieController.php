<?php

namespace App\Controller;

use App\Entities\DatabaseConnectionCredentials;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Undocumented class
 * PHP Version 8.2.0
 *
 * @category  Groups a series of packages together.
 * @package   Categorizes the associated element into a logical grouping or subdivision.
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class CookieController extends AbstractController
{
    /**
     * Undocumented function
     *
     * @return DatabaseConnectionCredentials
     */
    private function initEnv(): DatabaseConnectionCredentials
    {
        return new DatabaseConnectionCredentials(
            $this->getParameter('app.dbhost'),
            $this->getParameter('app.dbuser'),
            $this->getParameter('app.dbpass'),
            $this->getParameter('app.dbname'),
        );
    }

    #[Route('/api/cookies', name: 'checkCookiesOnPageLoad', methods: array('GET'))]
    /**
     * Undocumented function
     *
     * @return JsonResponse
     */
    public function checkCookiesOnPageLoad(): JsonResponse
    {
        if (count($_COOKIE) > 0) {
            return $this->json($_COOKIE);
        } else {
            return $this->json('no bitches');
        }
    }
}
