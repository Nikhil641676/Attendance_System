{{-- resources/views/admin/locations.blade.php --}}
@extends('layouts.app')

@section('title', 'Location Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Location Management</h2>
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
            <form action="{{ route('admin.locations.store') }}" method="POST">
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

@push('scripts')
<script>
function initMap() {
    // Initialize maps for each location
    @foreach($locations as $location)
        var map{{ $location->id }} = new google.maps.Map(document.getElementById('map_{{ $location->id }}'), {
            center: { lat: {{ $location->latitude }}, lng: {{ $location->longitude }} },
            zoom: 15
        });
        
        new google.maps.Marker({
            position: { lat: {{ $location->latitude }}, lng: {{ $location->longitude }} },
            map: map{{ $location->id }},
            title: '{{ $location->name }}'
        });
        
        // Draw circle for geofence
        new google.maps.Circle({
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            map: map{{ $location->id }},
            center: { lat: {{ $location->latitude }}, lng: {{ $location->longitude }} },
            radius: {{ $location->radius }}
        });
    @endforeach
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
@endpush
@endsection