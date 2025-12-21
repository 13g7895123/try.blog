<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UploadController extends BaseController
{
    public function index()
    {
        $validationRule = [
            'image' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[image]',
                    'is_image[image]',
                    'mime_in[image,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[image,2048]', // 2MB
                ],
            ],
        ];

        if (! $this->validate($validationRule)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => $this->validator->getErrors()
            ]);
        }

        $img = $this->request->getFile('image');

        if (! $img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'uploads', $newName);

            $url = base_url('uploads/' . $newName);

            return $this->response->setJSON([
                'url' => $url,
                'message' => 'Upload successful'
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'error' => 'Failed to move uploaded file'
        ]);
    }
}
