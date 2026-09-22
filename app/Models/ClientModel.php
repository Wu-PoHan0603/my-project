<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'ct_name',
        'ct_address',
        'route_no',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
