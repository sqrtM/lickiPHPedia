<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entities\DatabaseConnectionCredentials;

// TODO : when a lick has a valid parent ID, find that parent in the postgres database and give it a reference to its child. 


class LickController extends AbstractController
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

    #[Route('/api/licks', name: 'getAllLicks', methods: ['GET'])]
    public function getAllLicks(): JsonResponse
    {
        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        $query = 'SELECT * FROM licks';
        $results = pg_query($con, $query) or die('Query failed: ' . pg_last_error());

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con);
        unset($con_login);

        return $this->json($table);
    }

    #[Route('/api/getLick', name: 'getLick', methods: ['POST'])]
    public function getLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        $query = "SELECT * FROM licks WHERE uuid = '{$incoming_uuid}'";
        $results = pg_query($con, $query) or die('Query failed: ' . pg_last_error());
        $table = pg_fetch_all($results);

        pg_close($con);
        unset($con);
        unset($con_login);

        return $this->json($table);
    }


    #[Route('/api/licks', name: "createNewLick", methods: ['POST'])]
    public function createNewLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};
        $incoming_music_string = json_decode($request->getContent())->{'music_string'};
        $incoming_parent = json_decode($request->getContent())->{'parent'};
        $incoming_date = json_decode($request->getContent())->{'date'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "createNewLick", "INSERT INTO licks (uuid, music_string, parent, date) VALUES ($1, $2, $3, $4);");
        pg_send_execute($con, "createNewLick", [$incoming_uuid, $incoming_music_string, $incoming_parent, $incoming_date])
            or die('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con);
        unset($con_login);

        return $this->json(json_decode($request->getContent()));
    }

    // this currently does not throw an exception if the UUID isn't found.
    // fix that later.
    #[Route('/api/licks', name: "deleteLick", methods: ['DELETE'])]
    public function deleteLick(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "deleteLick", "DELETE FROM licks WHERE uuid = $1;");
        pg_send_execute($con, "deleteLick", [$incoming_uuid]) or die('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con);
        unset($con_login);

        return $this->json($incoming_uuid);
    }
}