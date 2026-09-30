<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'username'          => 'admin_reign',
                'full_name'         => 'Adrien Russel Tan',
                'role'              => 'Admin',
                'password_hash'     => password_hash('Admin123!', PASSWORD_DEFAULT),
                'attendance_status' => 'Clocked In',
                'is_verified'       => true,
                'created_at'        => $now,
            ],
            [
                'username'          => 'mgr_echo',
                'full_name'         => 'Jericho Macarang',
                'role'              => 'Store Manager',
                'password_hash'     => password_hash('Manager123!', PASSWORD_DEFAULT),
                'attendance_status' => 'Clocked Out',
                'is_verified'       => true,
                'created_at'        => $now,
            ],
            [
                'username'          => 'cashier_aaa',
                'full_name'         => 'aaa',
                'role'              => 'Cashier',
                'password_hash'     => password_hash('Cashier123!', PASSWORD_DEFAULT),
                'attendance_status' => 'PTO',
                'is_verified'       => true,
                'created_at'        => $now,
            ],
            [
                'username'          => 'cashier_bbb',
                'full_name'         => 'bbb',
                'role'              => 'Cashier',
                'password_hash'     => password_hash('Cashier123!', PASSWORD_DEFAULT),
                'attendance_status' => 'Clocked Out',
                'is_verified'       => false,
                'created_at'        => $now,
            ],
            [
                'username'          => 'inv_ccc',
                'full_name'         => 'ccc',
                'role'              => 'Inventory',
                'password_hash'     => password_hash('Inventory123!', PASSWORD_DEFAULT),
                'attendance_status' => 'AWOL',
                'is_verified'       => true,
                'created_at'        => $now,
            ],
        ];

        $this->db->table('users')->insertBatch($users);
    }
}
