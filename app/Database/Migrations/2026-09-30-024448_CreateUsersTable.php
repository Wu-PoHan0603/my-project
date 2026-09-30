<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        //建立 users 資料表欄位
        $this->forge->addField([
            //使用者編號，新增資料時自動遞增
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            //登入帳號，不允許重複
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],

            //存放密碼雜湊，不存明文密碼
            // VARCHAR(255) 可容納 password_hash() 產生的字串
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],

            // 記錄使用者資料建立時間
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // 記錄使用者資料更新時間
            'updata_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        //將id設定為主鍵
        $this->forge->addKey('id', true);

        //限制username不可重複
        $this->forge->addUniqueKey('username');

        //執行建立資料表
        $this->forge->createTable('users');
    }

    public function down()
    {
        //回復這次 Migration 時刪除 users 資料表
        $this->forge->dropTable('users');
    }
}
