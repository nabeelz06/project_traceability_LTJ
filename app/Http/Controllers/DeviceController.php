<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Display list devices
     */
    public function index(Request $request)
    {
        $query = Device::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Search by device ID or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%");
            });
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }

    /**
     * Show form create device
     */
    public function create()
    {
        return view('admin.devices.create');
    }

    /**
     * Store new device
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255|unique:devices,device_id',
            'device_name' => 'required|string|max:255',
            'type' => 'required|in:rfid_reader,rfid_writer,scanner,handheld',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $validated['is_active'] = true;
            
            $device = Device::create($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'device_registered',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.devices.index')
                ->with('success', 'Device ' . $device->device_name . ' berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal mendaftarkan device: ' . $e->getMessage());
        }
    }

    /**
     * Show detail device
     */
    public function show(Device $device)
    {
        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show form edit device
     */
    public function edit(Device $device)
    {
        return view('admin.devices.edit', compact('device'));
    }

    /**
     * Update device
     */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'type' => 'required|in:rfid_reader,rfid_writer,scanner,handheld',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $device->update($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'device_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'device_id' => $device->device_id,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.devices.show', $device)
                ->with('success', 'Device berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui device: ' . $e->getMessage());
        }
    }

    /**
     * Revoke device access
     */
    public function revoke(Device $device)
    {
        DB::beginTransaction();
        try {
            $device->update(['is_active' => false]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'device_revoked',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Akses device ' . $device->device_name . ' telah dicabut.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal revoke device: ' . $e->getMessage());
        }
    }

    /**
     * Delete device
     */
    public function destroy(Device $device)
    {
        DB::beginTransaction();
        try {
            $deviceName = $device->device_name;
            $device->delete();

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'device_deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'device_name' => $deviceName,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.devices.index')
                ->with('success', 'Device berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus device: ' . $e->getMessage());
        }
    }
}