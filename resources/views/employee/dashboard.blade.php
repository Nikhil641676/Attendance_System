@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12 text-center mb-4">
        <h2>Welcome, {{ Auth::user()->name }}!</h2>
        <p class="text-muted">{{ now()->format('l, F j, Y') }}</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3 text-center">
        <div class="card">
            <div class="card-body">
                @if($canClockIn)
                    <button class="btn btn-success clock-button clock-in" id="clockInBtn">
                        <i class="fas fa-sign-in-alt fa-2x mb-2"></i><br>
                        Clock In
                    </button>
                @elseif($canClockOut)
                    <button class="btn btn-danger clock-button clock-out" id="clockOutBtn">
                        <i class="fas fa-sign-out-alt fa-2x mb-2"></i><br>
                        Clock Out
                    </button>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-check-circle"></i> You have completed your attendance for today
                        @if($todayAttendance && $todayAttendance->total_hours > 0)
                            <br>Total Hours: {{ $todayAttendance->total_hours }} hours
                        @endif
                    </div>
                @endif
                
                @if($todayAttendance && $todayAttendance->clock_in_time)
                    <div class="mt-3">
                        <p>Clock In: <strong>{{ $todayAttendance->clock_in_time }}</strong></p>
                        @if($todayAttendance->clock_out_time)
                            <p>Clock Out: <strong>{{ $todayAttendance->clock_out_time }}</strong></p>
                            <p>Total Hours: <strong>{{ $todayAttendance->total_hours }}</strong></p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Attendance</h5>
            </div>
            <div class="card-body">
                @if($recentAttendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Total Hours</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('d M Y') }}</td>
                                    <td>{{ $attendance->clock_in_time ?? '-' }}</td>
                                    <td>{{ $attendance->clock_out_time ?? '-' }}</td>
                                    <td>{{ $attendance->total_hours ?? '-' }}</td>
                                    <td><span class="status-badge status-{{ $attendance->status }}">{{ ucfirst($attendance->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No attendance records found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Location Status -->
<div class="location-status">
    <div class="card" id="locationStatusCard" style="display: none;">
        <div class="card-body">
            <small><i class="fas fa-map-marker-alt"></i> <span id="locationStatus">Checking location...</span></small>
        </div>
    </div>
</div>

<!-- Clock In Modal -->
<div class="modal fade" id="clockInModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Clock‑in Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchAddressIn" class="form-control" placeholder="Search for an address...">
                </div>
                <div id="clockInMap" style="height: 350px; width: 100%;"></div>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-map-marker-alt"></i> Drag the marker to your exact location or search above.
                    <button type="button" id="currentLocationInBtn" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-location-dot"></i> My Location
                    </button>
                </div>
                <div id="clockInError" class="alert alert-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmClockInBtn">Confirm Clock In</button>
            </div>
        </div>
    </div>
</div>

<!-- Clock Out Modal -->
<div class="modal fade" id="clockOutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Clock‑out Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchAddressOut" class="form-control" placeholder="Search for an address...">
                </div>
                <div id="clockOutMap" style="height: 350px; width: 100%;"></div>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-map-marker-alt"></i> Drag the marker to your final location.
                    <button type="button" id="currentLocationOutBtn" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-location-dot"></i> My Location
                    </button>
                </div>
                <div id="clockOutError" class="alert alert-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmClockOutBtn">Confirm Clock Out</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentPosition = null;
let watchId = null;
let clockInMap, clockOutMap;
let clockInMarker, clockOutMarker;
let selectedClockInLat = null, selectedClockInLng = null;
let selectedClockOutLat = null, selectedClockOutLng = null;
let clockInAutocomplete, clockOutAutocomplete;

// Get current location using browser GPS
function getCurrentLocation() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            currentPosition = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            checkLocationStatus();
        }, function(error) {
            console.error("Error getting location:", error);
            $('#locationStatusCard').hide();
        });
    }
}

