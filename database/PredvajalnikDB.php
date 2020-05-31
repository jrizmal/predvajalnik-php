<?php

require_once "DBInit.php";

class PredvajalnikDB
{
    public static function getUser($email)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT email, name, id, token, password FROM user WHERE email = :email");
        $statement->bindParam(":email", $email, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function getUserByToken($token)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT email, name, id FROM user WHERE token = :token");
        $statement->bindParam(":token", $token, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function getUserById($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT email, name, id FROM user WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }


    public static function getAllPlaylists()
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT p.id, p.title, u.id as user,u.name as user_name, date, rating FROM playlist p INNER JOIN user u ON p.user=u.id");
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function getPlaylist($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT p.id, p.title, u.id as user,u.name as user_name, date, rating FROM playlist p INNER JOIN user u ON p.user=u.id WHERE p.id = :id ");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function SearchPlaylists($query)
    {
        $db = DBInit::getInstance();

        $query = "%$query%";

        $statement = $db->prepare("SELECT p.id, p.title, p.date, p.rating FROM playlist p WHERE p.title LIKE :q ");
        $statement->bindParam(":q", $query, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function getSongs($playlist_id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT id, url FROM song WHERE playlist = :id ");
        $statement->bindParam(":id", $playlist_id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function newPlaylist($title, $userid)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("INSERT INTO `playlist` (`title`, `date`, `user`, `rating`) VALUES (:title, NOW(), :userid, '0')");
        $statement->bindParam(":title", $title, PDO::PARAM_STR);
        $statement->bindParam(":userid", $userid, PDO::PARAM_INT);

        $statement->execute();



        if ($statement) {
            return $db->lastInsertId();
        }

        return $statement;
    }

    public static function addSong($playlist_id, $url)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("INSERT INTO `song` (`url`, `playlist`) VALUES (:url, :playlist)");
        $statement->bindParam(":url", $url, PDO::PARAM_STR);
        $statement->bindParam(":playlist", $playlist_id, PDO::PARAM_INT);
        $statement->execute();

        return $statement;
    }

    public static function getPlaylistChart()
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT p.id, p.title, u.id as user,u.name as user_name, date, rating FROM playlist p INNER JOIN user u ON p.user=u.id ORDER BY rating DESC");
        $statement->execute();

        return $statement->fetchAll();
    }
    public static function registerUser($email, $name, $password)
    {
        //Generate a token.
        $token = openssl_random_pseudo_bytes(32);
        $token = bin2hex($token);

        $db = DBInit::getInstance();
        $statement = $db->prepare("INSERT INTO `user` (`email`, `name`, `password`, `token`) VALUES (:email, :name, :password, :token)");
        $statement->bindParam(":email", $email, PDO::PARAM_STR);
        $statement->bindParam(":name", $name, PDO::PARAM_STR);
        $statement->bindParam(":token", $token, PDO::PARAM_STR);
        $statement->bindParam(":password", $password, PDO::PARAM_STR);
        return $statement->execute();
    }

    public static function userExists($email)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("SELECT COUNT(*) AS num FROM `user` WHERE email = :email");
        $statement->bindParam(":email", $email, PDO::PARAM_STR);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row['num'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteSong($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("DELETE FROM song WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public static function incrementVote($id)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("UPDATE playlist SET rating = rating + 1 WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public static function decrementVote($id)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("UPDATE playlist SET rating = rating - 1 WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public static function addVote($playlist, $user, $action)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("INSERT INTO `user_vote` (`user`, `playlist`, `action`) VALUES (:user, :playlist, :action)");
        $statement->bindParam(":user", $user, PDO::PARAM_INT);
        $statement->bindParam(":playlist", $playlist, PDO::PARAM_INT);
        $statement->bindParam(":action", $action, PDO::PARAM_STR);

        return $statement->execute();
    }

    public static function getPlaylistsByUser($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT p.id, p.title, u.id as user,u.name as user_name, date, rating FROM playlist p INNER JOIN user u ON p.user=u.id WHERE p.user = :id ");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addPlaylistToLibrary($playlist, $user)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("INSERT INTO `user_library` (`user`, `playlist`) VALUES (:user, :playlist)");
        $statement->bindParam(":user", $user, PDO::PARAM_INT);
        $statement->bindParam(":playlist", $playlist, PDO::PARAM_INT);

        return $statement->execute();
    }

    public static function getUserLibrary($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT p.* from playlist p inner join user_library ul on ul.playlist = p.id where ul.user = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
