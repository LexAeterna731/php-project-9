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
            "SELECT
                urls.id AS id,
                urls.name AS name,
                grouped_checks.created_at AS created_at
            FROM urls
            LEFT JOIN (
                SELECT 
                    MAX(url_checks.created_at) AS created_at,
                    url_checks.url_id AS url_id
                FROM url_checks
                GROUP BY url_id
            ) AS grouped_checks ON urls.id = grouped_checks.url_id
            ORDER BY urls.created_at DESC"
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
    
    //get all checks by id
    public function getChecks(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM url_checks WHERE url_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetchAll();
        return $result;
    }

    //add check to db
    public function addCheck(int $id, $date)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO url_checks (url_id, created_at) VALUES (?, ?)"
        );
        $stmt->execute([$id, $date]);
    }
}
