{{-- resources/views/admin/gps-reports.blade.php --}}
@extends('layouts.app')

@section('title', 'GPS Reports')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>GPS Tracking Reports</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Employees with GPS Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($users as $user)
                            <a href="#" class="list-group-item list-group-item-action gps-user" data-id="{{ $user->id }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-user"></i> {{ $user->name }}
                                        <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        @empty
                            <p class="text-muted">No employees with GPS tracking enabled</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Route Tracking Map</h5>
                </div>
                <div class="card-body">
                    <div id="dateSelect" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Select Date</label>
                                <input type="date" class="form-control" id="trackingDate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary w-100" id="loadTracking">Load Route</button>
                            </div>
                        </div>
                    </div>
                    <div id="routeMap" style="height: 500px; display: none;"></div>
                    <div id="noDataMessage" class="text-center text-muted" style="display: none;">
                        <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                        <p>Select an employee and date to view GPS route</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let map;
let routePath;
let markers = [];

$(document).ready(function() {
    $('.gps-user').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        var userName = $(this).find('.fa-user').parent().contents().first().text().trim();
        
        $('.gps-user').removeClass('active');
        $(this).addClass('active');
        
        $('#dateSelect').show();
        $('#routeMap').hide();
        $('#noDataMessage').show();
        
        $('#loadTracking').off('click').on('click', function() {
            var date = $('#trackingDate').val();
            if (!date) {
                alert('Please select a date');
                return;
            }
            
            loadGpsData(userId, date, userName);
        });
    });
});

function loadGpsData(userId, date, userName) {
    $.ajax({
        url: '/admin/gps-data/' + userId,
        method: 'GET',
        data: { date: date },
        success: function(response) {
            if (response.route.length === 0) {
                alert('No GPS data found for this date');
                return;
            }
            
            $('#noDataMessage').hide();
            $('#routeMap').show();
            
            if (!map) {
                initMap(response);
            } else {
                updateMap(response);
            }
        },
        error: function() {
            alert('Error loading GPS data');
        }
    });
}

function initMap(data) {
    var center = data.route.length > 0 ? 
        { lat: parseFloat(data.route[0].lat), lng: parseFloat(data.route[0].lng) } : 
        { lat: 21.001517, lng: 75.5778081 };
    
    map = new google.maps.Map(document.getElementById('routeMap'), {
        zoom: 14,
        center: center
    });
    
    updateMap(data);
}

function updateMap(data) {
    // Clear existing markers and path
    if (routePath) {
        routePath.setMap(null);
    }
    markers.forEach(marker => marker.setMap(null));
    markers = [];
    
    // Draw route path
    var routePoints = data.route.map(point => ({
        lat: parseFloat(point.lat),
        lng: parseFloat(point.lng)
    }));
    
    routePath = new google.maps.Polyline({
        path: routePoints,
        geodesic: true,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 2
    });
    
    routePath.setMap(map);
    
    // Add markers for each point
    data.route.forEach((point, index) => {
        var marker = new google.maps.Marker({
            position: { lat: parseFloat(point.lat), lng: parseFloat(point.lng) },
            map: map,
            label: (index + 1).toString(),
            title: 'Time: ' + point.time
        });
        markers.push(marker);
    });
    
    // Add clock in/out markers
    if (data.clock_in.lat) {
        var clockInMarker = new google.maps.Marker({
            position: { lat: parseFloat(data.clock_in.lat), lng: parseFloat(data.clock_in.lng) },
            map: map,
            icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            title: 'Clock In: ' + data.clock_in.time
        });
        markers.push(clockInMarker);
    }
    
    if (data.clock_out.lat) {
        var clockOutMarker = new google.maps.Marker({
            position: { lat: parseFloat(data.clock_out.lat), lng: parseFloat(data.clock_out.lng) },
            map: map,
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            title: 'Clock Out: ' + data.clock_out.time
        });
        markers.push(clockOutMarker);
    }
    
    // Fit bounds to show all points
    var bounds = new google.maps.LatLngBounds();
    routePoints.forEach(point => bounds.extend(point));
    map.fitBounds(bounds);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
@endpush
@endsection