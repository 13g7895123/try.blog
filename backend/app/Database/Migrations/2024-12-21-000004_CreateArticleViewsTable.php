<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArticleViewsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
            ],
            'article_id' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'viewed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('article_id');
        $this->forge->addKey('viewed_at');
        $this->forge->createTable('article_views');
    }

    public function down()
    {
        $this->forge->dropTable('article_views');
    }
}
