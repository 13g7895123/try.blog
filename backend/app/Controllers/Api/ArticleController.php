<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use App\Models\TagModel;
use CodeIgniter\HTTP\ResponseInterface;

class ArticleController extends BaseController
{
    protected ArticleModel $model;

    public function __construct()
    {
        $this->model = new ArticleModel();
    }

    /**
     * GET /api/articles
     * 取得所有文章列表
     */
    public function index(): ResponseInterface
    {
        $articles = $this->model->getAllArticles();

        // 轉換欄位名稱為 camelCase
        $result = array_map(function ($article) {
            return $this->transformArticle($article);
        }, $articles);

        return $this->response->setJSON($result);
    }

    /**
     * GET /api/articles/summary
     * 取得文章摘要列表
     */
    public function summary(): ResponseInterface
    {
        $summaries = $this->model->getArticleSummaries();
        return $this->response->setJSON($summaries);
    }

    /**
     * GET /api/articles/:id
     * 取得單一文章
     */
    public function show(string $id): ResponseInterface
    {
        $article = $this->model->find($id);

        if (!$article) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => '文章不存在']);
        }

        return $this->response->setJSON($this->transformArticle($article));
    }

    /**
     * POST /api/articles
     * 建立新文章
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (empty($data['title']) || empty($data['content'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => '標題和內容為必填']);
        }

        $article = [
            'id' => $this->generateUUID(),
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'tag_ids' => json_encode($data['tagIds'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->model->insert($article)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => $this->model->errors()]);
        }

        return $this->response
            ->setStatusCode(201)
            ->setJSON($this->transformArticle($article));
    }

    /**
     * PUT /api/articles/:id
     * 更新文章
     */
    public function update(string $id): ResponseInterface
    {
        $existing = $this->model->find($id);

        if (!$existing) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => '文章不存在']);
        }

        $data = $this->request->getJSON(true);

        $updateData = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['title'])) {
            $updateData['title'] = trim($data['title']);
        }
        if (isset($data['content'])) {
            $updateData['content'] = trim($data['content']);
        }
        if (isset($data['tagIds'])) {
            $updateData['tag_ids'] = json_encode($data['tagIds']);
        }

        // Handle SEO fields
        if (isset($data['seoTitle'])) {
            $updateData['seo_title'] = trim($data['seoTitle']);
        }
        if (isset($data['seoDescription'])) {
            $updateData['seo_description'] = trim($data['seoDescription']);
        }
        if (isset($data['seoKeywords'])) {
            $updateData['seo_keywords'] = trim($data['seoKeywords']);
        }

        if (!$this->model->update($id, $updateData)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => $this->model->errors()]);
        }

        $updated = $this->model->find($id);
        return $this->response->setJSON($this->transformArticle($updated));
    }

    /**
     * DELETE /api/articles/:id
     * 刪除文章
     */
    public function delete(string $id): ResponseInterface
    {
        $existing = $this->model->find($id);

        if (!$existing) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => '文章不存在']);
        }

        $this->model->delete($id);

        return $this->response
            ->setStatusCode(200)
            ->setJSON(['message' => '文章已刪除']);
    }

    /**
     * 轉換文章欄位為 camelCase
     */
    private function transformArticle(array $article): array
    {
        return [
            'id' => $article['id'],
            'title' => $article['title'],
            'content' => $article['content'],
            'tagIds' => json_decode($article['tag_ids'] ?? '[]', true) ?: [],
            'seoTitle' => $article['seo_title'] ?? '',
            'seoDescription' => $article['seo_description'] ?? '',
            'seoKeywords' => $article['seo_keywords'] ?? '',
            'createdAt' => $article['created_at'],
            'updatedAt' => $article['updated_at'],
        ];
    }

    /**
     * 生成 UUID
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * GET /api/articles/export
     * 匯出所有文章（含標籤資訊）
     */
    public function exportAll(): ResponseInterface
    {
        $articles = $this->model->getAllArticles();
        $tagModel = new TagModel();
        $allTags = $tagModel->findAll();

        // 建立標籤 ID 到標籤對應
        $tagsById = [];
        foreach ($allTags as $tag) {
            $tagsById[$tag['id']] = $tag;
        }

        // 轉換文章格式並附加標籤資訊
        $exportData = [];
        foreach ($articles as $article) {
            $tagIds = json_decode($article['tag_ids'] ?? '[]', true) ?: [];
            $tags = [];
            foreach ($tagIds as $tagId) {
                if (isset($tagsById[$tagId])) {
                    $tags[] = [
                        'id' => $tagsById[$tagId]['id'],
                        'name' => $tagsById[$tagId]['name'],
                        'slug' => $tagsById[$tagId]['slug'],
                    ];
                }
            }

            $exportData[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'content' => $article['content'],
                'tags' => $tags,
                'seoTitle' => $article['seo_title'] ?? '',
                'seoDescription' => $article['seo_description'] ?? '',
                'seoKeywords' => $article['seo_keywords'] ?? '',
                'createdAt' => $article['created_at'],
                'updatedAt' => $article['updated_at'],
            ];
        }

        return $this->response
            ->setContentType('application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="articles_export_' . date('Y-m-d_H-i-s') . '.json"')
            ->setJSON([
                'exportedAt' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'totalArticles' => count($exportData),
                'totalTags' => count($allTags),
                'tags' => array_values(array_map(function ($tag) {
                    return [
                        'id' => $tag['id'],
                        'name' => $tag['name'],
                        'slug' => $tag['slug'],
                    ];
                }, $allTags)),
                'articles' => $exportData,
            ]);
    }

    /**
     * POST /api/articles/import
     * 匯入文章（從 JSON）
     */
    public function import(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => '無效的 JSON 資料']);
        }

        // 驗證資料格式
        if (!isset($data['articles']) || !is_array($data['articles'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => '資料格式錯誤：缺少 articles 陣列']);
        }

        $tagModel = new TagModel();
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        // 如果有 tags 資料，先匯入標籤
        $tagMapping = []; // 舊 ID -> 新 ID
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                // 檢查標籤是否已存在
                $existingTag = $tagModel->where('slug', $tag['slug'])->first();
                if ($existingTag) {
                    $tagMapping[$tag['id']] = $existingTag['id'];
                } else {
                    // 建立新標籤
                    $newTagId = $this->generateUUID();
                    $tagModel->insert([
                        'id' => $newTagId,
                        'name' => $tag['name'],
                        'slug' => $tag['slug'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $tagMapping[$tag['id']] = $newTagId;
                }
            }
        }

        // 匯入文章
        foreach ($data['articles'] as $index => $article) {
            // 驗證必要欄位
            if (empty($article['title']) || empty($article['content'])) {
                $errors[] = "文章 #{$index}: 標題和內容為必填";
                $skippedCount++;
                continue;
            }

            // 檢查文章是否已存在（依 ID）
            if (!empty($article['id'])) {
                $existing = $this->model->find($article['id']);
                if ($existing) {
                    $errors[] = "文章 '{$article['title']}': ID 已存在，已跳過";
                    $skippedCount++;
                    continue;
                }
            }

            // 處理標籤 ID 對應
            $newTagIds = [];
            if (!empty($article['tags'])) {
                foreach ($article['tags'] as $tag) {
                    if (isset($tagMapping[$tag['id']])) {
                        $newTagIds[] = $tagMapping[$tag['id']];
                    } elseif (isset($tag['id'])) {
                        // 如果沒有映射，嘗試直接使用或建立新標籤
                        $existingTag = $tagModel->where('slug', $tag['slug'] ?? '')->first();
                        if ($existingTag) {
                            $newTagIds[] = $existingTag['id'];
                        } elseif (!empty($tag['name']) && !empty($tag['slug'])) {
                            $newTagId = $this->generateUUID();
                            $tagModel->insert([
                                'id' => $newTagId,
                                'name' => $tag['name'],
                                'slug' => $tag['slug'],
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                            $newTagIds[] = $newTagId;
                        }
                    }
                }
            }

            // 建立文章
            $newArticle = [
                'id' => $article['id'] ?? $this->generateUUID(),
                'title' => trim($article['title']),
                'content' => trim($article['content']),
                'tag_ids' => json_encode($newTagIds),
                'seo_title' => trim($article['seoTitle'] ?? ''),
                'seo_description' => trim($article['seoDescription'] ?? ''),
                'seo_keywords' => trim($article['seoKeywords'] ?? ''),
                'created_at' => $article['createdAt'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $article['updatedAt'] ?? date('Y-m-d H:i:s'),
            ];

            if ($this->model->insert($newArticle)) {
                $importedCount++;
            } else {
                $errors[] = "文章 '{$article['title']}': " . implode(', ', $this->model->errors());
                $skippedCount++;
            }
        }

        return $this->response->setJSON([
            'status' => $importedCount > 0 ? 'success' : 'partial',
            'message' => "匯入完成：成功 {$importedCount} 篇，跳過 {$skippedCount} 篇",
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
        ]);
    }
}
