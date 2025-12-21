<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    /**
     * POST /api/auth/login
     */
    public function login(): ResponseInterface
    {
        $data = $this->request->getJSON(true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Email and password are required']);
        }

        $model = new UserModel();
        $user = $model->verifyPassword($email, $password);

        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid credentials']);
        }

        // Set Session
        $session = session();
        $session->set('user_id', $user['id']);
        $session->set('user_email', $user['email']);
        $session->set('user_name', $user['name']);

        return $this->response->setJSON(['message' => 'Login successful', 'user' => $user]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): ResponseInterface
    {
        $session = session();
        $session->destroy();
        return $this->response->setJSON(['message' => 'Logged out successfully']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(): ResponseInterface
    {
        $session = session();
        if (!$session->has('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthenticated']);
        }

        return $this->response->setJSON([
            'id' => $session->get('user_id'),
            'email' => $session->get('user_email'),
            'name' => $session->get('user_name'),
        ]);
    }
}
