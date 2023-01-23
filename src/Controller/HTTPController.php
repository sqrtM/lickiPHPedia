<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HTTPController extends AbstractController
{

    private string $host = "rogue.db.elephantsql.com";
    private string $user = "xlxmgvws";
    private string $pass = "z96UT3uau2iSa2ws_zUz0g6LLADluT0k";
    private string $db = "xlxmgvws";
    

    #[Route('/api/licks', name: 'licks', methods: ['GET'])]
    public function get(): JsonResponse
    {

        $con = pg_connect("host=$this->host dbname=$this->db user=$this->user password=$this->pass")
            or die ("Could not connect to server\n");

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


    #[Route('/api/licks', name:"createlick", methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        $incoming_title = json_decode($request->getContent())->{'title'};
        $incoming_lick = json_decode($request->getContent())->{'lick'};

        $con = pg_connect("host=$this->host dbname=$this->db user=$this->user password=$this->pass")
            or die ("Could not connect to server\n");

        pg_prepare($con, "post", "INSERT INTO licks (title, lick) VALUES ($1, $2);");
        pg_send_execute($con, "post", [$incoming_title, $incoming_lick]);

        pg_close($con);

        return $this->json(json_decode($request->getContent()));
    }

}