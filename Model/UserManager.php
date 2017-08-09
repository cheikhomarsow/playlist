<?php

namespace Model;


class UserManager
{
    private $DBManager;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new UserManager();
        return self::$instance;
    }

    private function __construct()
    {
        $this->DBManager = DBManager::getInstance();
    }

    public function getUserById($id)
    {
        $id = (int)$id;
        return $this->DBManager->findOne("SELECT * FROM users WHERE id = " . $id);
    }


    public function getUserByUsername($username)
    {
        return $this->DBManager->findOneSecure("SELECT * FROM users WHERE username = :username",
            ['username' => $username]);
    }

    public function userCheckRegister($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');


        $errors = [];
        $isFormGood = true;

        $username = trim($data['username']);
        if (empty($username)) {
            $errors['username'] = 'Pseudo de 2 caractères minimum';
            $isFormGood = false;
        } else {
            if (strlen($username) < 2) {
                $errors['username'] = 'Pseudo de 2 caractères minimum';
                $isFormGood = false;
            } else {
                $data2 = $this->getUserByUsername($username);
                if ($data2 !== false) {
                    $errors['username'] = 'Nom d\'utilisateur est déjà utilisé';
                    $isFormGood = false;
                }
            }
        }


        if (!isset($data['password']) || !$this->passwordValid($data['password'])) {
            $errors['password'] = "Veiller saisir un mot de passe valide - 6 caractères minimum";
            $isFormGood = false;
        }
        if ($this->passwordValid($data['password']) && $data['password'] !== $data['repeat_password']) {
            $errors['password'] = "Les deux mot de passe ne sont pas identiques";
            $isFormGood = false;
        }

        if($isFormGood)
        {
            json_encode(array('success'=>true, 'user'=>$_POST));
        }
        else
        {
            echo(json_encode(array('success'=>false, 'errors'=>$errors), JSON_UNESCAPED_UNICODE ,http_response_code(400)));
            exit(0);
        }

        $res['isFormGood'] = $isFormGood;
        $res['errors'] = $errors;
        $res['data'] = $data;

        return $res;
    }


    private function emailValid($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }



    //Minimum : 8 caractères avec au moins une lettre majuscule et un nombre
    private function passwordValid($password)
    {
        return preg_match('`^([a-zA-Z0-9]{6,20})$`', $password);
    }


    private function userHash($pass)
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    public function userRegister($data)
    {
        $user['username'] = $data['username'];
        $user['password'] = $this->userHash($data['password']);
        $user['registerDate'] = $this->DBManager->getDatetimeNow();
        $user['isAdmin'] = 0;
        $user['avatar'] = 'assets/img/avatar.png';
        $this->DBManager->insert('users', $user);
    }


    private function usernameValid($username){
        return preg_match('`^([a-zA-Z0-9-_]{6,20})$`', $username);
    }


    public function userLogin($username)
    {
        if($this->usernameValid($username)){
            $data = $this->getUserByUsername($username);
            if ($data === false)
                return false;
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['user_username'] = $data['username'];
            return true;
        }else{
            $data = $this->getUserByUsername($username);
            if ($data === false)
                return false;
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['user_username'] = $data['username'];
            $date = $this->DBManager->take_date();
            $write = $date . ' -- ' . $_SESSION['user_username'] . ' is connected' . "\n";
            $this->DBManager->watch_action_log('access.log', $write);
            return true;
        }

    }

    public function audios(){
        return $this->DBManager->findAllSecure("SELECT * FROM audios");
    }

    public function addAudio($data){
        $audio['artist'] = $data['artist'];
        $audio['title'] = $data['title'];
        $audio['audio_path'] = 'uploads/cosinus/audios/'.$data['audio'];
        $audio['cover_path'] = 'uploads/cosinus/covers/'.$data['cover'];
        $audio['user_id'] = $_SESSION['user_id'];
        $audio['date'] = $this->DBManager->getDatetimeNow();


        if(move_uploaded_file($data['audio_tmp_name'],$audio['audio_path']) &&
            move_uploaded_file($data['cover_tmp_name'],$audio['cover_path'])
        ){
            $this->DBManager->insert('audios', $audio);

            chmod($audio['audio_path'], 0666);
            chmod($audio['cover_path'], 0666);
        }
    }



    public function checkAudio($data){

        $isFormGood = true;
        $errors = '';


        if(isset($_FILES['file']['name']) && count($_FILES['file']['name']) == 2){
            if($_FILES['file']['name'][0] !== ''){
                $data['audio'] = $_FILES['file']['name'][0];
                $data['audio_tmp_name'] = $_FILES['file']['tmp_name'][0];
            }else{
                $errors .= 'Veillez choisir un audio'.'<br>';
                $isFormGood = false;
            }

            if($_FILES['file']['name'][1] !== ''){
                $data['cover'] = $_FILES['file']['name'][1];
                $data['cover_tmp_name'] = $_FILES['file']['tmp_name'][1];
            }else{
                $errors .= 'Veillez choisir un cover'.'<br>';
                $isFormGood = false;
            }

        }else{
            $errors .= 'Veillez choisir un audio et un cover'.'<br>';
            $isFormGood = false;
        }

        if (!isset($data['artist']) || $data['artist'] == '') {
            $errors .= 'Veillez remplir le champs artiste'.'<br>';
            $isFormGood = false;
        }

        if (!isset($data['title']) || $data['title'] == '') {
            $errors  .= 'Veillez remplir le champs titre'.'<br>';
            $isFormGood = false;
        }
        $res['isFormGood'] = $isFormGood;
        $res['errors'] = $errors;
        $res['data'] = $data;
        return $res;
    }
    public function checkRegister($data){
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');

        $isFormGood = true;
        $errors = '';
        $username = trim($data['username']);

        if(empty($username) || empty(trim($data['password']))){
            $isFormGood = false;
            $errors = "Veillez remplir tous les champs";
        }else{
            $data2 = $this->getUserByUsername($username);
            if ($data2 !== false) {
                $errors .= 'Nom d\'utilisateur est déjà utilisé';
                $isFormGood = false;
            }
            if(strlen($username) < 2) {
                $isFormGood = false;
                $errors = "Veillez saisir un nom d'utilisateur valide";
            }
            if (!isset($data['password']) || !$this->passwordValid($data['password'])) {
                $errors .= "Veiller saisir un mot de passe valide - 6 caractères minimum";
                $isFormGood = false;
            }

            if ($this->passwordValid($data['password']) && $data['password'] !== $data['repeat_password']) {
                $errors .= "Les deux mot de passe ne sont pas identiques";
                $isFormGood = false;
            }
        }




        if($isFormGood)
        {
            echo(json_encode(array('success'=>true, 'data'=>$data), JSON_UNESCAPED_UNICODE ,http_response_code(200)));
            $res['isFormGood'] = $isFormGood;
            $res['errors'] = $errors;
            $res['data'] = $data;
            return $res;

        }else
        {
            echo(json_encode(array('error'=>false, 'error'=>$errors), JSON_UNESCAPED_UNICODE ,http_response_code(400)));
            exit(0);
        }
    }
    public function checkLogin($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');

        $isFormGood = true;
        $errors = '';

        if (empty($data['username']) OR empty($data['password'])) {
            $isFormGood = false;
            $errors = 'Veillez remplir tous les champs';
        }else{
            $user = $this->getUserByUsername($data['username']);
            if (!password_verify($data['password'], $user['password'])) {
                $isFormGood = false;
                $errors = 'Nom d\'utilisateur ou mot de passe incorrect';
            }
        }

        if($isFormGood)
        {
            echo(json_encode(array('success'=>true, 'data'=>$data), JSON_UNESCAPED_UNICODE ,http_response_code(200)));
            $res['isFormGood'] = $isFormGood;
            $res['errors'] = $errors;
            $res['data'] = $data;
            return $res;

        }else
        {
            echo(json_encode(array('error'=>false, 'error'=>$errors), JSON_UNESCAPED_UNICODE ,http_response_code(400)));
            exit(0);
        }
    }


}
