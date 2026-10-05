<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientProfileAndAttachments extends Migration
{
    public function up()
    {
        // 建立每位案主的詳細資料表
        $this->forge->addField([
            // 對應 clients.id；一位案主最多一筆明細
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],

            // 案主基本資料
            'region' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'national_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'sex' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'birthday' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'home_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],

            // 先以文字欄位保存兩位聯絡人資料
            'contact_person_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'contact_person_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // 目前先記錄人員名稱；之後確認人員帳號設計再改成關聯欄位
            'case_manager' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'care_specialist' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'home_service' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            // 戶籍地址；聯絡地址沿用 clients.ct_address
            'registered_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // 經緯度使用小數，保留地圖定位精度
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],

            // 打卡距離先存數值；實際單位會在表單標籤中明確標示
            'checkin_distance' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
            ],

            // 餐盒單價可包含小數
            'meal_box_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
            ],

            // 尾碼以文字保存，避免前面的 0 被當成數字移除
            'remittance_suffix' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            // 0 代表否，1 代表是
            'only_ot_case' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            // 居住、身分、健康和服務狀況
            'living_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'identity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'disability_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'health_status' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'disability_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'disease_names' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'care_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            // 開案評估和處遇計畫摘要
            'assessment_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'treatment_plan_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // 0 代表未啟用，1 代表啟用
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],

            // 讓 CI4 Model 之後可記錄建立與更新時間
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // client_id 同時作為主鍵，確保一位案主只有一筆詳細資料
        $this->forge->addKey('client_id', true);

        // 永久刪除案主時，一併刪除其詳細資料
        $this->forge->addForeignKey(
            'client_id',
            'clients',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 明細表使用 InnoDB，支援外鍵
        $this->forge->createTable(
            'client_profiles',
            false,
            ['ENGINE' => 'InnoDB']
        );

        // 建立附件資料表；實際檔案不會存在這張資料表裡
        $this->forge->addField([
            // 附件資料自己的流水編號
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // 表示附件屬於哪位案主
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],

            // 附件用途：照片、家系圖、資源生態圖或其他文件
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],

            // 原始檔名供畫面顯示
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            // 伺服器產生的隨機檔名，不直接使用上傳者提供的檔名
            'stored_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            // 檔案 MIME 類型和大小，方便預覽及管理
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_size' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],

            // 記錄上傳者帳號編號；尚未取得時允許空值
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],

            // 附件上傳時間
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 建立附件表主鍵
        $this->forge->addKey('id', true);

        // 加速依案主編號查詢附件
        $this->forge->addKey('client_id');

        // 避免案主被永久刪除時留下孤立附件資料
        // 有附件時，之後要先刪除實體檔案和附件資料，才能永久刪除案主
        $this->forge->addForeignKey('client_id', 'clients', 'id');

        // 附件表也使用 InnoDB
        $this->forge->createTable(
            'client_attachments',
            false,
            ['ENGINE' => 'InnoDB']
        );
    }

    public function down()
    {
        // 先移除附件表，再移除案主明細表
        $this->forge->dropTable('client_attachments', true);
        $this->forge->dropTable('client_profiles', true);
    }
}