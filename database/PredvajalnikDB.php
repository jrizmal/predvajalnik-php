<?php

require_once "DBInit.php";

class PredvajalnikDB
{
    public static function getUser($email)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT email, name, id, password FROM user WHERE email = :email");
        $statement->bindParam(":email", $email, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }


    public static function getAllPlaylists()
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT title, date, rating FROM playlist");
        $statement->execute();

        return $statement->fetchAll();
    }
    public static function registerUser($email, $name, $password)
    {
        $db = DBInit::getInstance();
        $statement = $db->prepare("INSERT INTO `user` (`email`, `name`, `password`) VALUES (:email, :name, :password)");
        $statement->bindParam(":email", $email, PDO::PARAM_STR);
        $statement->bindParam(":name", $name, PDO::PARAM_STR);
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


    public static function get($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT id, author, title, price, year FROM book 
            WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        $book = $statement->fetch();

        if ($book != null) {
            return $book;
        } else {
            throw new InvalidArgumentException("Error Processing Request: $_GET[id]", 1);
        }
    }

    public static function insert($author, $title, $price, $year)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("INSERT INTO book (author, title, price, year)
            VALUES (:author, :title, :price, :year)");
        $statement->bindParam(":author", $author);
        $statement->bindParam(":title", $title);
        $statement->bindParam(":price", $price);
        $statement->bindParam(":year", $year);
        $statement->execute();
    }

    public static function update($id, $author, $title, $price, $year)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("UPDATE book SET author = :author,
            title = :title, price = :price, year = :year WHERE id = :id");
        $statement->bindParam(":author", $author);
        $statement->bindParam(":title", $title);
        $statement->bindParam(":price", $price);
        $statement->bindParam(":year", $year);
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();
    }

    public static function delete($id)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("DELETE FROM book WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();
    }

    public static function search($query)
    {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT id, author, title, price, year FROM book 
            WHERE author LIKE :query OR title LIKE :query");

        # Alternatively, we could execute: 
        # $statement = $db->prepare("SELECT id, author, title, price FROM book 
        #    WHERE MATCH (author, title) against (:query)");
        # However, we would have to set the table ("book") storage engine to 
        # MyISAM and set a joint full-text index to author and title columns

        $statement->bindValue(":query", '%' . $query . '%');
        $statement->execute();

        return $statement->fetchAll();
    }
}
