<?php

namespace App\Models;

use CodeIgniter\Model;

// 對應一位案主的詳細資料表
class ClientProfileModel extends Model
{
    // 指定這個 Model 操作的資料表
    protected $table = 'client_profiles';

    // client_profiles 使用 client_id 作為主鍵
    protected $primaryKey = 'client_id';

    // client_id 沿用 clients.id，不是資料表自動產生的新編號
    protected $useAutoIncrement = false;

    // 查詢結果以陣列回傳，方便用 $profile['region'] 讀取
    protected $returnType = 'array';

    // 列出允許新增或修改的詳細資料欄位
    // 主鍵 client_id 不放在 allowedFields 裡
    protected $allowedFields = [
        'region',
        'national_id',
        'sex',
        'birthday',
        'mobile',
        'home_phone',
        'contact_person_1',
        'contact_person_2',
        'case_manager',
        'care_specialist',
        'home_service',
        'registered_address',
        'longitude',
        'latitude',
        'checkin_distance',
        'meal_box_price',
        'remittance_suffix',
        'only_ot_case',
        'living_status',
        'identity_type',
        'disability_status',
        'health_status',
        'disability_level',
        'disease_names',
        'care_level',
        'assessment_summary',
        'treatment_plan_summary',
        'is_active',
    ];

    // 新增或修改資料時，自動填入 created_at 和 updated_at
    protected $useTimestamps = true;

    // 指定建立與更新時間欄位名稱
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}