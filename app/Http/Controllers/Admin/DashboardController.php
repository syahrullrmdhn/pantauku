<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('type') && in_array($request->input('type'), ['app_open', 'browser_access'])) {
            $query->where('type', $request->input('type'));
        }

        $statsQuery = Event::query();
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('occurred_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('occurred_at', '<=', $request->input('date_to'));
        }

        $totalEvents = $statsQuery->count();
        $appOpens = (clone $statsQuery)->where('type', 'app_open')->count();
        $browserAccesses = (clone $statsQuery)->where('type', 'browser_access')->count();
        $suspiciousCount = (clone $statsQuery)->where('is_suspicious', true)->count();

        $events = $query->orderBy('occurred_at', 'desc')->paginate(25)->withQueryString();

        return view('dashboard', compact(
            'events', 'totalEvents', 'appOpens', 'browserAccesses', 'suspiciousCount'
        ));
    }

    public function exportCsv(Request $request)
    {
        $query = Event::query();
        if ($request->filled('date_from')) $query->whereDate('occurred_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('occurred_at', '<=', $request->date_to);
        $events = $query->orderBy('occurred_at', 'desc')->limit(5000)->get();

        $filename = 'pantauku-export-' . date('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function () use ($events) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Waktu', 'Tipe', 'Nilai', 'Mencurigakan', 'ID Perangkat']);
            foreach ($events as $e) {
                fputcsv($file, [
                    $e->occurred_at,
                    $e->type === 'app_open' ? 'Buka Aplikasi' : 'Akses Browser',
                    $e->value,
                    $e->is_suspicious ? 'Ya' : 'Tidak',
                    $e->device_id ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = Event::query();
        if ($request->filled('date_from')) $query->whereDate('occurred_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('occurred_at', '<=', $request->date_to);
        $events = $query->orderBy('occurred_at', 'desc')->limit(500)->get();

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PantauKu Report</title>';
        $html .= '<style>body{font-family:sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px;text-align:left}th{background:#1a73e8;color:#fff}.suspicious{color:red;font-weight:bold}h1{color:#1a73e8}</style></head><body>';
        $html .= '<h1>PantauKu - Laporan Aktivitas</h1>';
        $html .= '<p>Tanggal: ' . date('d M Y H:i') . '</p>';
        $html .= '<table><thead><tr><th>Waktu</th><th>Tipe</th><th>Nilai</th><th>Mencurigakan</th></tr></thead><tbody>';

        foreach ($events as $e) {
            $suspicious = $e->is_suspicious ? '<span class="suspicious">YA</span>' : 'Tidak';
            $type = $e->type === 'app_open' ? 'Buka Aplikasi' : 'Akses Browser';
            $html .= "<tr><td>{$e->occurred_at}</td><td>{$type}</td><td>{$e->value}</td><td>{$suspicious}</td></tr>";
        }

        $html .= '</tbody></table><p style="margin-top:20px"><em>Dibuat oleh PantauKu Monitoring System</em></p></body></html>';

        return response($html)->header('Content-Type', 'text/html');
    }

    public function maps()
    {
        return view('maps');
    }

    public function locations(Request $request)
    {
        $hours = $request->input('hours', 24);
        $locations = Event::where('type', 'location')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('occurred_at', '>=', now()->subHours($hours))
            ->orderBy('occurred_at', 'desc')
            ->limit(500)
            ->get(['latitude', 'longitude', 'device_name', 'device_id', 'occurred_at']);

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }
}
