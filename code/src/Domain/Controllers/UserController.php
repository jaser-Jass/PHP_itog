<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Auth;
use Geekbrains\Application1\Domain\Models\User;

class UserController extends AbstractController {

    protected array $actionsPermissions = [
        'actionHash' => ['admin', 'some'],
        'actionSave' => ['admin']
    ];

    public function actionIndex(): string {
        $users = User::getAllUsersFromStorage();
        
        $render = new Render();

        if(!$users){
            return $render->renderPage(
                'user-empty.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPage(
                'user-index.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users,
                    'isAdmin' => User::isAdmin($_SESSION['id_user'] ?? null)
                ]);
        }
    }

    public function actionIndexRefresh(){
        $limit = null;
        
        if(isset($_POST['maxId']) && ($_POST['maxId'] > 0)){
            $limit = $_POST['maxId'];
        }

        $users = User::getAllUsersFromStorage($limit);
        $usersData = [];
        
        /*
        $render = new Render();
 
        if(!$users){
            return $render->renderPartial(
                'user-empty.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPartial(
                'user-index.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]);
        }
        */

        if(count($users) > 0) {
            foreach($users as $user){
                $usersData[] = $user->getUserDataAsArray();
            }
        }

        return json_encode($usersData, JSON_UNESCAPED_UNICODE);
    }

    public static function isAdmin(?int $idUser): bool {
        if($idUser > 0) {
            $sql = "SELECT role FROM user_roles WHERE role = 'admin' AND id_user = :id_user";

            $handler = Application::$storage->get()->prepare($sql);
            $handler->execute([
                'id_user' => $idUser
            ]);
            $result = $handler->fetchAll();
            if(count($result) > 0){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function actionSave(): string {
        if(User::validateRequestData()) {
            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();

    if (!$saveResult) {
        throw new \Exception("Не удалось сохранить пользователя.");
    }
            $render = new Render();

            return $render->renderPage(
                'user-created.tpl', 
                [
                    'title' => 'Пользователь создан',
                    'message' => "Создан пользователь " . $user->getUserName() . " " . $user->getUserLastName()
                ]);
        }
        else {
           return $this->renderError("Переданные данные некорректны");
            
        }
    }
        private function renderError(string $message): string {
        // Вывод сообщения об ошибке
        $render = new Render();
        return $render->renderPage('error.tpl', ['error' => $message]);
    }

public function actionDelete(): string {
    if(User::exists($_GET['user_id'])) {
        User::deleteFromStorage($_GET['user_id']);

        header('Location: /user');
        die();
    }
    else{
        throw new \Exception("Пользователь не существует");
    }
}



    public function actionEdit(): string {
        $render = new Render();
        
        return $render->renderPageWithForm(
                'user-form.tpl', 
                [
                    'title' => 'Форма создания пользователя'
                ]);
    }

    public function actionAuth(): string {
        $render = new Render();
        
        return $render->renderPageWithForm(
                'user-auth.tpl', 
                [
                    'title' => 'Форма логина'
                ]);
    }

    public function actionHash(): string {
        return Auth::getPasswordHash($_GET['pass_string']);
    }

        public function actionLogin(): string {
        $result = false;

        if (isset($_POST['login']) && isset($_POST['password'])) {
            $result = Application::$auth->proceedAuth($_POST['login'], $_POST['password']);
        }

        $render = new Render();
        if ($result) {
            return $render->renderPage(
                'user-logged-in.tpl',
                [
                    'title' => 'Добро пожаловать',
                    'message' => "Вы успешно вошли в систему."
                ]
            );
        } else {
            return $render->renderPage(
                'user-auth.tpl',
                [
                    'title' => 'Форма логина',
                    'error' => "Неправильный логин или пароль. Попробуйте еще раз."
                ]
            );
        }
    }
}