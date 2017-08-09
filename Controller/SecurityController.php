<?php


namespace Controller;

use Model\UserManager;

class SecurityController extends BaseController
{

    public function logoutAction()
    {

        $userManager = UserManager::getInstance();
        //on supprime l'entrée en bdd au niveau de auth_tokens
        $userManager->deleteTokens($_SESSION['user_id']);

        //supprimer les cookies et detruire la session
        setcookie('auth','',time()-3600);

        session_destroy();
        echo $this->redirect('home');
    }


    public function adminAction(){
        $userManager = UserManager::getInstance();
        if(!empty($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
            if($user['isAdmin'] == 1){
                $admin = true;
                $audios = $userManager->audios();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $res = $userManager->checkAudio($_POST);
                    if($res['isFormGood']){
                        $userManager->addAudio($res['data']);
                    }else{
                        $errors = $res['errors'];
                    }
                }

                echo $this->renderView('admin.html.twig',[
                    'user' => $user,
                    'admin' => $admin,
                    'errors' => $errors,
                    'audios' => $audios,
                ]);
            }else{
                echo $this->redirect('home');
            }
        }else{
            echo $this->redirect('home');
        }

    }

    public function registerAction(){
        $userManager = UserManager::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $userManager->checkRegister($_POST);
            if($res['isFormGood']){
                $userManager->userRegister($res['data']);
            }
        }
    }

    public function loginAction(){

        $userManager = UserManager::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $userManager->checkLogin($_POST);
            if($res['isFormGood']){
                $userManager->userLogin($_POST['username']);
            }
        }
    }

}
