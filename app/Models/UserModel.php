<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // 這個 Model 操作 users 資料表
    protected $table            = 'users';

    // users 資料表的主鍵欄位
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    // 查詢結果以關聯陣列形式回傳
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // 只允許 Model 寫入這兩個欄位
    protected $allowedFields    = [
        'username',
        'password_hash',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    // 新增時自動寫入 created_at，更新時自動寫入 updated_at
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
