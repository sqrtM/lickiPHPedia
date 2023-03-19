<?php

// TODO : cleanup all api URIs

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
class UserController extends AbstractController
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

    /**
     * Get User.
     *
     * Takes an email and a password field and searches the postgres base to find a match.
     *
     * @return jsonResponse boolean JsonResponse based on if a match has been found.
     */
    #[Route('/api/loginUser', name: 'loginUser', methods: array('POST'))]
    public function loginUser(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
            or exit("Could not connect to server\n");

        pg_prepare($con, 'loginUser', 'SELECT id FROM users WHERE email = $1 AND password = crypt($2, password);');
        $results = pg_execute($con, 'loginUser', array($incoming_email, $incoming_password));

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con, $con_login);

        return $this->json(count($table) > 0 ? true : false);
    }

    // password is 72 max char
    #[Route('/api/createUser', name: 'createUser', methods: array('POST'))]
    public function createUser(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_password = json_decode($request->getContent())->{'password'};

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
            or exit("Could not connect to server\n");

        $query = "SELECT * FROM users WHERE email = '{$incoming_email}'";
        $results = pg_query($con, $query) or exit('Query failed: ' . pg_last_error());
        $table = pg_fetch_all($results);

        if (0 == count($table)) {
            pg_prepare(
                $con,
                'createUser',
                "INSERT INTO users (email, password) VALUES ($1, crypt($2, gen_salt('bf')));"
            );
            pg_send_execute($con, 'createUser', array($incoming_email, $incoming_password))
                or exit('Query failed: ' . pg_last_error());
            // set a cookie...
        }

        pg_close($con);
        unset($con, $con_login);

        return $this->json(count($table) > 0 ? false : true);
    }

    #[Route('/api/users/licks', name: 'getSavedLicks', methods: array('POST'))]
    /**
     * Undocumented function.
     *
     * @param Request $request undocumented param
     */
    public function getSavedLicks(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
            or exit("Could not connect to server\n");

        pg_prepare($con, 'getSavedLicks', 'SELECT saved_licks FROM users WHERE email = $1');
        $results = pg_execute($con, 'getSavedLicks', array($incoming_email))
            or exit('Query failed: ' . pg_last_error());

        $table = pg_fetch_all($results);
        pg_close($con);
        unset($con, $con_login);

        return $this->json($table);
    }

    #[Route('/api/users/licks', name: 'addSavedLick', methods: array('PATCH'))]
    /**
     * Undocumented function.
     *
     * @param Request $request undocumented param
     */
    public function addSavedLick(Request $request): JsonResponse
    {
        $incoming_email = json_decode($request->getContent())->{'email'};
        $incoming_uuid = json_decode($request->getContent())->{'uuid'};

        $con_login = $this->initEnv();

        $con = pg_connect($con_login)
            or exit("Could not connect to server\n");

        pg_prepare(
            $con,
            'addSavedLick',
            'UPDATE users SET saved_licks = array_append(saved_licks, $1) WHERE email = $2;'
        );
        pg_send_execute($con, 'addSavedLick', array($incoming_uuid, $incoming_email))
            or exit('Query failed: ' . pg_last_error());

        pg_close($con);
        unset($con, $con_login);

        return $this->json(json_decode($request->getContent()));
    }
}
