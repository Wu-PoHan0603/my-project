<?php

namespace App\Models;

use CodeIgniter\Model;

// 對應送餐員資料表
class DeliveryStaffModel extends Model
{
    // 指定要操作的資料表
    protected $table = 'delivery_staff';

    // 指定資料表主鍵
    protected $primaryKey = 'id';

    // 查詢結果以陣列形式回傳
    protected $returnType = 'array';

    // 允許新增或修改的欄位
    protected $allowedFields = [
        'staff_name',
        'phone',
        'address',
        'is_active',
    ];

    // 新增或修改時自動更新時間欄位
    protected $useTimestamps = true;

    // 指定建立時間欄位
    protected $createdField = 'created_at';

    // 指定修改時間欄位
    protected $updatedField = 'updated_at';

    // 指定資料庫時間格式
    protected $dateFormat = 'datetime';
}