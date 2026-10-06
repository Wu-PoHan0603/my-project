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
        'ct_addr',
        'route_no',
    ];

    // 開啟新增與更新時的自動時間紀錄
    protected $useTimestamps = true;
    
    // 啟用軟刪除，刪除時間會寫入 d_date
    protected $useSoftDeletes = true;

    // 資料庫時間欄位使用 DATETIME 格式
    protected $dateFormat = 'datetime';

    // 指定新增、更新、軟刪除各自使用的欄位名稱
    protected $createdField = 'b_date';
    protected $updatedField = 'e_date';
    protected $deletedField = 'd_date';

    //還原已軟刪除的案主
    public function restoreClient(int $id):bool
    {
        //將指定資料的d_date改回NULL
        return (bool) $this->builder()
            ->where($this->primaryKey, $id)
            ->update([$this->deletedField => null,]);
    }
}
