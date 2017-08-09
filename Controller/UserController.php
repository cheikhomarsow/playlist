<?php

namespace Controller;

use Model\UserManager;

class UserController extends BaseController
{
    public function profileAction(){
        $userManager = UserManager::getInstance();
        if(!empty($_SESSION['user_id'])){
            $user = $userManager->getUserById($_SESSION['user_id']);
            echo $this->renderView('profile.html.twig',[
                'user' => $user,
            ]);
        }else{

        }
    }
}
