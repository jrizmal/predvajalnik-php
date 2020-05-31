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
            $params = json_decode($json);
            // Params contain email and password
            // Pogledam če v bazi že obstaja uporabnik s tem naslovom
            if (!PredvajalnikDB::userExists($params->email)) {
                // Geslo haširamo
                $passHashed = password_hash($params->email, PASSWORD_BCRYPT);
                $registered = PredvajalnikDB::registerUser($params->email, "default", $passHashed);
                if ($registered) {
                    // opravljena registracija
                    $dbUser = PredvajalnikDB::getUser($params->email);
                    echo (json_encode($dbUser));
                }
            } else {
                $ret = [
                    "message" => "Uporabnik s tem emailom že obstaja."
                ];
                http_response_code(400);
                echo (json_encode($ret));
            }
        } else {
            echo ("Loooool");
        }
        exit(0);
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
            $params = json_decode($json, $assoc=true);

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
            $params = json_decode($json);


            if (isset($params->email) && !empty($params->email)) {
                if (isset($params->password) && !empty($params->password)) {

                    $email = $params->email;
                    $password = $params->password;

                    $user = PredvajalnikDB::getUser($email);
                    if (!$user) {
                        $ctx = [
                            "message" => "User with this email doesn't exist."
                        ];
                        http_response_code(400);
                        echo (json_encode($ctx));
                        exit();
                    }
                    if (password_verify($password, $user["password"])) {
                        echo (json_encode($user));
                        exit();
                    } else {
                        $ctx = [
                            "message" => "Wrong credentials."
                        ];
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
                "message" => "Wrong method. Only POST allowed."
            ];
            http_response_code(400);
            echo (json_encode($ctx));
            exit();
        }
        exit();
    }
    public static function odjava()
    {
        session_start();
        $_SESSION = array();
        session_destroy();
        ViewHelper::redirect(BASE_URL);
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
    public static function priljubljeni()
    {
        ViewHelper::render("./views/priljubljeni.php");
    }
    public static function moji()
    {
        ViewHelper::render("./views/moji.php");
    }









    public static function get()
    {
        $variables = ["book" => PredvajalnikDB::get($_GET["id"])];
        ViewHelper::render("view/book-detail.php", $variables);
    }

    public static function showAddForm($variables = array(
        "author" => "", "title" => "",
        "price" => "", "year" => ""
    ))
    {
        ViewHelper::render("view/book-add.php", $variables);
    }

    public static function add()
    {
        $validData = isset($_POST["author"]) && !empty($_POST["author"]) &&
            isset($_POST["title"]) && !empty($_POST["title"]) &&
            isset($_POST["year"]) && !empty($_POST["year"]) &&
            isset($_POST["price"]) && !empty($_POST["price"]);

        if ($validData) {
            PredvajalnikDB::insert($_POST["author"], $_POST["title"], $_POST["price"], $_POST["year"]);
            ViewHelper::redirect(BASE_URL . "book");
        } else {
            self::showAddForm($_POST);
        }
    }

    public static function searchBook()
    {
        if (isset($_GET["query"])) {
            $query = $_GET["query"];
            $hits = PredvajalnikDB::search($query);
        } else {
            $hits = [];
            $query = "";
        }
        $vars = [
            "hits" => $hits,
            "query" => $query
        ];
        ViewHelper::render("view/book-search.php", $vars);
    }

    public static function editBook()
    {
        $edit = isset($_POST["author"]) && !empty($_POST["author"]) &&
            isset($_POST["title"]) && !empty($_POST["title"]) &&
            isset($_POST["price"]) && !empty($_POST["price"]) &&
            isset($_POST["id"]) && !empty($_POST["id"]) &&
            isset($_POST["year"]) && !empty($_POST["year"]);

        $delete = isset($_POST["delete_confirmation"]) &&
            isset($_POST["id"]) && !empty($_POST["id"]);

        // If we send a valid POST request (contains all required data)
        if ($edit) {
            try {
                PredvajalnikDB::update($_POST["id"], $_POST["author"], $_POST["title"], $_POST["price"], $_POST["year"]);
                // Go to the detail page
                header("Location: " . BASE_URL);
            } catch (Exception $e) {
                $errorMessage = "A database error occured: $e";
            }
            // Do we delete the record?
        } else if ($delete) {
            try {
                PredvajalnikDB::delete($_POST["id"]);
                header("Location: index.php");
            } catch (Exception $e) {
                $errorMessage = "A database error occured: $e";
            }
            // Read the contents from the DB and populate the form with it
        } else {
            try {
                // GET id from either GET or POST request
                $book = PredvajalnikDB::get($_REQUEST["id"]);
            } catch (Exception $e) {
                $errorMessage = "A database error occured: $e";
            }
        }

        $context = array();

        if (isset($book)) {
            $context["book"] = $book;
        }

        if (isset($errorMessage)) {
            $context["errorMessage"] = $errorMessage;
        }

        ViewHelper::render("view/book-edit.php", $context);
    }


    public static function deleteBook()
    {
        $delete = isset($_POST["delete_confirmation"]) &&
            isset($_POST["id"]) && !empty($_POST["id"]);
        if ($delete) {
            try {
                PredvajalnikDB::delete($_POST["id"]);
                ViewHelper::redirect(BASE_URL);
                return;
            } catch (Exception $e) {
                $errorMessage = "A database error occured: $e";
                return;
            }
        } else {
            $book = PredvajalnikDB::get($_REQUEST["id"]);
            $context = [
                "book" => $book
            ];
            ViewHelper::render("view/book-edit.php", $context);
        }
    }

    # TODO: Implement controlers for searching, editing and deleting books
}
