<?php

namespace Controller;

use Model\ArticleManager;
use Model\UserManager;

class ArticleController extends BaseController
{
    public function add_articleAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        if (!empty($_SESSION['user_id'])) {
            $articleManager = ArticleManager::getInstance();
            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
            $email = $user['email'];
            if ($email == 'cheikhomar60@gmail.com') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $res = $articleManager->checkArticle($_POST);
                    if ($res['isFormGood']) {
                        $articleManager->addArticle($res['data']);
                        echo $this->renderView('admin.html.twig', ['addArticleSuccess' => 'Article ajouté !']);
                    } else {
                        echo $this->renderView('admin.html.twig', ['addArticleErrors' => $res['errors']]);
                    }
                }
            } else {
                $this->redirect('user');
            }
        } else {
            $this->redirect('user');
        }
    }

    public function edit_articleAction()
    {
        $userManager = UserManager::getInstance();
        $userManager->auto_login();
        if (!empty($_SESSION['user_id'])) {
            $articleManager = ArticleManager::getInstance();
            $user_id = $_SESSION['user_id'];
            $user = $userManager->getUserById($user_id);
            $email = $user['email'];
            $admin = false;
            $tokenExist = true;


            if ($email == 'cheikhomar60@gmail.com') {
                $admin = true;
                $token = $_GET['token'];
                $article = $articleManager->getArticleByToken($token);
                if (!$article) {
                    $tokenExist = false;
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $res = $articleManager->checkArticle($_POST);
                    if ($res['isFormGood']) {
                        $articleManager->editArticle($res['data']);
                        $editArticleSuccess[] = 'Article modifié !';
                        $article = $articleManager->getArticleByToken($token);

                        echo $this->renderView('edit_article.html.twig', [
                            'editArticleSuccess' => $editArticleSuccess,
                            'user' => $user,
                            'admin' => $admin,
                            'tokenExist' => $tokenExist,
                            'article' => $article,
                        ]);
                    }
                    else {
                        echo $this->renderView('edit_article.html.twig', [
                            'editArticleErrors' => $res['errors'],
                            'user' => $user,
                            'admin' => $admin,
                            'tokenExist' => $tokenExist,
                            'article' => $article,
                        ]);
                    }
                }
                else{

                    echo $this->renderView('edit_article.html.twig', [
                        'user' => $user,
                        'admin' => $admin,
                        'tokenExist' => $tokenExist,
                        'article' => $article,
                    ]);
                }

            }
            else {
                $this->redirect('user');
            }

        }
        else{
            $this->redirect('user');
        }

    }
}
?>
