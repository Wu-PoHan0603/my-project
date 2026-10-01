<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDisplayNameAndRoleToUsers extends Migration
{
    public function up()
    {
        // 在 users 資料表新增顯示名稱和角色欄位
        $this->forge->addColumn('users', [
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '',
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                // 新帳號預設為一般工作人員，不給管理員權限
                'default'    => 'staff',
            ],
        ]);

        // 讓既有帳號先用 username 當顯示名稱
        $this->db->query(
            'UPDATE `users` SET `display_name` = `username` WHERE `display_name` = ?',
            ['']
        );
    }

    public function down()
    {
        // 回復這次變更時，移除剛新增的兩個欄位
        $this->forge->dropColumn('users', ['display_name', 'role']);
    }
}