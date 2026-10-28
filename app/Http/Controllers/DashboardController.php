<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        // ---- Pendapatan: hanya order LUNAS, dihitung berdasarkan tanggal pembayaran (paid_at) ----
        $revenueToday = Order::where('payment_status', 'lunas')
            ->whereBetween('paid_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->sum('total_price');

        $revenueMonth = Order::where('payment_status', 'lunas')
            ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total_price');

        // ---- Ringkasan tambahan ----
        $ordersThisMonth = Order::whereBetween('order_date', [
            $now->copy()->startOfMonth()->toDateString(),
            $now->copy()->endOfMonth()->toDateString(),
        ])->count();

        $unpaidTotal = Order::where('payment_status', 'belum_bayar')->sum('total_price');

        // ---- Jumlah order per status (1 query) ----
        $statusCounts = Order::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // ---- Grafik: jumlah order per minggu (8 minggu terakhir) ----
        [$weeklyLabels, $weeklyOrders] = $this->weeklyOrders(8);

        // ---- 5 customer paling loyal: terbanyak order, lalu terbesar total belanja ----
        $topCustomers = Customer::query()
            ->has('orders')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_price')
            ->withMax('orders as last_order_at', 'order_date')
            ->orderByDesc('orders_count')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'revenueToday'    => $revenueToday,
            'revenueMonth'    => $revenueMonth,
            'ordersThisMonth' => $ordersThisMonth,
            'unpaidTotal'     => $unpaidTotal,
            'statusCounts'    => $statusCounts,
            'statuses'        => Order::STATUSES,
            'weeklyLabels'    => $weeklyLabels,
            'weeklyOrders'    => $weeklyOrders,
            'topCustomers'    => $topCustomers,
        ]);
    }

    /**
     * Jumlah order per minggu (Senin-Minggu) selama $weeks minggu terakhir.
     * Minggu tanpa order tetap ditampilkan dengan nilai 0.
     */
    private function weeklyOrders(int $weeks): array
    {
        $firstWeek = now()->startOfWeek(Carbon::MONDAY)->subWeeks($weeks - 1);

        // 1 query: kelompokkan per minggu ISO. Hasil: [202644 => 5, 202645 => 3, ...]
        $rows = Order::where('order_date', '>=', $firstWeek->toDateString())
            ->selectRaw('YEARWEEK(order_date, 3) AS yw, COUNT(*) AS total')
            ->groupBy('yw')
            ->pluck('total', 'yw');

        $labels = [];
        $data   = [];

        for ($i = 0; $i < $weeks; $i++) {
            $week = $firstWeek->copy()->addWeeks($i);
            $key  = $week->isoWeekYear() * 100 + $week->isoWeek();

            $labels[] = $week->locale('id')->translatedFormat('d M');   // tanggal awal minggu
            $data[]   = (int) ($rows[$key] ?? 0);
        }

        return [$labels, $data];
    }
}
