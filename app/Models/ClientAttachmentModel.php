<?php

namespace App\Models;

use CodeIgniter\Model;

// 對應案主上傳檔案的資料表
class ClientAttachmentModel extends Model
{
    // 指定附件資料表
    protected $table = 'client_attachments';

    // 附件資料表使用自動遞增的 id
    protected $primaryKey = 'id';

    // 查詢結果以陣列回傳
    protected $returnType = 'array';

    // 列出允許新增或修改的附件欄位
    // id 由資料庫自動產生，因此不放進這份清單
    protected $allowedFields = [
        'client_id',
        'category',
        'original_name',
        'stored_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    // 新增或修改附件紀錄時，自動填入時間欄位
    protected $useTimestamps = true;

    // 指定建立與更新時間欄位名稱
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}