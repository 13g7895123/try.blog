<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeoFieldsToArticles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('articles', [
            'seo_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'tag_ids'
            ],
            'seo_description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'seo_title'
            ],
            'seo_keywords' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'seo_description'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('articles', ['seo_title', 'seo_description', 'seo_keywords']);
    }
}
