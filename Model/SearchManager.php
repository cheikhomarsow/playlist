<?php

namespace Model;


class SearchManager
{
    private $DBManager;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new SearchManager();
        return self::$instance;
    }

    private function __construct()
    {
        $this->DBManager = DBManager::getInstance();
    }

    public function getArticlesLike($q){
        return $this->DBManager->findAllSecure("SELECT * FROM articles WHERE title LIKE :q",
            [':q' => $q . '%']
        );
    }
    public function getQuestionsLike($q){
        return $this->DBManager->findAllSecure("SELECT * FROM questions WHERE sujet LIKE :q",
            [':q' => $q . '%']
        );
    }

    public function checkSearch($data)
    {
        return !empty(trim($data['search'])) && strlen($data['search'])>=2;
    }
}
