<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table            = 'comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['article_id', 'user_name', 'user_email', 'user_avatar', 'content', 'created_at'];

    protected $useTimestamps = false; // We manage created_at manually or let DB handle it? Migration has created_at. Let's rely on manual setting or default
    // Actually CI4 can handle it.
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at in migration
}
