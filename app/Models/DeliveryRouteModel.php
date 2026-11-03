<?php

namespace App\Models;

use CodeIgniter\Model;

// 對應路線指派資料表
class DeliveryRouteModel extends Model
{
    // 指定要操作的資料表
    protected $table = 'delivery_routes';

    // 指定資料表主鍵
    protected $primaryKey = 'id';

    // 查詢結果以陣列形式回傳
    protected $returnType = 'array';

    // 只允許新增或修改路線編號與送餐員編號
    protected $allowedFields = [
        'route_no',
        'delivery_staff_id',
    ];

    // 新增或修改時自動更新時間欄位
    protected $useTimestamps = true;

    // 指定建立時間欄位名稱
    protected $createdField = 'created_at';

    // 指定修改時間欄位名稱
    protected $updatedField = 'updated_at';

    // 指定資料庫時間欄位格式
    protected $dateFormat = 'datetime';
}