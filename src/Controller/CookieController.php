<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Undocumented class
 * PHP Version 8.2.0.
 *
 * @category  Groups a series of packages together.
 * @package   Categorizes the associated element into a logical grouping or subdivision.
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class CookieController extends AbstractControllerWithEnv
{
    #[Route('/api/cookies', name: 'checkCookiesOnPageLoad', methods: array('GET'))]
    /**
     * Undocumented function.
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
