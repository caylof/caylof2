<?php
namespace App\Models;

use \Caylof\Mvc\Model;

class UserModel extends Model {

    protected $table = 'user';

    protected $pk = 'id';

    protected $autoIncrent = 'id';

    protected $fieldsMapping = [
        'id'   => 'id',
        'name' => 'name',
        'pass' => 'pwd'
    ];
}
