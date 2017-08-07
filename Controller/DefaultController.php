<?php

namespace Controller;

use Model\ArticleManager;
use Model\UserManager;
use Model\ForumManager;
use Model\SearchManager;

class DefaultController extends BaseController
{
    public function homeAction()
    {
        $userManager = UserManager::getInstance();
        if(!empty($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
        }else{
            $user = false;
        }
        echo $this->renderView('home.html.twig',[
            'user' => $user
        ]);
    }
    public function searchAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        $searchManager = SearchManager::getInstance();
        $articleManager = ArticleManager::getInstance();
        $forumManager = ForumManager::getInstance();
        $user_id = $_SESSION['user_id'];
        $user = $userManager->getUserById($user_id);
        $email = $user['email'];
        $admin = false;
        $autor = $userManager->getUserByEmail("cheikhomar60@gmail.com");
        $commentsNumber = [];


        if($email == 'cheikhomar60@gmail.com'){
            $admin = true;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($searchManager->checkSearch($_POST)) {
                $search = str_replace(' ', '_', trim($_POST['search']));
                $this->redirect('search&q=' . $search);
            }
        }

        if (isset($_GET['q']) && $_GET['q'] !== '') {
            $like = str_replace('_', ' ', $_GET['q']);
            $articles = $searchManager->getArticlesLike($like);
            $questions = $searchManager->getQuestionsLike($like);

            if (!$articles) {
                $messageArticle = 'Aucun article correspondant à votre recherche n\'a été trouvé...';
            }else{
                foreach ($articles as $article){
                    $commentsNumber[$article['id']] = $articleManager->countComments($article['id']);
                }
            }
            if (!$questions) {
                $messageQuestion = 'Aucune question correspondant à votre recherche n\'a été trouvé...';
            }else{
                foreach($questions as $value){
                    $countAnswers[$value['id']] = $forumManager->countAnswers($value['id']);
                }
            }

            echo $this->renderView('search.html.twig',
                [
                    'messageArticle' => $messageArticle,
                    'messageQuestion' => $messageQuestion,
                    'articles' => $articles,
                    'questions' => $questions,
                    'commentsNumber' => $commentsNumber,
                    'admin' => $admin,
                    'autor' => $autor,
                    'user' => $user,
                    'countAnswers' => $countAnswers,


                ]);
        }else{
            $messageArticle = 'Aucun article correspondant à votre recherche n\'a été trouvé...';
            $messageQuestion = 'Aucune question correspondant à votre recherche n\'a été trouvé...';

            echo $this->renderView('search.html.twig',
                [
                    'messageArticle' => $messageArticle,
                    'messageQuestion' => $messageQuestion,
                    'user' => $user,
                    'admin' => $admin,
                ]);
        }

    }


    public function contactAction(){
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        $user_id = $_SESSION['user_id'];
        $user = $userManager->getUserById($user_id);
        $email = $user['email'];
        $admin = false;

        if($email == 'cheikhomar60@gmail.com'){
            $admin = true;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $userManager->checkContactMe($_POST);
            if($res['isFormGood']){
                $to      = 'cheikhomar60@gmail.com';
                $subject = 'COS - Contact';
                $message = 'Nom : ' . $_POST['username'] . "\r\n" . 'Email : ' . $_POST['email']."\r\n" . 'Sujet : ' . $_POST['sujet'] . "\r\n" . 'Message : ' .  "\r\n" . $_POST['message'];
                $headers = 'From: postmaster@cheikhomarsow.ovh' . "\r\n" .
                    'Reply-To: postmaster@cheikhomarsow.ovh' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);
            }
        }
        echo $this->renderView('contact.html.twig',
            [
                'user'=>$user,
                'admin' => $admin,
            ]);
    }

    public function demo_ajaxAction(){
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        $user_id = $_SESSION['user_id'];
        $user = $userManager->getUserById($user_id);
        $email = $user['email'];
        $admin = false;
        $error = '';


        if($email == 'cheikhomar60@gmail.com'){
            $admin = true;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $manager = UserManager::getInstance();
            if ($manager->userCheckDemo($_POST))
            {
                /*$manager->userLogin($_POST['username']);
                $this->redirect('home');*/
            }
            else {
                $error = "Invalid username or password";
            }
        }
        echo $this->renderView('demo_ajax.html.twig', ['error' => $error,
                                                        'user'=>$user,
                                                        'admin' => $admin,
                                                        ]);
    }

    public function pdf_generatorAction()
    {
        $userManager = UserManager::getInstance();
        $html = "OK";

        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $userManager->returnPDFResponseFromHTML($html);
        }
        echo $this->renderView('pdf_generator.html.twig');
    }


}
