<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CommentModel;

class CommentController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CommentModel();
    }

    // GET /api/articles/(:id)/comments
    public function index($articleId = null)
    {
        if (!$articleId) {
            return $this->failValidationError('Article ID is required');
        }

        $comments = $this->model->where('article_id', $articleId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON($comments);
    }

    // POST /api/articles/(:id)/comments
    public function create($articleId = null)
    {
        if (!$articleId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Article ID is required']);
        }

        // Validate basic inputs
        $rules = [
            'content' => 'required|min_length[1]',
            'user_name' => 'required',
            // 'id_token' => 'required' // For stricter validation later
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => $this->validator->getErrors()
            ]);
        }

        $data = $this->request->getJSON(true);

        // Security Note: In a production app, we MUST verify the Google ID Token here
        // to ensure the user info (name, email, avatar) is legitimate.
        // For this iteration, we accept the data sent from the frontend as trusted
        // (assuming frontend verifies it via Google SDK).

        $commentData = [
            'article_id' => $articleId,
            'user_name' => $data['user_name'],
            'user_email' => $data['user_email'] ?? null,
            'user_avatar' => $data['user_avatar'] ?? null,
            'content' => $data['content'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->model->insert($commentData);

        $newComment = $this->model->find($this->model->getInsertID());

        return $this->response->setStatusCode(201)->setJSON($newComment);
    }
}
