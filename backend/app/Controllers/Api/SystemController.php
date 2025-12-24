<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SystemController extends BaseController
{
    /**
     * 預期的資料表清單
     */
    private array $expectedTables = [
        'articles',
        'tags',
        'users',
        'article_views',
        'comments',
        'settings',
    ];

    /**
     * GET /api/system/status
     * 取得資料庫狀態摘要
     */
    public function status(): ResponseInterface
    {
        $db = \Config\Database::connect();

        try {
            // 取得所有現存資料表
            $existingTables = $db->listTables();

            // 檢查預期資料表狀態
            $tableStatus = [];
            $totalRecords = 0;
            $missingTables = [];
            $existingTablesCount = 0;

            foreach ($this->expectedTables as $tableName) {
                $exists = in_array($tableName, $existingTables);
                $rowCount = 0;

                if ($exists) {
                    $rowCount = $db->table($tableName)->countAllResults();
                    $totalRecords += $rowCount;
                    $existingTablesCount++;
                } else {
                    $missingTables[] = $tableName;
                }

                $tableStatus[$tableName] = [
                    'exists' => $exists,
                    'rowCount' => $rowCount,
                ];
            }

            // 資料庫連線狀態
            $dbConnected = true;
            try {
                $db->query('SELECT 1');
            } catch (\Exception $e) {
                $dbConnected = false;
            }

            return $this->response->setJSON([
                'status' => count($missingTables) === 0 && $dbConnected ? 'healthy' : 'degraded',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => [
                    'connected' => $dbConnected,
                    'name' => $db->getDatabase(),
                    'driver' => $db->getPlatform(),
                ],
                'summary' => [
                    'expectedTables' => count($this->expectedTables),
                    'existingTables' => $existingTablesCount,
                    'missingTables' => count($missingTables),
                    'totalRecords' => $totalRecords,
                ],
                'tables' => $tableStatus,
                'missingTables' => $missingTables,
            ]);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * GET /api/system/tables
     * 取得所有資料表狀態
     */
    public function tables(): ResponseInterface
    {
        $db = \Config\Database::connect();

        try {
            // 取得所有資料表
            $tables = $db->listTables();

            $result = [];
            foreach ($tables as $tableName) {
                // 取得資料表資訊
                $rowCount = $db->table($tableName)->countAllResults();
                $fields = $db->getFieldData($tableName);

                $columns = [];
                foreach ($fields as $field) {
                    $columns[] = [
                        'name' => $field->name,
                        'type' => $field->type,
                        'maxLength' => $field->max_length ?? null,
                        'nullable' => $field->nullable ?? false,
                        'default' => $field->default ?? null,
                        'primaryKey' => $field->primary_key ?? false,
                    ];
                }

                $result[] = [
                    'name' => $tableName,
                    'rowCount' => $rowCount,
                    'columnCount' => count($columns),
                    'columns' => $columns,
                ];
            }

            return $this->response->setJSON([
                'status' => 'ok',
                'database' => $db->getDatabase(),
                'tableCount' => count($tables),
                'tables' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * GET /api/system/health
     * 健康檢查
     */
    public function health(): ResponseInterface
    {
        $db = \Config\Database::connect();

        $dbStatus = 'ok';
        try {
            $db->query('SELECT 1');
        } catch (\Exception $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $dbStatus,
            'php' => PHP_VERSION,
            'ci4' => \CodeIgniter\CodeIgniter::CI_VERSION,
        ]);
    }
}
