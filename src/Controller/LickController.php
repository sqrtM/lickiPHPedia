<?php

namespace App\Controller;

use App\Exception\PostgresConnectionException;
use App\Exception\PostgresQueryException;
use App\Service\LickCreator;
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
        $con = pg_connect($this->getConnectionString())
        or exit("Could not connect to server\n");

        $query = 'SELECT * FROM licks ORDER BY date';
        $results = pg_query($con, $query) or exit('Query failed: ' . pg_last_error($con));

        $table = pg_fetch_all($results);
        pg_close($con);

        return $this->json($table);
    }

    #[Route('/api/getLick', name: 'getLick', methods: array('POST'))]
    /**
     * Undocumented function.
     *
     * @param Request $request undocumented param
     */
    public function getLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con = pg_connect($this->getConnectionString())
        or exit("Could not connect to server\n");

        $query = "SELECT * FROM licks WHERE uuid = '{$incoming_uuid}'";
        $results = pg_query($con, $query) or exit('Query failed: ' . pg_last_error($con));
        $table = pg_fetch_all($results);

        pg_close($con);

        return $this->json($table);
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

    // this currently does not throw an exception if the UUID isn't found.
    // fix that later.
    #[Route('/api/licks', name: 'deleteLick', methods: array('DELETE'))]
    /**
     * Summary of deleteLick.
     */
    public function deleteLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->getConnectionString();

        $con = pg_connect($con_login)
        or exit("Could not connect to server\n");

        pg_prepare($con, 'deleteLick', 'DELETE FROM licks WHERE uuid = $1;');
        pg_send_execute($con, 'deleteLick', array($incoming_uuid))
        or exit('Query failed: ' . pg_last_error($con));

        unset($con, $con_login);

        return $this->json($incoming_uuid);
    }
}
