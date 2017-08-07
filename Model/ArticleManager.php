<?php

namespace Model;


class ArticleManager
{
    private $DBManager;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new ArticleManager();
        return self::$instance;
    }

    private function __construct()
    {
        $this->DBManager = DBManager::getInstance();
    }

    public function getArticleByToken($token){
        return $this->DBManager->findOneSecure("SELECT * FROM articles WHERE token =:token",
                                            ['token' => $token]
            );
    }
    public function getArticleById($id){
        return $this->DBManager->findOneSecure("SELECT * FROM articles WHERE visible = 1 AND id =:id",
            ['id' => $id]
        );
    }


    public function checkArticle($data)
    {
        $errors = array();
        $res = array();
        $isFormGood = true;


        if(isset($_FILES['file']['name']) && !empty($_FILES)){
            $data['file'] = $_FILES['file']['name'];
            $data['file_tmp_name'] = $_FILES['file']['tmp_name'];
            $res['data'] = $data;
        }

        if (!isset($data['title']) || $data['title'] == '') {
            $errors['title'] = 'Veillez remplir le titre';
            $isFormGood = false;
        }

        if (!isset($data['editor1']) || $data['editor1'] == '') {
            $errors['editor1'] = 'Veillez remplir le message';
            $isFormGood = false;
        }
        if (!isset($data['visible']) || ($data['visible'] !== '0' && $data['visible'] !== '1')) {
            $errors['visible'] = 'Veillez remplir le champs visible ?';
            $isFormGood = false;
        }
        $res['isFormGood'] = $isFormGood;
        $res['errors'] = $errors;
        $res['data'] = $data;
        return $res;
    }

    public function addArticle($data){
        if($data['file'] == ''){
            $pathImage = 'assets/img/default-image.jpg';
        }else{
            $pathImage = 'uploads/cosinus/'.$data['file'];
        }
        $article['user_id'] = $_SESSION['user_id'];
        $article['title'] = $data['title'];
        $article['content'] =  $data['editor1'];
        $article['file'] = $pathImage;
        $article['date'] = $this->DBManager->getDatetimeNow();
        $article['token'] = $this->token();
        $article['visible'] =  (int)$data['visible'];
        $article['countVisites'] =  0;
        $this->DBManager->insert('articles', $article);
        move_uploaded_file($data['file_tmp_name'],$pathImage);
        chmod($pathImage, 0666);
    }
    public function editArticle($data){
        $title = $data['title'];
        $content = $data['editor1'];
        $id = (int)$data['id'];
        $visible = (int)$data['visible'];

        if($data['file'] == ''){
            $file = 'assets/img/default-image.jpg';
        }else{
            $file = 'uploads/cosinus/'.$data['file'];
            move_uploaded_file($data['file_tmp_name'],$file);
            chmod($file, 0666);
        }
        return $this->DBManager->findOneSecure(
            "UPDATE articles SET title = :title, content = :content, file = :file, visible = :visible WHERE id=:id",
            [
                'title' => $title,
                'content' => $content,
                'file' => $file,
                'id' => $id,
                'visible'=> $visible,
            ]);
    }
    public function checkComment($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');

        $errors = [];
        $res = [];
        $isFormGood = true;


        if (!isset($data['content_comment']) || $data['content_comment'] == '') {
            $errors['content_comment'] = 'Ce champs ne doit pas être vide';
            $isFormGood = false;
        }else{
            $str = trim($data['content_comment']);
            if(empty($str)){
                $errors['content_comment'] = 'Ce champs ne doit pas être vide';
                $isFormGood = false;
            }else{
                if(strlen($data['content_comment']) > 1000){
                    $errors['content_comment'] = 'Nombre de caractère non autorisé (max 1000)';
                    $isFormGood = false;
                }
            }
        }
        if (!isset($data['user_id']) || $data['user_id'] == '' || !isset($data['article_id']) || $data['article_id'] == '') {
            $errors['content_comment'] = 'Veillez remplir le commentaire';
            $isFormGood = false;
        }

        if($isFormGood)
        {
            echo(json_encode(array('success'=>true, 'data'=>$data), JSON_UNESCAPED_UNICODE ,http_response_code(200)));
            $res['isFormGood'] = $isFormGood;
            $res['errors'] = $errors;
            $res['data'] = $data;
            //exit(0);
            return $res;

        }else
        {
            echo(json_encode(array('error'=>false, 'error'=>$errors), JSON_UNESCAPED_UNICODE ,http_response_code(400)));
            exit(0);
        }

    }

    public function addComment($data){
        $comment['content'] = $data['content_comment'];
        $comment['article_id'] = $data['article_id'];
        $comment['user_id'] =  $data['user_id'];
        $comment['date'] = $this->DBManager->getDatetimeNow();

        $this->DBManager->insert('comments', $comment);
    }

    public function availableArticle(){
        return $this->DBManager->findAllSecure("SELECT * FROM articles WHERE visible = 1 ORDER BY date DESC");
    }
    public function hiddenArticle(){
        return $this->DBManager->findAllSecure("SELECT * FROM articles WHERE visible = 0 ORDER BY date DESC");
    }
    public function allArticle(){
        return $this->DBManager->findAllSecure("SELECT * FROM articles ORDER BY date DESC");
    }
    public function availableComment($data){
        $article_id = (int)$data;
        return $this->DBManager->findAllSecure("SELECT * FROM comments WHERE article_id =:article_id",
                                                ['article_id' => $article_id]
            );
    }

    public function getAllComments(){
        return $this->DBManager->findAllSecure("SELECT * FROM comments ORDER BY date DESC");
    }

    public function removeComment($id){
        return $this->DBManager->findOneSecure('DELETE FROM comments WHERE id = :id',['id' => $id]);
    }
    public function removeArticle($id){
        $article_id = $id;
        $article  = $this->getArticleById($article_id);
        $url = $article['file'];
        $this->DBManager->findAllSecure('DELETE FROM comments WHERE article_id = :article_id',['article_id' => $article_id]);
        return $this->DBManager->findOneSecure('DELETE FROM articles WHERE id = :id',['id' => $id]);
    }

    public function removeUser($id){
        $user_id = $id;
        $this->DBManager->findAllSecure('DELETE FROM comments WHERE user_id = :user_id',['user_id' => $user_id]);
        $this->DBManager->findAllSecure('DELETE FROM articles WHERE user_id = :user_id',['user_id' => $user_id]);
        return $this->DBManager->findOneSecure('DELETE FROM users WHERE id = :id',['id' => $id]);
    }

    public function countComments($article_id){
        $data = $this->DBManager->findAllSecure('SELECT COUNT(*) FROM comments WHERE article_id =:article_id',['article_id' => $article_id]);
        return $data[0]["COUNT(*)"];
    }

    public function token()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 20; $i++) {
            $randstring .= $characters[mt_rand(0, strlen($characters))];
        }
        return $randstring;
    }

    public function alreadyRead($ip,$token){
        return $this->DBManager->findOneSecure("SELECT * FROM users_IP WHERE ip =:ip AND token =:token",
            [
                'ip' => $ip,
                'token' => $token,
            ]
        );
    }

    public function checkIP($ip, $token){
        $data = $this->alreadyRead($ip, $token);
        if($data !== false){
            $date = date('Y/m/d H:i:s',strtotime($data['date']));
            $currentDate = $this->DBManager->getDatetimeNow();

            $date1 = date_create($date);
            $date2 = date_create($currentDate);
            $diff = date_diff($date1, $date2);
            $interval = (int)$diff->format("%R%a");
            if($interval > 1){
                $this->updateCounter($token);
                $this->DBManager->findOneSecure(
                    "UPDATE users_IP SET date =:currentDate WHERE ip=:ip AND token =:token",
                    [
                        'ip' => $ip,
                        'currentDate' => $currentDate,
                        'token' => $token,
                    ]);
            }else{
                $this->DBManager->findOneSecure(
                    "UPDATE users_IP SET date =:currentDate WHERE ip=:ip AND token =:token",
                    [
                        'ip' => $ip,
                        'currentDate' => $currentDate,
                        'token' => $token,
                    ]);
            }
        }else{
            $this->updateCounter($token);
            $this->addIP($ip,$token);
        }
    }

    public function updateCounter($token){
        $d = $this->getArticleByToken($token);
        $countVisites = (int)$d['countVisites'] + 1;
        return $this->DBManager->findOneSecure(
            "UPDATE articles SET countVisites=:countVisites WHERE token=:token",
            [
                'token' => $token,
                'countVisites' => $countVisites,
            ]);
    }

    public function addIP($ip, $token){
        $IP['ip'] = $ip;
        $IP['token'] = $token;
        $IP['date'] = $this->DBManager->getDatetimeNow();
        $this->DBManager->insert('users_IP', $IP);
    }

    public function get_ip() {
        // IP si internet partagé
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // IP derrière un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Sinon : IP normale
        else {
            return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        }
    }




}
