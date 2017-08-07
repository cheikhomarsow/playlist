<?php

namespace Model;


class ForumManager
{
    private $DBManager;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new ForumManager();
        return self::$instance;
    }

    private function __construct()
    {
        $this->DBManager = DBManager::getInstance();
    }

    public function getQuestionByToken($token)
    {
        return $this->DBManager->findOneSecure("SELECT * FROM questions WHERE token =:token",
            ['token' => $token]
        );
    }

    public function checkQuestion($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');

        $isFormGood = true;
        $errors = [];
        $sujet = $data['sujet'];
        $question = $data['editor1'];

        if(!isset($sujet) || empty(trim($sujet)) || !isset($question) || empty(trim($question))){
            $isFormGood = false;
            $errors['errors'] = 'Veillez remplir rous les champs';
        }
        if(!empty(trim($question)) && strlen($question) > 10000){
            $isFormGood = false;
            $errors['question'] = 'Vous avez dépassé le nombre de caractère autorisé';
        }



        if($isFormGood)
        {
            echo(json_encode(array('success'=>true, 'data'=>$data), JSON_UNESCAPED_UNICODE ,http_response_code(200)));
            $res['data'] = $data;
            $res['errors'] = $errors;
            $res['isFormGood'] = $isFormGood;
            return $res;

        }else
        {
            echo(json_encode(array('error'=>false, 'error'=>$errors), JSON_UNESCAPED_UNICODE ,http_response_code(400)));
            exit(0);
        }
    }

    public function addQuestion($data)
    {
        $question['sujet'] = $data['sujet'];
        $question['question'] = $data['editor1'];
        $question['user_id'] = $data['user_id'];
        $question['date'] = $this->DBManager->getDatetimeNow();
        $question['token'] = $this->DBManager->token();
        $this->DBManager->insert('questions', $question);

    }

    public function getQuestions()
    {
        return $this->DBManager->findAllSecure("SELECT * FROM questions ORDER BY date DESC");
    }

    public function checkAnswer($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');

        $errors = [];
        $res = [];
        $isFormGood = true;

        $content_answer = $data['editor1'];


        if (!isset($content_answer) || $content_answer == '') {
            $errors['content_comment'] = 'Ce champs ne doit pas être vide';
            $isFormGood = false;
        }else{
            $str = trim($content_answer);
            if(empty($str)){
                $errors['content_answer'] = 'Ce champs ne doit pas être vide';
                $isFormGood = false;
            }else{
                if(strlen($content_answer) > 1000){
                    $errors['content_answer'] = 'Nombre de caractère non autorisé (max 1000)';
                    $isFormGood = false;
                }
            }
        }
        if (!isset($data['user_id']) || $data['user_id'] == '' || !isset($data['question_id']) || $data['question_id'] == '') {
            $errors['content_answer'] = 'Veillez remplir le commentaire';
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

    public function addAnswer($data)
    {
        $answer['content'] = $data['editor1'];
        $answer['question_id'] = $data['question_id'];
        $answer['user_id'] = $data['user_id'];
        $answer['date'] = $this->DBManager->getDatetimeNow();
        $this->DBManager->insert('answers', $answer);
    }

    public function getAnswers($question_id)
    {
        return $this->DBManager->findAllSecure("SELECT * FROM answers WHERE question_id =:question_id",
            ['question_id' => $question_id]
        );
    }

    public function countAnswers($question_id)
    {
        $data = $this->DBManager->findAllSecure('SELECT COUNT(*) FROM answers WHERE question_id =:question_id',['question_id' => $question_id]);
        return $data[0]["COUNT(*)"];
    }

}
