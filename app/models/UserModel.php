<?php
namespace App\Models;

use Caylof\Mvc\Model;

class UserModel extends Model {

    public function findUser($userId) {
        $user = $this->query->select()
            ->table('user')
            ->where(['id' => $userId])
            ->run()
            ->one();

        return $user;
    }
}
