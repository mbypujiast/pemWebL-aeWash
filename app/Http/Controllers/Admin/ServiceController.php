<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('admin.services.index', [
            'services' => Service::withCount('orderItems')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request));

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        // Layanan yang sudah dipakai order tidak boleh dihapus (riwayat harga harus utuh)
        if ($service->orderItems()->exists()) {
            return back()->with('error', "Layanan \"{$service->name}\" sudah dipakai di order. Nonaktifkan saja lewat Edit.");
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'price_per_kg'  => ['required', 'numeric', 'min:0', 'max:10000000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
        ], ValidationMessages::id(), [
            'name' => 'Nama layanan', 'price_per_kg' => 'Harga per kg', 'duration_days' => 'Lama pengerjaan',
        ]);

        // Checkbox: tidak dicentang = tidak terkirim, jadi pakai boolean()
        $data['is_express'] = $request->boolean('is_express');
        $data['is_active']  = $request->boolean('is_active');

        return $data;
    }
}
