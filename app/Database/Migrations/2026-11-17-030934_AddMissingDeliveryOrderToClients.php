<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingDeliveryOrderToClients extends Migration
{
    public function up()
    {
        $this->forge->addColumn('clients', [
            'delivery_order' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('clients', 'delivery_order');
    }
}