// Continuous GPS tracking (if enabled)
function startWatchingLocation() {
    if ("geolocation" in navigator && {{ Auth::user()->gps_tracking_enabled ? 'true' : 'false' }}) {
        watchId = navigator.geolocation.watchPosition(function(position) {
            currentPosition = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            saveGpsLocation(position.coords.latitude, position.coords.longitude, position.coords.accuracy);
        }, function(error) {
            console.error("Error watching location:", error);
        }, { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 });
    }
}

function saveGpsLocation(lat, lng, accuracy) {
    $.ajax({
        url: '{{ route("employee.gps.save") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', latitude: lat, longitude: lng, accuracy: accuracy },
        success: function(response) { console.log('GPS saved'); },
        error: function(xhr) { console.error('GPS save failed'); }
    });
}

function checkLocationStatus() {
    if (!currentPosition) return;
    $.ajax({
        url: '{{ route("employee.location.status") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', latitude: currentPosition.lat, longitude: currentPosition.lng },
        success: function(response) {
            $('#locationStatusCard').show();
            if (response.allowed) {
                $('#locationStatus').html('<i class="fas fa-check-circle text-success"></i> ' + response.message);
                if (response.nearest_location) {
                    // Show distance in kilometers (already converted by backend)
                    let distance = response.nearest_location.distance;
                    let unit = (distance < 1) ? 'm' : 'km';
                    let displayDist = (unit === 'm') ? Math.round(distance * 1000) : distance;
                    $('#locationStatus').append('<br><small>Nearest: ' + response.nearest_location.name + ' (' + displayDist + ' ' + unit + ')</small>');
                }
            } else {
                $('#locationStatus').html('<i class="fas fa-times-circle text-danger"></i> ' + response.message);
                if (response.nearest_location) {
                    let distance = response.nearest_location.distance;
                    let unit = (distance < 1) ? 'm' : 'km';
                    let displayDist = (unit === 'm') ? Math.round(distance * 1000) : distance;
                    $('#locationStatus').append('<br><small>Nearest: ' + response.nearest_location.name + ' (' + displayDist + ' ' + unit + ')</small>');
                }
            }
        }
    });
}

// Helper to center map to current GPS position
function centerMapToCurrent(map, marker) {
    if (!currentPosition) {
        alert('Unable to get your current location. Please enable GPS.');
        return;
    }
    const center = { lat: currentPosition.lat, lng: currentPosition.lng };
    map.setCenter(center);
    marker.setPosition(center);
    // Update selected coordinates
    if (marker === clockInMarker) {
        selectedClockInLat = center.lat;
        selectedClockInLng = center.lng;
    } else if (marker === clockOutMarker) {
        selectedClockOutLat = center.lat;
        selectedClockOutLng = center.lng;
    }
}

// Initialize Clock In Map with draggable marker and search
function initClockInMap() {
    let center = currentPosition ? { lat: currentPosition.lat, lng: currentPosition.lng } : { lat: 21.001517, lng: 75.5778081 };
    clockInMap = new google.maps.Map(document.getElementById('clockInMap'), {
        zoom: 16,
        center: center
    });
    clockInMarker = new google.maps.Marker({
        position: center,
        map: clockInMap,
        draggable: true,
        title: 'Your selected location'
    });
    selectedClockInLat = center.lat;
    selectedClockInLng = center.lng;

    // Update selected position when marker is dragged
    google.maps.event.addListener(clockInMarker, 'dragend', function(ev) {
        selectedClockInLat = ev.latLng.lat();
        selectedClockInLng = ev.latLng.lng();
        clockInMap.setCenter(ev.latLng);
    });

    // Search box
    const input = document.getElementById('searchAddressIn');
    clockInAutocomplete = new google.maps.places.Autocomplete(input);
    clockInAutocomplete.bindTo('bounds', clockInMap);
    clockInAutocomplete.addListener('place_changed', function() {
        const place = clockInAutocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        clockInMap.setCenter(loc);
        clockInMarker.setPosition(loc);
        selectedClockInLat = loc.lat();
        selectedClockInLng = loc.lng();
    });

    // "My Location" button for Clock In map
    $('#currentLocationInBtn').off('click').on('click', function() {
        centerMapToCurrent(clockInMap, clockInMarker);
    });
}

