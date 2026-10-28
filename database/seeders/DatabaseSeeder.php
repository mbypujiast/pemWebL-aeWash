<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        Model::unguard();

        $users     = $this->seedUsers();
        $customers = $this->seedCustomers($users['customer']);
        $services  = $this->seedServices();
        $this->seedOrders($customers, $services, $users['staff']);

        Model::reguard();
    }

    private function seedUsers(): array
    {
        return [
            'admin' => User::create([
                'name' => 'Owner æ-WASH', 'email' => 'admin@aewash.test',
                'password' => 'password', 'role' => 'admin', 'phone' => '081234567001',
            ]),
            'staff' => User::create([
                'name' => 'Lorena', 'email' => 'staff@aewash.test',
                'password' => 'password', 'role' => 'staff', 'phone' => '081234567002',
            ]),
            'customer' => User::create([
                'name' => 'Aksara Juan', 'email' => 'customer@aewash.test',
                'password' => 'password', 'role' => 'customer', 'phone' => '081234567003',
            ]),
        ];
    }

    /** 5 customer. Customer pertama terhubung ke akun login. */
    private function seedCustomers(User $customerUser): array
    {
        $data = [
            ['user_id' => $customerUser->id, 'name' => 'Rebecca',  'phone' => '081234567003', 'address' => 'Jl. Pahlawan No. 12, Sidoarjo'],
            ['user_id' => null, 'name' => 'Deryl',   'phone' => '082111222301', 'address' => 'Perum Griya Asri Blok C5, Sidoarjo'],
            ['user_id' => null, 'name' => 'Hastaraka',    'phone' => '082111222302', 'address' => 'Jl. Raya Waru No. 88, Sidoarjo'],
            ['user_id' => null, 'name' => 'Carmenita',   'phone' => '082111222303', 'address' => 'Kost Mawar, Jl. Kampus No. 3, Surabaya'],
            ['user_id' => null, 'name' => 'Julian',   'phone' => '082111222304', 'address' => 'Jl. Diponegoro No. 45, Sidoarjo'],
        ];

        return array_map(fn ($row) => Customer::create($row), $data);
    }

    /** Master harga layanan (angka dummy, silakan sesuaikan) */
    private function seedServices(): array
    {
        $data = [
            ['name' => 'Cuci Kering',  'price_per_kg' => 6000,  'duration_days' => 2, 'is_express' => false],
            ['name' => 'Cuci Setrika', 'price_per_kg' => 8000,  'duration_days' => 3, 'is_express' => false],
            ['name' => 'Express',      'price_per_kg' => 12000, 'duration_days' => 1, 'is_express' => true],
        ];

        return array_map(fn ($row) => Service::create($row), $data);
    }

    private function seedOrders(array $customers, array $services, User $staff): void
    {

        $orders = [
            [0, 33, 'siap_ambil', true,  [[1, 'Katun', 4.0], [1, 'Jeans', 2.5]]],
            [1, 21, 'siap_ambil', true,  [[0, 'Sprei & Selimut', 5.0]]],
            [2, 14, 'siap_ambil', true,  [[1, 'Kemeja Kerja', 3.5], [1, 'Katun', 2.0]]],
            [3,  9, 'siap_ambil', true,  [[2, 'Seragam Kuliah', 3.0]]],
            [0,  7, 'siap_ambil', true,  [[1, 'Katun', 5.0]]],
            [4,  4, 'siap_ambil', true,  [[0, 'Handuk', 2.0], [1, 'Kaos', 3.0]]],
            [1,  2, 'siap_ambil', false, [[1, 'Katun', 4.5]]],
            [2,  1, 'disetrika',  false, [[1, 'Kemeja Kerja', 3.0], [2, 'Jas', 1.5]]],
            [3,  0, 'dicuci',     false, [[0, 'Jeans', 4.0]]],
            [0,  0, 'diterima',   false, [[2, 'Kaos Olahraga', 2.5]]],
        ];

        $invoiceCounter = []; 
        $stages = array_keys(Order::STATUSES);

        foreach ($orders as [$customerIdx, $daysAgo, $status, $paid, $items]) {
            $date = now()->subDays($daysAgo)->setTime(8, 30);
            $key  = $date->format('Ymd');
            $invoiceCounter[$key] = ($invoiceCounter[$key] ?? 0) + 1;

            $totalWeight = 0;
            $totalPrice  = 0;
            $maxDuration = 1;
            $itemRows    = [];

            foreach ($items as [$serviceIdx, $fabric, $kg]) {
                $service  = $services[$serviceIdx];
                $subtotal = $kg * $service->price_per_kg;

                $totalWeight += $kg;
                $totalPrice  += $subtotal;
                $maxDuration  = max($maxDuration, $service->duration_days);

                $itemRows[] = [
                    'service_id'   => $service->id,
                    'fabric_type'  => $fabric,
                    'weight_kg'    => $kg,
                    'price_per_kg' => $service->price_per_kg,
                    'subtotal'     => $subtotal,
                    'created_at'   => $date,
                    'updated_at'   => $date,
                ];
            }

            $order = Order::create([
                'invoice_number'        => sprintf('INV-%s-%03d', $key, $invoiceCounter[$key]),
                'customer_id'           => $customers[$customerIdx]->id,
                'staff_id'              => $staff->id,
                'status'                => $status,
                'payment_status'        => $paid ? 'lunas' : 'belum_bayar',
                'total_weight'          => $totalWeight,
                'total_price'           => $totalPrice,
                'order_date'            => $date->toDateString(),
                'estimated_finish_date' => $date->copy()->addDays($maxDuration)->toDateString(),
                'paid_at'               => $paid ? $date->copy()->addDays($maxDuration)->setTime(16, 0) : null,
                'created_at'            => $date,
                'updated_at'            => $date,
            ]);

            $order->items()->createMany($itemRows);

            // Riwayat status: dari "diterima" sampai status saat ini
            $currentIndex = array_search($status, $stages);
            for ($i = 0; $i <= $currentIndex; $i++) {
                $order->statusHistories()->create([
                    'changed_by' => $staff->id,
                    'status'     => $stages[$i],
                    'note'       => $i === 0 ? 'Order diterima di kasir' : 'Status diperbarui oleh staff',
                    'created_at' => $date->copy()->addHours($i * 2),
                    'updated_at' => $date->copy()->addHours($i * 2),
                ]);
            }
        }
    }
}
