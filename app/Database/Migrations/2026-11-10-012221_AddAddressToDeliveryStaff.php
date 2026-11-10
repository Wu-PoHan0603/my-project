<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAddressToDeliveryStaff extends Migration
{
    // 執行 php spark migrate 時，新增地址欄位
    public function up()
    {
        $this->forge->addColumn('delivery_staff', [
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'phone',
            ],
        ]);
    }

    // 回復 Migration 時，移除地址欄位
    public function down()
    {
        $this->forge->dropColumn('delivery_staff', 'address');
    }
}