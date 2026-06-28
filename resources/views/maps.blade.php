@extends('layouts.admin')

@section('title', 'Maps')
@section('page_title', 'Lokasi Perangkat')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Maps</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt mr-1"></i> Peta Lokasi Perangkat
                </h3>
                <div class="card-tools">
                    <select id="hours-filter" class="form-control form-control-sm" style="width: 140px;">
                        <option value="1">1 jam terakhir</option>
                        <option value="6">6 jam terakhir</option>
                        <option value="24" selected>24 jam terakhir</option>
                        <option value="72">3 hari terakhir</option>
                        <option value="168">7 hari terakhir</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 600px; width: 100%;"></div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <span id="location-count">0</span> titik lokasi ditemukan.
                    <span class="ml-3">Klik marker untuk lihat detail perangkat.</span>
                </small>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="col-12">
        <div class="card card-dark card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Riwayat Lokasi Terbaru
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Perangkat</th>
                            <th>Koordinat</th>
                        </tr>
                    </thead>
                    <tbody id="location-table">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#map { z-index: 1; }
.leaflet-popup-content { font-size: 13px; line-height: 1.5; }
.leaflet-popup-content strong { color: #1a73e8; }
</style>
@stop

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Init map centered on Indonesia
var map = L.map('map').setView([-2.5, 118], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

var markers = [];
var markerGroup = L.layerGroup().addTo(map);

// Custom icons per device
var deviceColors = ['#1a73e8', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c'];
var deviceIcons = {};
var colorIndex = 0;

function getDeviceIcon(deviceName) {
    if (!deviceIcons[deviceName]) {
        var color = deviceColors[colorIndex % deviceColors.length];
        colorIndex++;
        deviceIcons[deviceName] = L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:' + color + ';color:white;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-mobile-alt"></i></div>',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -16]
        });
    }
    return deviceIcons[deviceName];
}

function loadLocations(hours) {
    fetch('/api/locations?hours=' + hours)
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            markerGroup.clearLayers();
            document.getElementById('location-count').textContent = resp.data.length;

            var tableBody = document.getElementById('location-table');
            if (resp.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data lokasi</td></tr>';
            } else {
                var rows = '';
                var bounds = [];
                resp.data.forEach(function(loc) {
                    var lat = parseFloat(loc.latitude);
                    var lng = parseFloat(loc.longitude);
                    var name = loc.device_name || loc.device_id || 'Unknown';
                    var time = new Date(loc.occurred_at).toLocaleString('id-ID');

                    bounds.push([lat, lng]);

                    var marker = L.marker([lat, lng], { icon: getDeviceIcon(name) })
                        .bindPopup('<strong>' + name + '</strong><br>'
                            + lat.toFixed(6) + ', ' + lng.toFixed(6) + '<br>'
                            + '<small>' + time + '</small>'
                            + '<br><a href="https://www.google.com/maps?q=' + lat + ',' + lng + '" target="_blank" rel="noopener">Buka di Google Maps</a>')
                        .addTo(markerGroup);

                    rows += '<tr>'
                        + '<td>' + time + '</td>'
                        + '<td><strong>' + name + '</strong></td>'
                        + '<td>' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</td>'
                        + '</tr>';
                });
                tableBody.innerHTML = rows;

                // Fit bounds
                if (bounds.length === 1) {
                    map.setView(bounds[0], 15);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            }
        })
        .catch(function(err) {
            console.error('Failed to load locations:', err);
        });
}

// Load on page ready
document.addEventListener('DOMContentLoaded', function() {
    loadLocations(24);

    // Filter change
    document.getElementById('hours-filter').addEventListener('change', function() {
        loadLocations(parseInt(this.value));
    });
});
</script>
@stop
