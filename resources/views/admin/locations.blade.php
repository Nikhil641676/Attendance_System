{{-- resources/views/admin/locations.blade.php --}}
@extends('layouts.app')

@section('title', 'Location Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Branch Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                <i class="fas fa-plus"></i> Add Location
            </button>
        </div>
    </div>

    <div class="row">
        @foreach($locations as $location)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $location->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Coordinates:</strong><br>
                            Lat: {{ $location->latitude }}<br>
                            Lng: {{ $location->longitude }}</p>
                            <p><strong>Radius:</strong> {{ $location->radius }} meters</p>
                            <p><strong>Status:</strong> 
                                @if($location->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong><br>{{ $location->address ?? 'N/A' }}</p>
                            <p><strong>Assigned Employees:</strong> {{ $location->employees_count }}</p>
                        </div>
                    </div>
                    <div id="map_{{ $location->id }}" style="height: 200px; margin-top: 10px;"></div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-warning edit-location" 
                            data-id="{{ $location->id }}"
                            data-name="{{ $location->name }}"
                            data-lat="{{ $location->latitude }}"
                            data-lng="{{ $location->longitude }}"
                            data-radius="{{ $location->radius }}"
                            data-address="{{ $location->address }}"
                            data-active="{{ $location->is_active }}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-location" 
                            data-id="{{ $location->id }}"
                            data-name="{{ $location->name }}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addLocationForm" action="{{ route('admin.locations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Location Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude *</label>
                            <input type="number" step="any" class="form-control" name="latitude" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude *</label>
                            <input type="number" step="any" class="form-control" name="longitude" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Radius (meters) *</label>
                            <input type="number" class="form-control" name="radius" value="100" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div id="addLocationMap" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editLocationForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Location Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude *</label>
                            <input type="number" step="any" class="form-control" id="edit_latitude" name="latitude" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude *</label>
                            <input type="number" step="any" class="form-control" id="edit_longitude" name="longitude" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Radius (meters) *</label>
                            <input type="number" class="form-control" id="edit_radius" name="radius" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div id="editLocationMap" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete location: <strong id="delete_location_name"></strong>?</p>
                <p class="text-danger">This action cannot be undone. All associated employee data will be affected.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteLocationForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Store location data for use after API loads
    window.locationData = @json($locations);
    let editMap = null;
    let editMarker = null;
    let addMap = null;
    let addMarker = null;
    
    // Callback function that will be executed when Google Maps API loads
    window.initGoogleMaps = function() {
        // Initialize maps for each location
        if (window.locationData && window.locationData.length > 0) {
            window.locationData.forEach(function(location) {
                var mapElement = document.getElementById('map_' + location.id);
                if (mapElement) {
                    var map = new google.maps.Map(mapElement, {
                        center: { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) },
                        zoom: 15
                    });
                    
                    new google.maps.Marker({
                        position: { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) },
                        map: map,
                        title: location.name
                    });
                    
                    // Draw circle for geofence
                    new google.maps.Circle({
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FF0000',
                        fillOpacity: 0.35,
                        map: map,
                        center: { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) },
                        radius: parseFloat(location.radius)
                    });
                }
            });
        }
        
        // Initialize the add location map
        var addLocationMapElement = document.getElementById('addLocationMap');
        if (addLocationMapElement) {
            var defaultCenter = { lat: 40.7128, lng: -74.0060 };
            addMap = new google.maps.Map(addLocationMapElement, {
                center: defaultCenter,
                zoom: 12
            });
            
            // Add click listener to set coordinates
            addMap.addListener('click', function(event) {
                var latInput = document.querySelector('#addLocationForm input[name="latitude"]');
                var lngInput = document.querySelector('#addLocationForm input[name="longitude"]');
                if (latInput && lngInput) {
                    latInput.value = event.latLng.lat();
                    lngInput.value = event.latLng.lng();
                    
                    if (addMarker) {
                        addMarker.setMap(null);
                    }
                    addMarker = new google.maps.Marker({
                        position: event.latLng,
                        map: addMap
                    });
                }
            });
        }
        
        // Initialize edit map
        var editLocationMapElement = document.getElementById('editLocationMap');
        if (editLocationMapElement) {
            editMap = new google.maps.Map(editLocationMapElement, {
                center: { lat: 40.7128, lng: -74.0060 },
                zoom: 12
            });
            
            editMap.addListener('click', function(event) {
                document.getElementById('edit_latitude').value = event.latLng.lat();
                document.getElementById('edit_longitude').value = event.latLng.lng();
                
                if (editMarker) {
                    editMarker.setMap(null);
                }
                editMarker = new google.maps.Marker({
                    position: event.latLng,
                    map: editMap
                });
            });
        }
    };
    
    // Handle Edit Location
    document.querySelectorAll('.edit-location').forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            var lat = this.dataset.lat;
            var lng = this.dataset.lng;
            var radius = this.dataset.radius;
            var address = this.dataset.address;
            var isActive = this.dataset.active === '1';
            
            // Set form action URL
            document.getElementById('editLocationForm').action = '/admin/locations/' + id;
            
            // Populate form fields
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_latitude').value = lat;
            document.getElementById('edit_longitude').value = lng;
            document.getElementById('edit_radius').value = radius;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_is_active').checked = isActive;
            
            // Update map
            if (editMap) {
                var position = { lat: parseFloat(lat), lng: parseFloat(lng) };
                editMap.setCenter(position);
                editMap.setZoom(15);
                
                if (editMarker) {
                    editMarker.setMap(null);
                }
                editMarker = new google.maps.Marker({
                    position: position,
                    map: editMap
                });
                
                // Add circle to show geofence radius
                if (editCircle) {
                    editCircle.setMap(null);
                }
                editCircle = new google.maps.Circle({
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: editMap,
                    center: position,
                    radius: parseFloat(radius)
                });
            }
            
            // Show modal
            var editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));
            editModal.show();
        });
    });
    
    // Handle Delete Location
    document.querySelectorAll('.delete-location').forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            
            // Set form action URL
            document.getElementById('deleteLocationForm').action = '/admin/locations/' + id;
            
            // Set location name in modal
            document.getElementById('delete_location_name').textContent = name;
            
            // Show modal
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteLocationModal'));
            deleteModal.show();
        });
    });
    
    // Update radius on edit map when radius input changes
    var editRadiusInput = document.getElementById('edit_radius');
    if (editRadiusInput) {
        editRadiusInput.addEventListener('change', function() {
            if (editMap && editMarker) {
                if (editCircle) {
                    editCircle.setMap(null);
                }
                var position = editMarker.getPosition();
                editCircle = new google.maps.Circle({
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: editMap,
                    center: position,
                    radius: parseFloat(this.value)
                });
            }
        });
    }
    
    // Form validation for add/edit
    document.getElementById('addLocationForm')?.addEventListener('submit', function(e) {
        var lat = parseFloat(this.querySelector('input[name="latitude"]').value);
        var lng = parseFloat(this.querySelector('input[name="longitude"]').value);
        
        if (isNaN(lat) || isNaN(lng)) {
            e.preventDefault();
            alert('Please select a location on the map or enter valid coordinates');
        }
    });
    
    document.getElementById('editLocationForm')?.addEventListener('submit', function(e) {
        var lat = parseFloat(document.getElementById('edit_latitude').value);
        var lng = parseFloat(document.getElementById('edit_longitude').value);
        
        if (isNaN(lat) || isNaN(lng)) {
            e.preventDefault();
            alert('Please select a location on the map or enter valid coordinates');
        }
    });
    
    let editCircle = null;
    
    // Fallback in case the API doesn't load
    window.gm_authFailure = function() {
        console.error('Google Maps API authentication failed');
        document.querySelectorAll('[id^="map_"]').forEach(function(mapElement) {
            mapElement.innerHTML = '<div class="alert alert-danger">Map failed to load. Please check your API key.</div>';
        });
    };
</script>

<!-- Load Google Maps API with async/defer and proper callback -->
<script>
    (function() {
        if (typeof google !== 'undefined' && google.maps) {
            window.initGoogleMaps();
            return;
        }
        
        var script = document.createElement('script');
        var apiKey = 'YOUR_API_KEY_HERE'; // Replace with your actual API key
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMaps&loading=async`;
        script.async = true;
        script.defer = true;
        
        script.onerror = function() {
            console.error('Failed to load Google Maps API');
        };
        
        document.head.appendChild(script);
    })();
</script>
@endpush

@endsection