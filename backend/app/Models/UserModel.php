<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id', 'email', 'password', 'name', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * 驗證密碼
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return null;
    }
}
