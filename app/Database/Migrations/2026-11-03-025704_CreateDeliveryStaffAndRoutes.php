<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// 建立送餐員資料表與路線指派資料表
class CreateDeliveryStaffAndRoutes extends Migration
{
    // migrate 時執行，建立資料表
    public function up()
    {
        // 定義 delivery_staff 的欄位
        $this->forge->addField([
            // 送餐員資料的流水編號
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // 送餐員姓名
            'staff_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],

            // 聯絡電話可以不填
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],

            // 1 代表啟用，0 代表停用
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],

            // 建立資料的時間
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // 最後修改資料的時間
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 將 id 設為 delivery_staff 的主鍵
        $this->forge->addPrimaryKey('id');

        // 建立送餐員資料表
        $this->forge->createTable('delivery_staff');

        // 定義 delivery_routes 的欄位
        $this->forge->addField([
            // 路線指派資料的流水編號
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // 路線編號；目前依照你的程式設定使用整數
            'route_no' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],

            // 送餐員編號；允許暫時不指派送餐員
            'delivery_staff_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],

            // 建立指派資料的時間
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // 最後修改指派資料的時間
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 將 id 設為 delivery_routes 的主鍵
        $this->forge->addPrimaryKey('id');

        // 限制每個路線編號只能有一筆指派資料
        $this->forge->addUniqueKey('route_no', 'uq_delivery_routes_route_no');

        // 加速依送餐員編號查詢路線
        $this->forge->addKey('delivery_staff_id');

        // 建立送餐員外鍵；刪除送餐員時保留路線，但清空指派
        $this->forge->addForeignKey(
            'delivery_staff_id',
            'delivery_staff',
            'id',
            'CASCADE',
            'SET NULL',
            'fk_delivery_routes_staff'
        );

        // 建立路線資料表
        $this->forge->createTable('delivery_routes');

        // 從既有案主資料中，匯入目前已使用的路線編號
        // DISTINCT 會去除重複路線，避免同一路線匯入多次
        $this->db->query(
            'INSERT INTO delivery_routes (route_no, created_at, updated_at)
             SELECT DISTINCT route_no, NOW(), NOW()
             FROM clients
             WHERE route_no IS NOT NULL'
        );
    }

    // rollback 時執行，依照外鍵依賴順序刪除資料表
    public function down()
    {
        // 先刪除有參照送餐員資料的路線資料表
        $this->forge->dropTable('delivery_routes');

        // 再刪除送餐員資料表
        $this->forge->dropTable('delivery_staff');
    }
}