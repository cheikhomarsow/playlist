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
