<?php

require_once("./controllers/PredvajalnikController.php");

function startsWith($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

# Define a global constant pointing to the URL of the application
define("BASE_URL", $_SERVER["SCRIPT_NAME"] . "/");
define("BASE_DIR", dirname($_SERVER["SCRIPT_NAME"]) . "/");

# Request path after /index.php/ with leading and trailing slashes removed
$path = isset($_SERVER["PATH_INFO"]) ? trim($_SERVER["PATH_INFO"], "/") : "";

# The mapping of URLs. It is a simple array where:
# - keys represent URLs
# - values represent functions to be called when a client requests that URL
$urls = [
    "" => function () {
        PredvajalnikController::home();
    },
    "auth/registracija" => function () {
        PredvajalnikController::registracija();
    },
    "auth/prijava" => function () {
        PredvajalnikController::prijava();
    },
    "auth/profil" => function () {
        PredvajalnikController::profil();
    },
    "odjava" => function () {
        PredvajalnikController::odjava();
    },
    "profil" => function () {
        PredvajalnikController::profil();
    },
    "vsi" => function () {
        PredvajalnikController::vsi();
    },
    "lestvica" => function () {
        PredvajalnikController::lestvica();
    },
    "priljubljeni" => function () {
        PredvajalnikController::priljubljeni();
    },
    "moji" => function () {
        PredvajalnikController::moji();
    },
    "seznam" => function () {
        PredvajalnikController::seznam();
    },
    "user-profil"=> function () {
        PredvajalnikController::publicProfil();
    },
    "seznam/search"=> function () {
        PredvajalnikController::isciSeznam();
    },
    /* "book" => function () {
        if (isset($_GET["id"])) {
            BookController::get();
        } else {
            BookController::getAll();
        }
    },
    "book/add" => function () {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            BookController::showAddForm();
        } else {
            BookController::add();
        }
    },
    "" => function () {
        ViewHelper::redirect(BASE_URL . "book");
    },
    "book/search" => function(){
        BookController::searchBook();
    },
    "book/edit" => function(){
        BookController::editBook();
    },
    "book/delete" => function(){
        BookController::deleteBook();
    } */

    // TODO: Add router entries for 1) search, 2) book/edit and 3) book/delete
];

# The actual router.
# Tries to invoke the function that is mapped for the given path
try {
    if($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        exit();
    }
    if (isset($urls[$path])) {
        header("Access-Control-Allow-Origin: *");
        # Great, the path is defined in the router
        $urls[$path](); // invokes function that calls the controller
    } else {
        # Fail, the path is not defined. Show an error message.
        echo "No controller for '$path'";
    }
} catch (Exception $e) {
    # Provisional: whenever there is an exception, display some info about it
    # this should be disabled in production
    ViewHelper::error400($e);
} finally {
    exit();
}
