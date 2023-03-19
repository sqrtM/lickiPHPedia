<?php

namespace App\Controller;

use App\Entities\DatabaseConnectionCredentials;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class LickController extends AbstractController
{
    /**
     * Undocumented function.
     */
    private function initEnv(): string
    {
        $dbCredentials = new DatabaseConnectionCredentials(
            $this->getParameter('app.dbhost'),
            $this->getParameter('app.dbuser'),
            $this->getParameter('app.dbpass'),
            $this->getParameter('app.dbname'),
        );
        return $dbCredentials->connectionString();
    }

    #[Route('/api/licks', name: 'getAllLicks', methods: array('GET'))]
    /**
     * Undocumented function.
     */
    public function getAllLicks(): JsonResponse
    {
        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
        or exit("Could not connect to server\n");

        $query = 'SELECT * FROM licks ORDER BY date';
        $results = pg_query($con, $query) or exit('Query failed: ' . pg_last_error());

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con, $con_login);

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

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
        or exit("Could not connect to server\n");

        $query = "SELECT * FROM licks WHERE uuid = '{$incoming_uuid}'";
        $results = pg_query($con, $query) or exit('Query failed: ' . pg_last_error());
        $table = pg_fetch_all($results);

        pg_close($con);
        unset($con, $con_login);

        return $this->json($table);
    }

    #[Route('/api/licks', name: 'createNewLick', methods: array('POST'))]
    /**
     * Summary of createNewLick.
     */
    public function createNewLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};
        $incoming_music_string = json_decode($request->getContent())->{'music_string'};
        $incoming_parent = json_decode($request->getContent())->{'parent'};
        $incoming_date = json_decode($request->getContent())->{'date'};

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
        or exit("Could not connect to server\n");

        pg_prepare(
            $con,
            'createNewLick',
            'INSERT INTO licks (uuid, music_string, parent, date) VALUES ($1, $2, $3, $4);'
        );

        pg_send_execute(
            $con,
            'createNewLick',
            array($incoming_uuid, $incoming_music_string, $incoming_parent, $incoming_date)
        ) or exit('Query failed: ' . pg_last_error());

        if (strlen($incoming_parent) > 0) {
            pg_prepare($con, 'addChild', 'UPDATE licks SET children = array_append(children, $1) WHERE uuid = $2;');
            pg_send_execute($con, 'addChild', array($incoming_uuid, $incoming_parent))
            or exit('Query failed: ' . pg_last_error());
        }

        pg_close($con);
        unset($con, $con_login);

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

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
        or exit("Could not connect to server\n");

        pg_prepare($con, 'deleteLick', 'DELETE FROM licks WHERE uuid = $1;');
        pg_send_execute($con, 'deleteLick', array($incoming_uuid))
        or exit('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con, $con_login);

        return $this->json($incoming_uuid);
    }
}
