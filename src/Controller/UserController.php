<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
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

    // returns true if successful, false otherwise
    #[Route('/api/get_user', name: 'get_user', methods: ['POST'])]
    public function get_user(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login['dbhost']} dbname={$con_login['dbname']} user={$con_login['dbuser']} password={$con_login['dbpass']}")
            or die("Could not connect to server\n");

        pg_prepare($con, "post", "SELECT id FROM users WHERE email = $1 AND password = crypt($2, password);");
        $results = pg_execute($con, "post", [$incoming_email, $incoming_password]);

        // this is uncessesary, since it will always be binary 1 or 0. fix.
        $table = pg_fetch_all($results);
        pg_close($con);

        $finalVal = [];

        foreach ($table as &$value) {
            array_push($finalVal, $value);
        }
        //did the query return a match (i.e., length of one, since all users are unique) ? 
        //return true as the respose data.
        return $this->json(count($finalVal) > 0 ? true : false);
    }

    // password is 72 max char
    #[Route('/api/users', name: "createuser", methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login['dbhost']} dbname={$con_login['dbname']} user={$con_login['dbuser']} password={$con_login['dbpass']}")
            or die("Could not connect to server\n");

        pg_prepare($con, "post", "INSERT INTO users (email, password) VALUES ($1, crypt($2, gen_salt('bf')));");
        pg_send_execute($con, "post", [$incoming_email, $incoming_password])
            or die('Query failed: ' . pg_last_error());

        pg_close($con);
        
        return $this->json(json_decode($request->getContent()));
    }

}