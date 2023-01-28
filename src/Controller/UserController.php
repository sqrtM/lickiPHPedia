<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entities\DatabaseConnectionCredentials;


class UserController extends AbstractController
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

    // returns true if successful, false otherwise

    /**
     * Get User
     * 
     * Takes an email and a password field and searches the postgres base to find a match.
     * 
     * @return jsonResponse boolean JsonResponse based on if a match has been found.
     */
    #[Route('/api/get_user', name: 'loginUser', methods: ['POST'])]
    public function loginUser(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "loginUser", "SELECT id FROM users WHERE email = $1 AND password = crypt($2, password);");
        $results = pg_execute($con, "loginUser", [$incoming_email, $incoming_password]);

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con); unset($con_login);

        return $this->json(count($table) > 0 ? true : false);
    }

    // password is 72 max char
    #[Route('/api/users', name: "createUser", methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "createUser", "INSERT INTO users (email, password) VALUES ($1, crypt($2, gen_salt('bf')));");
        pg_send_execute($con, "createUser", [$incoming_email, $incoming_password])
            or die('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con); unset($con_login);
        
        return $this->json(json_decode($request->getContent()));
    }

    #[Route('/api/users/licks', name: "getSavedLicks", methods: ['POST'])]
    public function getSavedLicks(Request $request): JsonResponse
    {   
        $incoming_email = json_decode($request->getContent())->{'email'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "getSavedLicks", "SELECT saved_licks FROM users WHERE email = $1");
        $results = pg_execute($con, "getSavedLicks", [$incoming_email])
            or die('Query failed: ' . pg_last_error());

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con); unset($con_login);

        return $this->json($table);
    }

    #[Route('/api/users/licks', name: "addSavedLick", methods: ['PATCH'])]
    public function addSavedLick(Request $request): JsonResponse
    {   
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->init_env();

        $con = pg_connect("host={$con_login->host()} dbname={$con_login->name()} user={$con_login->user()} password={$con_login->pass()}")
            or die("Could not connect to server\n");

        pg_prepare($con, "addSavedLick", "UPDATE users SET saved_licks = array_append(saved_licks, $1) WHERE email = $2;");
        pg_send_execute($con, "addSavedLick", [$incoming_uuid, $incoming_email])
            or die('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con); unset($con_login);

        return $this->json(json_decode($request->getContent()));
    }
}