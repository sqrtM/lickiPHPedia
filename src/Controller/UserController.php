<?php

// TODO : cleanup all api URIs

namespace App\Controller;

use App\Exception\EmailAlreadyInUseException;
use App\Exception\InvalidPasswordException;
use App\Exception\NoMatchingLickException;
use App\Exception\PostgresConnectionException;
use App\Exception\PostgresQueryException;
use App\Service\UserCreator;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FULLY REFACTORED.
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
class UserController extends AbstractControllerWithEnv
{
    #[Route('/api/loginUser', name: 'loginUser', methods: array('POST'))]
    public function loginUser(Request $request): JsonResponse
    {
        $userInfo = false;
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $userManager = new UserManager($request, $con);
            $userInfo = $userManager->getUserInfo();
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }
        return $this->json($userInfo);
    }

    /**
     * When the function finds that another, already existing account is
     * using the email which was provided by the user, it will throw an exception.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/createUser', name: 'createUser', methods: array('POST'))]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $userCreator = new UserCreator($request, $con);
            $userInfo = $userCreator->getUserInfo();
            empty($userInfo) ? $userCreator->createUser() : throw new EmailAlreadyInUseException();
        } catch (PostgresQueryException | InvalidPasswordException | EmailAlreadyInUseException $e) {
            $exception = $e->getMessage();
        } finally {
            pg_close($con);
        }
        return $this->json(!empty($userInfo) ? $exception : true);
    }

    #[Route('/api/users/licks', name: 'getSavedLicks', methods: array('POST'))]
    public function getSavedLicks(Request $request): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $userManager = new UserManager($request, $con);
            $savedLicks = $userManager->getSavedLicks();
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }

        return $this->json($savedLicks);
    }

    #[Route('/api/users/licks', name: 'addSavedLick', methods: array('PATCH'))]
    public function addSavedLick(Request $request): JsonResponse
    {

        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $userManager = new UserManager($request, $con);
            $userManager->addSavedLick();
        } catch (
            PostgresQueryException |
            PostgresConnectionException |
            NoMatchingLickException $e
        ) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }
        pg_close($con);
        return $this->json(json_decode($request->getContent()));
    }
}
