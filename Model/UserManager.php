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
        $data = $this->DBManager->findOne("SELECT * FROM users WHERE id = " . $id);
        return $data;
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
        //exit(0);
        //return false;
        return $res;

        /*if($isFormGood)
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
        }*/
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
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        return $hash;
    }

    public function userRegister($data)
    {
        $user['username'] = $data['username'];
        $user['password'] = $this->userHash($data['password']);
        $user['registerDate'] = $this->DBManager->getDatetimeNow();
        $this->DBManager->insert('users', $user);
    }


    private function usernameValid($username){
        return preg_match('`^([a-zA-Z0-9-_]{6,20})$`', $username);
    }


    public function userLogin($username)
    {
        if($this->emailValid($username)){
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

    public function checkContact($data){
        $isFormGood = true;
        $errors = [];
        $res = [];

        $email = $data['email'];
        $user = $this->getUserById($_SESSION['user_id']);
        $username = trim($data['username']);


        if(empty($username)){
            $errors['username'] = 'Pseudo de 2 caractères minimum';
            $isFormGood = false;
        }
        else {
            if (strlen($username) < 2) {
                $errors['username'] = 'Pseudo de 2 caractères minimum';
                $isFormGood = false;
            }
        }
        if(!$this->emailValid($email)){
            $errors['email'] = "email non valide";
            $isFormGood = false;
        }else{
            $referee = $this->getUserByEmail($email);
            if($referee == true && $user['id'] != $referee['id']){
                $errors['email'] = "L'adresse email est déjà utilisé";
                $isFormGood = false;
            }
        }
        $res['isFormGood'] = $isFormGood;
        $res['errors'] = $errors;
        $res['data'] = $data;

        return $res;
    }
    public function updateContact($data){
        $username = $data['username'];
        $email = $data['email'];
        $id = $_SESSION['user_id'];
        return $this->DBManager->findOneSecure(
            "UPDATE users SET username = :username, email = :email WHERE id=:id",
            [
                'email' => $email,
                'username' => $username,
                'id' => $id
            ]);
    }
    public function checkPassword($data){
        $isFormGood = true;
        $errors = [];
        $res = [];

        $old = $data['oldPassword'];
        $new = $data['newPassword'];

        $user = $this->getUserById($_SESSION['user_id']);

        if(!password_verify($old, $user['password'])){
            $errors['password'] = "Le mot de passe n'est pas valide";
            $isFormGood = false;
        }else{
            if(!$this->passwordValid($new)){
                $errors['password'] = "Nouveau mot de passe non valide";
                $isFormGood = false;
            }
        }


        $res['isFormGood'] = $isFormGood;
        $res['errors'] = $errors;
        $res['data'] = $data;

        return $res;
    }
    public function updatePassword($data){
        $new = $this->userHash($data['newPassword']);
        $id = $_SESSION['user_id'];
        return $this->DBManager->findOneSecure(
            "UPDATE users SET password = :new WHERE id=:id",
            [
                'new' => $new,
                'id' => $id
            ]);
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

    //prend en paramètre l'id de l'utilisateur
    public function deleteTokens($user_id)
    {
        return $this->DBManager->findOneSecure("DELETE FROM auth_tokens WHERE user_id = :user_id",
            ['user_id' => $user_id]
        );
    }

}
