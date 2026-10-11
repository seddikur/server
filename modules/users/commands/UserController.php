<?php

declare(strict_types=1);

namespace app\modules\users\commands;

use app\modules\users\models\User;
use Yii;
use yii\console\Controller;

/**
 * Сиды пользователей сайта: php yii user/init (после rbac/init).
 */
class UserController extends Controller
{
    public function actionInit(): void
    {
        echo "Инициализация пользователей...\n";

        $this->createUser([
            'first_name' => 'Менеджер',
            'last_name' => 'Системы',
            'email' => 'manager@example.com',
            'phone' => '+79999999998',
            'password' => 'manager',
            'role' => 'moder',
        ]);

        $this->createUser([
            'first_name' => 'Тестовый',
            'last_name' => 'Пользователь',
            'email' => 'user@example.com',
            'phone' => '+79999999997',
            'password' => 'user123',
            'role' => 'user',
        ]);

        echo "Операция завершена.\n";
    }

    /**
     * @param array<string, string> $data
     */
    private function createUser(array $data): void
    {
        if (User::findOne(['phone' => $data['phone']])) {
            echo "Пользователь '{$data['phone']}' уже существует.\n";

            return;
        }

        $user = new User();
        $user->scenario = 'create';
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->password = $data['password'];
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        if (!$user->save()) {
            echo "Ошибка при создании '{$data['phone']}':\n";
            print_r($user->getErrors());

            return;
        }

        echo "Пользователь '{$data['phone']}' создан.\n";

        if (Yii::$app->has('authManager')) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($data['role']);
            if ($role && !$auth->getAssignment($data['role'], $user->id)) {
                $auth->assign($role, $user->id);
                echo "Роль '{$data['role']}' назначена пользователю '{$data['phone']}'.\n";
            }
        }
    }
}
