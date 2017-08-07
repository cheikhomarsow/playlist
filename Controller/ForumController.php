<?php

namespace Controller;

use Model\UserManager;
use Model\ForumManager;

class ForumController extends BaseController
{
    public function forumAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        $forumManager = ForumManager::getInstance();
        $user_id = $_SESSION['user_id'];
        $user = $userManager->getUserById($user_id);
        $email = $user['email'];
        $admin = false;
        $questions = $forumManager->getQuestions();
        $userWhoAskQuestion = [];
        $countAnswers = [];

        if ($email == 'cheikhomar60@gmail.com') {
            $admin = true;
        }

        foreach($questions as $value){
            $userWhoAskQuestion[$value['id']] = $userManager->getUserById($value['user_id'])['username'];
            $countAnswers[$value['id']] = $forumManager->countAnswers($value['id']);
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $forumManager->checkQuestion($_POST);
            if($res['isFormGood']){
                $forumManager->addQuestion($res['data']);
            }
        }

        echo $this->renderView('forum.html.twig', [
            'user' => $user,
            'admin' => $admin,
            'questions' => $questions,
            'userWhoAskQuestion' => $userWhoAskQuestion,
            'countAnswers' => $countAnswers,
        ]);
    }

    public function questions_responsesAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        $forumManager = ForumManager::getInstance();
        $tokenExist = true;
        $admin = false;
        $token = $_GET['token'];
        $question = $forumManager->getQuestionByToken($token);
        $isLog = false;
        $userAnswer = [];



        if(!empty($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
            $email = $user['email'];
            $isLog = true;
            if($email == 'cheikhomar60@gmail.com'){
                $admin = true;
            }
        }

        if(!$question){
            $tokenExist = false;
        }else{
            $autor = $userManager->getUserById($question['user_id'])['username'];
            $answers = $forumManager->getAnswers($question['id']);

            if(!empty($answers)){
                foreach ($answers as $value){
                    $userAnswer[$value['id']] = $userManager->getUserById($value['user_id'])['username'];
                }
            }

        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $forumManager->checkAnswer($_POST);
            if($res['isFormGood']){
                $forumManager->addAnswer($res['data']);
            }
        }



        echo $this->renderView('questions_responses.html.twig',[
            'admin' => $admin,
            'user'  => $user,
            'tokenExist' => $tokenExist,
            'question' => $question,
            'autor' => $autor,
            'isLog' => $isLog,
            'answers' => $answers,
            'userAnswer' => $userAnswer,
        ]);
    }
}