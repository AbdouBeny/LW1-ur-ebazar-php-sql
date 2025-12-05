<?php
require_once 'control/UserController.php';
require_once 'control/AdminController.php';
require_once 'control/MainController.php';
require_once 'control/AnnonceController.php';
require_once 'view/MainView.php';
require_once 'model/CategoryStorage.php';
require_once 'model/AnnonceStorage.php';



class Router{

    public function main(){
        $action = $_GET['action'] ?? '';


        switch($action){
            case 'home':
                $controller = new MainController(
                    new MainView(),
                    new CategoryStorage(),
                    new AnnonceStorage()
                );
                $controller->home();
                break;

            // inscription et connextion
            case 'registerForm':
                $userController = new UserController();
                $userController->registerForm();
                break;
            case 'register':
                $userController = new UserController();
                $userController->register();
                break;
            case 'loginForm':
                $userController = new UserController();
                $userController->loginForm();
                break;
            case 'login':
                $userController = new UserController();
                $userController->login();
                break;
            case 'logout':
                $userController = new UserController();
                $userController->logout();
                break;
            // gestion des catégories
            case 'categoryList':
                $adminController = new AdminController();
                $adminController->categoryList();
                break;
            case 'addCategoryForm':
                $adminController = new AdminController();
                $adminController->addCategoryForm();
                break;
            case 'addCategory':
                $adminController = new AdminController();
                $adminController->addCategory();
                break;
            // gestion des annonces
            case 'newAnnonce':
                $controller = new AnnonceController();
                $controller->showCreateForm();
                break;

            case 'saveAnnonce':
                $controller = new AnnonceController();
                $controller->createAnnonce();
                break;

            case 'annonce':
                $controller = new AnnonceController();
                $controller->showAnnonce($_GET['id']);
                break;

            case 'category':
                $controller = new AnnonceController();
                $controller->listByCategory($_GET['id']);
                break;
            case 'buyForm':
                $controller = new AnnonceController();
                $controller->showBuyForm($_GET['id'] ?? null);
                break;
            case 'buy':
                $controller = new AnnonceController();
                $controller->buyAnnonce();
                break;
            case 'myAccount':
                $controller = new AnnonceController();
                $controller->myAccount();
                break;
            case 'confirmReception':
                $controller = new AnnonceController();
                $controller->confirmReception();
                break;
            case 'deleteAnnonce':
                $controller = new AnnonceController();
                $controller->deleteAnnonce($_GET['id'] ?? null);
                break;
            // admin 
            case 'deleteUser':
                $admin = new AdminController();
                $admin->deleteUser($_GET['id'] ?? null);
                break;
            case 'deleteAnnonce':
                $admin = new AdminController();
                $admin->deleteAnnonce($_GET['id'] ?? null);
                break;
            case 'renameCategoryForm':
                $admin = new AdminController();
                $admin->renameCategoryForm($_GET['id'] ?? null);
                break;

            case 'renameCategory':
                $admin = new AdminController();
                $admin->renameCategory();
                break;

            default:
                echo "<h1>Bienvenue sur e-bazar</h1>";
                echo '<a href="?action=registerForm">S\'inscrire</a> | <a href="?action=loginForm">Se connecter</a>';
                break;
        }
    }
}