<?php

namespace Controller;

use Model\UserManager;

class UserController extends BaseController
{
    public function userAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        if (!empty($_SESSION['user_id'])) {

            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
            $email = $user['email'];
            $admin = false;

            if($email == 'cheikhomar60@gmail.com'){
                $admin = true;
            }

            if(isset($_POST['updateContact'])){
                $res = $userManager->checkContact($_POST);
                if($res['isFormGood']){
                    $userManager->updateContact($res['data']);
                    header('Location: ?action=user');
                }else{
                    $errorsUpdateContact = $res['errors'];
                }
            }
            if(isset($_POST['updatePassword'])){
                $res = $userManager->checkPassword($_POST);
                if($res['isFormGood']){
                    $userManager->updatePassword($res['data']);
                    $successUpdatePassword[] = 'Mot de passe modifié avec success';
                }else{
                    $errorsUpdatePassword = $res['errors'];
                }
            }


            echo $this->renderView('user.html.twig',
                [
                    'user'=>$user,
                    'admin' => $admin,
                    'errorsUpdateContact' => $errorsUpdateContact,
                    'errorsUpdatePassword' => $errorsUpdatePassword,
                    'successUpdatePassword' =>  $successUpdatePassword,
                ]);
        }else{
            echo $this->redirect('reglog');
        }
    }
}
