<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LickController extends AbstractController
{
    private function init_env() 
    {
        return array(
            "dbhost" => $this->getParameter('app.dbhost'),
            "dbuser" => $this->getParameter('app.dbuser'),
            "dbpass" => $this->getParameter('app.dbpass'),
            "dbname" => $this->getParameter('app.dbname'),
        );
    }

    #[Route('/api/licks', name: 'licks', methods: ['GET'])]
    public function get(): JsonResponse
    {

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login['dbhost']} dbname={$con_login['dbname']} user={$con_login['dbuser']} password={$con_login['dbpass']}")
            or die("Could not connect to server\n");

        $query = 'SELECT * FROM licks';
        $results = pg_query($con, $query) or die('Query failed: ' . pg_last_error());

        $table = pg_fetch_all($results);
        pg_close($con);

        $finalVal = [];

        foreach ($table as &$value) {
            array_push($finalVal, $value);
        }

        return $this->json($finalVal);
    }


    #[Route('/api/licks', name: "createlick", methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};
        $incoming_music_string = json_decode($request->getContent())->{'music_string'};
        $incoming_parent = json_decode($request->getContent())->{'parent'};
        $incoming_date = json_decode($request->getContent())->{'date'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login['dbhost']} dbname={$con_login['dbname']} user={$con_login['dbuser']} password={$con_login['dbpass']}")
            or die("Could not connect to server\n");

        pg_prepare($con, "post", "INSERT INTO licks (uuid, music_string, parent, date) VALUES ($1, $2, $3, $4);");
        pg_send_execute($con, "post", [$incoming_uuid, $incoming_music_string, $incoming_parent, $incoming_date])
            or die('Query failed: ' . pg_last_error());

        pg_close($con);

        return $this->json(json_decode($request->getContent()));
    }

    // this currently does not throw an exception if the UUID isn't found.
    // fix that later.
    #[Route('/api/licks', name: "deletelick", methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login['dbhost']} dbname={$con_login['dbname']} user={$con_login['dbuser']} password={$con_login['dbpass']}")
            or die("Could not connect to server\n");

        pg_prepare($con, "delete", "DELETE FROM licks WHERE uuid = $1;");
        pg_send_execute($con, "delete", [$incoming_uuid]) or die ('Query failed: ' . pg_last_error());

        pg_close($con);
        return $this->json($incoming_uuid);
    }
}