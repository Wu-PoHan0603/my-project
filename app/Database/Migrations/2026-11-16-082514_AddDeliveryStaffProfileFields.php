<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryStaffProfileFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('delivery_staff', [
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'gender' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'age' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'hire_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);

        // 一個登入帳號只能連結一位送餐員
        $this->db->query(
            'ALTER TABLE `delivery_staff`
             ADD UNIQUE KEY `uq_delivery_staff_user_id` (`user_id`)'
        );

        // 帳號刪除時，解除送餐員與帳號的連結
        $this->db->query(
            'ALTER TABLE `delivery_staff`
             ADD CONSTRAINT `fk_delivery_staff_user`
             FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
             ON UPDATE CASCADE
             ON DELETE SET NULL'
        );
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE `delivery_staff`
             DROP FOREIGN KEY `fk_delivery_staff_user`,
             DROP INDEX `uq_delivery_staff_user_id`'
        );

        $this->forge->dropColumn(
            'delivery_staff',
            ['user_id', 'gender', 'age', 'hire_date']
        );
    }
}