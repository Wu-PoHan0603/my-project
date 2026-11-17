<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryOrderToClients extends Migration
{
    public function up()
    {
        // 新增送餐順序欄位；尚未排序的案主可以先保持 NULL
        $this->forge->addColumn('clients', [
            'delivery_order' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);
    }

    public function down()
    {
        // 回復 Migration 時移除送餐順序欄位
        $this->forge->dropColumn('clients', 'delivery_order');
    }
}