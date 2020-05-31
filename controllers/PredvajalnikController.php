<?php

require_once("database/PredvajalnikDB.php");
require_once("ViewHelper.php");

# Controller for handling books
class PredvajalnikController
{
    public static function home()
    {
        $playlists = ["playlists" => PredvajalnikDB::getAllPlaylists()];
        ViewHelper::render("./views/domov.php", $playlists);
    }

    public static function registracija()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Takes raw data from the request
            $json = file_get_contents('php://input');
            $params = json_decode($json, $assoc = true);
            // Params contain email and password
            // Pogledam če v bazi že obstaja uporabnik s tem naslovom
            if (!PredvajalnikDB::userExists($params["email"])) {
                // Geslo haširamo
                $passHashed = password_hash($params["email"], PASSWORD_BCRYPT);
                $registered = PredvajalnikDB::registerUser($params["email"], "Ime Priimek", $passHashed);
                if ($registered) {
                    // opravljena registracija
                    $dbUser = PredvajalnikDB::getUser($params->email);
                    header('Content-Type: application/json');
                    echo (json_encode($dbUser));
                    exit();
                }
            } else {
                $ret = [
                    "message" => "Uporabnik s tem emailom že obstaja."
                ];
                header('Content-Type: application/json');
                http_response_code(400);
                echo (json_encode($ret));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
    }

    public static function isciSeznam()
    {
        if (isset($_GET["query"]) && !empty($_GET["query"])) {
            $query = $_GET["query"];
            $seznami = PredvajalnikDB::SearchPlaylists($query);
            if (!$seznami) {
                $ctx = [
                    "message" => "Playlist does not exist."
                ];
                header('Content-Type: application/json');
                http_response_code(404);
                echo (json_encode($ctx));
                exit();
            } else {
                header('Content-Type: application/json');
                echo (json_encode($seznami));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Wrong parameters."
            ];
            header('Content-Type: application/json');
            http_response_code(400);
            echo (json_encode($ctx));
            exit();
        }
    }


