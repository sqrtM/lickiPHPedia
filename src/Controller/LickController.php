<?php

namespace App\Controller;

use App\Exception\NoMatchingLickException;
use App\Exception\PostgresConnectionException;
use App\Exception\PostgresQueryException;
use App\Service\LickCreator;
use App\Service\LickManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
class LickController extends AbstractControllerWithEnv
{
    #[Route('/api/licks', name: 'getAllLicks', methods: array('GET'))]
    /**
     * Undocumented function.
     */
    public function getAllLicks(): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $lickManager = new LickManager($con);
            $licks = $lickManager->getAllLicks();
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }
        return $this->json($licks);
    }

    #[Route('/api/getLick', name: 'getLick', methods: array('POST'))]
    /**
     * Undocumented function.
     *
     * @param Request $request undocumented param
     */
    public function getLick(Request $request): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $lickManager = new LickManager($con, $request);
            $lick = $lickManager->getLick();
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }
        return $this->json($lick);
    }

    #[Route('/api/licks', name: 'createNewLick', methods: array('POST'))]
    /**
     * Undocumented function.
     *
     * @param Request $request undocumented param
     */
    public function createNewLick(Request $request): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $lickCreator = new LickCreator($request, $con);
            $lickCreator->insertLickIntoDatabase();
            if ($lickCreator->hasParent()) {
                $lickCreator->appendChildToParent();
            }
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }

        return $this->json(json_decode($request->getContent()));
    }

    #[Route('/api/licks', name: 'deleteLick', methods: array('DELETE'))]
    public function deleteLick(Request $request): JsonResponse
    {
        try {
            $con = pg_connect($this->getConnectionString())
            or throw new PostgresConnectionException();

            $lick = new LickManager($con, $request);
            $lick->exists() ? $lick->delete() : throw new NoMatchingLickException();
        } catch (PostgresQueryException | PostgresConnectionException $e) {
            echo $e->getMessage();
        } finally {
            pg_close($con);
        }
        return $this->json(true);
    }
}