// Initialize Clock Out Map
function initClockOutMap() {
    let center = currentPosition ? { lat: currentPosition.lat, lng: currentPosition.lng } : { lat: 21.001517, lng: 75.5778081 };
    clockOutMap = new google.maps.Map(document.getElementById('clockOutMap'), {
        zoom: 16,
        center: center
    });
    clockOutMarker = new google.maps.Marker({
        position: center,
        map: clockOutMap,
        draggable: true,
        title: 'Your selected location'
    });
    selectedClockOutLat = center.lat;
    selectedClockOutLng = center.lng;

    google.maps.event.addListener(clockOutMarker, 'dragend', function(ev) {
        selectedClockOutLat = ev.latLng.lat();
        selectedClockOutLng = ev.latLng.lng();
        clockOutMap.setCenter(ev.latLng);
    });

    const input = document.getElementById('searchAddressOut');
    clockOutAutocomplete = new google.maps.places.Autocomplete(input);
    clockOutAutocomplete.bindTo('bounds', clockOutMap);
    clockOutAutocomplete.addListener('place_changed', function() {
        const place = clockOutAutocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        clockOutMap.setCenter(loc);
        clockOutMarker.setPosition(loc);
        selectedClockOutLat = loc.lat();
        selectedClockOutLng = loc.lng();
    });

    // "My Location" button for Clock Out map
    $('#currentLocationOutBtn').off('click').on('click', function() {
        centerMapToCurrent(clockOutMap, clockOutMarker);
    });
}

// Clock In button click
$('#clockInBtn').click(function() {
    if (!currentPosition) {
        alert('Please enable location services to clock in.');
        return;
    }
    // Initialize or re-center map to current GPS position
    if (clockInMap) {
        let center = { lat: currentPosition.lat, lng: currentPosition.lng };
        clockInMap.setCenter(center);
        clockInMarker.setPosition(center);
        selectedClockInLat = center.lat;
        selectedClockInLng = center.lng;
    } else {
        initClockInMap();
    }
    $('#clockInModal').modal('show');
});

$('#confirmClockInBtn').click(function() {
    if (!selectedClockInLat || !selectedClockInLng) {
        $('#clockInError').text('Please select a location on the map.').show();
        return;
    }
    $.ajax({
        url: '{{ route("employee.clockin") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            latitude: selectedClockInLat,
            longitude: selectedClockInLng,
        },
        success: function(response) {
            if (response.success) location.reload();
        },
        error: function(xhr) {
            $('#clockInError').text(xhr.responseJSON?.error || 'Clock in failed').show();
        }
    });
});

// Clock Out button click
$('#clockOutBtn').click(function() {
    if (!currentPosition) {
        alert('Please enable location services to clock out.');
        return;
    }
    if (clockOutMap) {
        let center = { lat: currentPosition.lat, lng: currentPosition.lng };
        clockOutMap.setCenter(center);
        clockOutMarker.setPosition(center);
        selectedClockOutLat = center.lat;
        selectedClockOutLng = center.lng;
    } else {
        initClockOutMap();
    }
    $('#clockOutModal').modal('show');
});

$('#confirmClockOutBtn').click(function() {
    if (!selectedClockOutLat || !selectedClockOutLng) {
        $('#clockOutError').text('Please select a location on the map.').show();
        return;
    }
    $.ajax({
        url: '{{ route("employee.clockout") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            latitude: selectedClockOutLat,
            longitude: selectedClockOutLng
        },
        success: function(response) {
            if (response.success) location.reload();
        },
        error: function(xhr) {
            $('#clockOutError').text(xhr.responseJSON?.error || 'Clock out failed').show();
        }
    });
});

// Initialization
$(document).ready(function() {
    getCurrentLocation();
    startWatchingLocation();
    setInterval(getCurrentLocation, 30000);
});
</script>
@endpush