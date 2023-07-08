<?php

namespace Hexlet\Code;

class Query
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    //add url to db
    public function addUrl(string $url, $date)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO urls (name, created_at) VALUES (?, ?)"
        );
        $stmt->execute([$url, $date]);
    }

    //get all urls from db
    public function getUrls()
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM urls ORDER BY created_at DESC"
        );
        $result = $stmt->fetchAll();
        return $result;
    }

    //get a single url from db
    public function getUrl(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM urls WHERE id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetchAll();
        return $result;
    }

    //is url in db
    public function isId(int $id)
    {
        $result = $this->getUrl($id) !== [] ? true : false;
        return $result;
    }

    //get id db by name
    public function getId(string $name)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM urls WHERE name = ?"
        );
        $stmt->execute([$name]);
        $data = $stmt->fetchColumn();
        $result = $data !== false ? $data : null;
        return $result;
    }
    
}