    public static function novSeznam()
    {
        $headers = getallheaders();
        if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
            $token = explode(" ", $headers["Authorization"])[1];
            $user = PredvajalnikDB::getUserByToken($token);

            // dob naslov, dob seznam urljev
            $json = file_get_contents('php://input');
            $params = json_decode($json, $assoc = true);

            // najprej nared nov seznam
            $created = PredvajalnikDB::newPlaylist($params["title"], $user["id"]);
            // nato nanj daj vse komade
            if (!$created) {
                $ctx = [
                    "message" => "Error creating a playlist."
                ];
                header('Content-Type: application/json');
                http_response_code(500);
                echo (json_encode($ctx));
                exit();
            } else {
                $songList = $params["songs"];
                for ($i = 0; $i < count($songList); $i++) {
                    $s = $songList[$i];
                    PredvajalnikDB::addSong($created, $s);
                }
                $ctx = [
                    "message" => "Playlist created."
                ];
                header('Content-Type: application/json');
                http_response_code(201);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Wrong credentials."
            ];
            header('Content-Type: application/json');
            http_response_code(403);
            echo (json_encode($ctx));
            exit();
        }
        exit();
    }

    public static function novKomad()
    {
        $headers = getallheaders();
        if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
            $token = explode(" ", $headers["Authorization"])[1];
            $user = PredvajalnikDB::getUserByToken($token);

            // dob naslov, dob seznam urljev
            $json = file_get_contents('php://input');
            $params = json_decode($json, $assoc = true);

            $playlist = PredvajalnikDB::getPlaylist($params["playlist"]);

            if ($playlist["user"] != $user["id"]) {
                $ctx = [
                    "message" => "You do not have permission to edit this playlist."
                ];
                header('Content-Type: application/json');
                http_response_code(403);
                echo (json_encode($ctx));
                exit();
            }
            $songList = $params["songs"];
            for ($i = 0; $i < count($songList); $i++) {
                $s = $songList[$i];
                PredvajalnikDB::addSong($playlist["id"], $s);
            }
            $ctx = [
                "message" => "Songs added."
            ];
            header('Content-Type: application/json');
            http_response_code(201);
            echo (json_encode($ctx));
            exit();
        } else {
            $ctx = [
                "message" => "Wrong credentials."
            ];
            header('Content-Type: application/json');
            http_response_code(403);
            echo (json_encode($ctx));
            exit();
        }
    }

    public static function seznam()
    {
        if (isset($_GET["id"]) && !empty($_GET["id"])) {
            $id = $_GET["id"];
            $seznam = PredvajalnikDB::getPlaylist($id);
            // var_dump($seznam);
            if ($seznam) {
                $return = [
                    "playlist" => $seznam,
                    "songs" => array(),
                ];
                // $seznam = array($seznam);
                // dobi še vse skladbe iz seznama
                $glasbe = PredvajalnikDB::getSongs($id);
                if ($glasbe) {
                    $return["songs"] = $glasbe;
                }
                header('Content-Type: application/json');
                echo (json_encode($return));
                exit();
            } else {
                $ctx = [
                    "message" => "Playlist does not exist."
                ];
                header('Content-Type: application/json');
                http_response_code(404);
                echo (json_encode($ctx));
                exit();
            }
        }
    }

    public static function profil()
    {
        // Pridob userja prek tokena in ga vrn v jsonu
        $headers = getallheaders();
        if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
            $token = explode(" ", $headers["Authorization"])[1];
            $user = PredvajalnikDB::getUserByToken($token);
            if (!$user) {
                $ctx = [
                    "message" => "Wrong token."
                ];
                header('Content-Type: application/json');
                http_response_code(403);
                echo (json_encode($ctx));
                exit();
            }
            $return = [
                "user" => $user
            ];
            header('Content-Type: application/json');
            echo (json_encode($return));
        }
        exit();
    }


    public static function publicProfil()
    {
        if (isset($_GET["id"]) && !empty($_GET["id"])) {

            $user = PredvajalnikDB::getUserById($_GET["id"]);
            if (!$user) {
                $ctx = [
                    "message" => "User does not exist."
                ];
                header('Content-Type: application/json');
                http_response_code(404);
                echo (json_encode($ctx));
                exit();
            }
            header('Content-Type: application/json');
            echo (json_encode($user));
        } else {
            $return = [
                "message" => "Wrong arguments."
            ];
            header('Content-Type: application/json');
            echo (json_encode($return));
        }
        exit();
    }

    public static function prijava()
    {
        // Iz post vzem email pa username in returni token za tega userja
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $json = file_get_contents('php://input');
            $params = json_decode($json, $assoc = true);

            if (isset($params["email"]) && !empty($params["email"])) {
                if (isset($params["password"]) && !empty($params["password"])) {

                    $email = $params["email"];
                    $password = $params["password"];

                    $user = PredvajalnikDB::getUser($email);
                    if (!$user) {
                        $ctx = [
                            "message" => "User with this email doesn't exist."
                        ];
                        header('Content-Type: application/json');
                        http_response_code(400);
                        echo (json_encode($ctx));
                        exit();
                    }

                    if (password_verify($password, $user["password"])) {
                        header('Content-Type: application/json');
                        http_response_code(200);
                        echo (json_encode($user));
                        exit();
                    } else {
                        $ctx = [
                            "message" => "Wrong credentials."
                        ];
                        header('Content-Type: application/json');
                        http_response_code(403);
                        echo (json_encode($ctx));
                        exit();
                    }
                } else {
                    $ctx = [
                        "message" => "Password missing."
                    ];
                    http_response_code(400);
                    echo (json_encode($ctx));
                    exit();
                }
            } else {
                $ctx = [
                    "message" => "Email missing."
                ];
                http_response_code(400);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
        exit();
    }

    public static function lestvica()
    {
        $playlisti = PredvajalnikDB::getPlaylistChart();
        echo (json_encode($playlisti));
        exit();
    }
    public static function vsi()
    {
        $playlisti = PredvajalnikDB::getAllPlaylists();
        echo (json_encode($playlisti));
        exit();
    }

    public static function izbrisiKomad()
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
            // preberi podatke
            $json = file_get_contents('php://input');
            $params = json_decode($json, $assoc = true);
            if (isset($params["id"]) && !empty($params["id"])) {
                $id = $params["id"];
                // Delete from db
                $deleted = PredvajalnikDB::deleteSong($id);
                if ($deleted) {
                    $ctx = [
                        "message" => "Song deleted."
                    ];
                    header('Content-Type: application/json');
                    http_response_code(200);
                    echo (json_encode($ctx));
                    exit();
                } else {
                    $ctx = [
                        "message" => "Error deleting song."
                    ];
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo (json_encode($ctx));
                    exit();
                }
            } else {
                $ctx = [
                    "message" => "Wrong parameters."
                ];
                header('Content-Type: application/json');
                http_response_code(400);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
    }

    public static function oceniSeznam()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $headers = getallheaders();
            if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
                $token = explode(" ", $headers["Authorization"])[1];
                $user = PredvajalnikDB::getUserByToken($token);
                $json = file_get_contents('php://input');
                $params = json_decode($json, $assoc = true);
                if ((isset($params["playlist"]) && !empty($params["playlist"])) && (isset($params["action"]) && !empty($params["action"]))) {
                    // povečaj/zmanjšaj rating na samem playlistu
                    $playlist_id = $params["playlist"];
                    $action = $params["action"];
                    if ($action == "up") {
                        PredvajalnikDB::incrementVote($playlist_id);
                    } else {
                        PredvajalnikDB::decrementVote($playlist_id);
                    }

                    $ctx = [
                        "message" => "Voted successfully."
                    ];
                    header('Content-Type: application/json');
                    http_response_code(201);
                    echo (json_encode($ctx));
                    exit();
                } else {
                    $ctx = [
                        "message" => "Bad request."
                    ];
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo (json_encode($ctx));
                    exit();
                }
            } else {
                $ctx = [
                    "message" => "Forbidden."
                ];
                header('Content-Type: application/json');
                http_response_code(403);
                echo (json_encode($ctx));
                exit();
            }
            // preberi podatke

        }
    }

    public static function mojiSeznami()
    {
        $headers = getallheaders();
        if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
            $token = explode(" ", $headers["Authorization"])[1];
            $user = PredvajalnikDB::getUserByToken($token);

            header('Content-Type: application/json');
            $playlists = PredvajalnikDB::getPlaylistsByUser($user["id"]);
            echo (json_encode($playlists));
            exit();
        } else {
            $ctx = [
                "message" => "Forbidden."
            ];
            header('Content-Type: application/json');
            http_response_code(403);
            echo (json_encode($ctx));
            exit();
        }
    }

    public static function uporabnikoviSeznami()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            if (isset($_GET["user"]) && !empty($_GET["user"])) {
                $id = $_GET["user"];
                $lists = PredvajalnikDB::getPlaylistsByUser($id);
                header('Content-Type: application/json');
                echo (json_encode($lists));
                exit();
            }else{
                $ctx = [
                    "message" => "Bad request."
                ];
                header('Content-Type: application/json');
                http_response_code(400);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
    }


    public static function dodajVZbirko()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $headers = getallheaders();
            if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
                $token = explode(" ", $headers["Authorization"])[1];
                $user = PredvajalnikDB::getUserByToken($token);

                $json = file_get_contents('php://input');
                $params = json_decode($json, $assoc = true);

                $created = PredvajalnikDB::addPlaylistToLibrary($params["playlist"], $user["id"]);
                if ($created) {
                    $ctx = [
                        "message" => "Playlist added to library."
                    ];
                    header('Content-Type: application/json');
                    http_response_code(201);
                    echo (json_encode($ctx));
                    exit();
                }
                $ctx = [
                    "message" => "Error adding a playlist."
                ];
                header('Content-Type: application/json');
                http_response_code(500);
                echo (json_encode($ctx));
                exit();
            } else {
                $ctx = [
                    "message" => "Forbidden."
                ];
                header('Content-Type: application/json');
                http_response_code(403);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
    }


    public static function mojaKnjiznica()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $headers = getallheaders();
            if (isset($headers["Authorization"]) && !empty($headers["Authorization"])) {
                $token = explode(" ", $headers["Authorization"])[1];
                $user = PredvajalnikDB::getUserByToken($token);



                $playlists = PredvajalnikDB::getUserLibrary($user["id"]);
                header('Content-Type: application/json');
                echo (json_encode($playlists));
                exit();
            } else {
                $ctx = [
                    "message" => "Forbidden."
                ];
                header('Content-Type: application/json');
                http_response_code(403);
                echo (json_encode($ctx));
                exit();
            }
        } else {
            $ctx = [
                "message" => "Method not allowed."
            ];
            header('Content-Type: application/json');
            http_response_code(405);
            echo (json_encode($ctx));
            exit();
        }
    }
}
