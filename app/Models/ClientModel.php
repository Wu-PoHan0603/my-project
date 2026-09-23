<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    // 資料表名稱
    protected $table = 'clients';

    // 主鍵欄位
    protected $primaryKey = 'id';
    
    // 可由表單新增或修改的欄位
    protected $allowedFields = [
        'ct_name',
        'ct_address',
        'route_no',
    ];

    // 自動管理建立及更新時間
    protected $useTimestamps = true;
    
    // 啟用軟刪除
    protected $useSoftDeletes = true;

    //還原已軟刪除的案主
    public function restoreClient(int $id):bool
    {
        //將指定資料的deleted_at改回NULL
        return (bool) $this->builder()
            ->where($this->primaryKey, $id)
            ->update([$this->deletedField => null,]);
    }
}
