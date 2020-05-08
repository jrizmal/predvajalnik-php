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
        $status = session_status();
        if ($status == PHP_SESSION_NONE) {
            //There is no active session
            session_start();
        }
        if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true) {
            ViewHelper::redirect(BASE_URL);
        } else {
            $ctx = [
                "errorMessage" => ""
            ];
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // POST prijava

                // 1. ali so vsa polja polna?
                // 2. Ali je email v pravi obliki - regex
                // 3. Shranimo uporabnika s hashiranim geslom

                if (isset($_POST["email"]) && !empty($_POST["email"])) {
                    if (isset($_POST["geslo1"]) && !empty($_POST["geslo1"])) {
                        if (isset($_POST["geslo2"]) && !empty($_POST["geslo2"])) {
                            if (isset($_POST["ime"]) && !empty($_POST["ime"])) {
                                $geslo1 = $_POST["geslo1"];
                                $geslo2 = $_POST["geslo2"];
                                // preverimo če sta gesli enaki
                                if ($geslo1 == $geslo2) {
                                    $geslo = $geslo1;
                                    if (strlen($geslo) > 8) {
                                    } else {
                                        $ctx["errorMessage"] = "Geslo mora biti dolgo vsaj 8 znakov";
                                    }
                                    $email = $_POST["email"];
                                    $ime = $_POST["ime"];
                                    // preverjanje vhoda
                                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        // email je ok
                                        // preverimo če uporabnik s tem mailom že obstaja
                                        if (!PredvajalnikDB::userExists($email)) {
                                            // ne obstaja, nadaljujemo
                                            $passHashed = password_hash($geslo, PASSWORD_BCRYPT);
                                            $registered = PredvajalnikDB::registerUser($email, $ime, $passHashed);
                                            if ($registered) {
                                                // opravljena registracija
                                                // Uporabnika kar prijavim
                                                $dbUser = PredvajalnikDB::getUser($email);
                                                // ViewHelper::redirect(BASE_URL . "prijava/");
                                                if (password_verify($geslo, $dbUser["password"])) {
                                                    $_SESSION["loggedIn"] = true;
                                                    $_SESSION["user"] = $dbUser;
                                                    ViewHelper::render("./views/domov.php");
                                                    return;
                                                } else {
                                                    $ctx["errorMessage"] = "Napačna kombinacija emaila in gesla.";
                                                }
                                            } else {
                                                // neka napaka
                                                $ctx["errorMessage"] = "Napaka pri registraciji";
                                            }
                                        } else {
                                            // uporabnik s tem mailom že obstaja
                                            $ctx["errorMessage"] = "Uporabnik s tem emailom že obstaja";
                                        }
                                    } else {
                                        $ctx["errorMessage"] = "Napačen email naslov";
                                    }
                                } else {
                                    $ctx["errorMessage"] = "Gesli se ne ujemata";
                                }
                            } else {
                                $ctx["errorMessage"] = "Manjka ime";
                            }
                        } else {
                            $ctx["errorMessage"] = "Manjka ponovljeno geslo";
                        }
                    } else {
                        $ctx["errorMessage"] = "Manjka geslo";
                    }
                } else {
                    $ctx["errorMessage"] = "Manjka email naslov";
                }
                ViewHelper::render("./views/registracija.php", $ctx);
            } else {
                // GET prijava
                ViewHelper::render("./views/registracija.php", $ctx);
            }
        }
    }

    public static function prijava()
    {
        $status = session_status();
        if ($status == PHP_SESSION_NONE) {
            //There is no active session
            session_start();
        }
        if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true) {
            ViewHelper::redirect(BASE_URL);
        } else {
            $ctx = [
                "errorMessage" => ""
            ];
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // POST prijava
                if (isset($_POST["email"]) && !empty($_POST["email"])) {
                    if (isset($_POST["geslo"]) && !empty($_POST["geslo"])) {
                        $email = $_POST["email"];
                        $geslo = $_POST["geslo"];
                        // echo $email . " " . $geslo;
                        $dbUser = PredvajalnikDB::getUser($email);
                        if (password_verify($geslo, $dbUser["password"])) {
                            $_SESSION["loggedIn"] = true;
                            $_SESSION["user"] = $dbUser;
                            ViewHelper::render("./views/domov.php");
                            return;
                        } else {
                            $ctx["errorMessage"] = "Napačna kombinacija emaila in gesla.";
                        }
                    } else {
                        $ctx["errorMessage"] = "Manjka geslo.";
                    }
                } else {
                    $ctx["errorMessage"] = "Manjka email naslov.";
                }
                ViewHelper::render("./views/prijava.php", $ctx);
            } else {
                // GET prijava
                ViewHelper::render("./views/prijava.php", $ctx);
            }
        }
    }
    public static function odjava()
    {
        session_start();
        $_SESSION = array();
        session_destroy();
        ViewHelper::redirect(BASE_URL);
    }

    public static function profil()
    {
        $status = session_status();
        if ($status == PHP_SESSION_NONE) {
            //There is no active session
            session_start();
        }
        if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true) {
            $user = $_SESSION["user"];
            $vars = [
                "user" => $user,
            ];
            ViewHelper::render("./views/profil.php", $vars);
        } else {
            ViewHelper::redirect(BASE_URL);
        }
    }

    public static function lestvica()
    {
        ViewHelper::render("./views/lestvica.php");
    }
    public static function vsi()
    {
        ViewHelper::render("./views/vsi.php");
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
