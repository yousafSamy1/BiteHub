@extends('admin.admin_dashboard')

@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
#map { height: 300px; width: 100%; border-radius: 8px; margin-bottom: 10px; border: 1px solid var(--border-color); z-index: 10; }
</style>
<div class="page-content">
    <div class="row profile-body">
        <div class="d-none d-md-block col-md-4 col-xl-4 left-wrapper">
            <div class="card rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img class="wd-100 rounded-circle"
                            src="{{ (!empty($profileData->Image) && file_exists(public_path('upload/admin_images/'.$profileData->Image))) ? url('upload/admin_images/'.$profileData->Image) : url('upload/no_image.jpg') }}"
                            alt="profile">
                        <span class="h5 ms-3 text-white">{{ $profileData->name }}</span>
                    </div>
                    <div class="mt-3">
                        <label class="tx-11 fw-bolder mb-0 text-uppercase">Name:</label>
                        <p class="text-muted">{{ $profileData->name }}</p>
                    </div>
                    <div class="mt-3">
                        <label class="tx-11 fw-bolder mb-0 text-uppercase">Email:</label>
                        <p class="text-muted">{{ $profileData->email }}</p>
                    </div>
                    <div class="mt-3">
                        <label class="tx-11 fw-bolder mb-0 text-uppercase">Role:</label>
                        <p class="text-muted">Caterer</p>
                    </div>
                    @if($profileData->caterer && $profileData->caterer->Location)
                    <div class="mt-3">
                        <label class="tx-11 fw-bolder mb-0 text-uppercase">Location:</label>
                        <p class="text-muted">{{ $profileData->caterer->Location }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xl-8 middle-wrapper">
            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Update Profile</h6>
                        <form class="forms-sample" method="POST" enctype="multipart/form-data" action="{{ route('caterer.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $profileData->name }}" placeholder="Full Name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $profileData->email }}" placeholder="Email" readonly>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location / Address <small class="text-muted">(Search or click on the map)</small></label>
                                <div id="map"></div>
                                <input type="hidden" name="latitude" id="latInput" value="{{ $profileData->caterer ? $profileData->caterer->Latitude : '' }}">
                                <input type="hidden" name="longitude" id="lngInput" value="{{ $profileData->caterer ? $profileData->caterer->Longitude : '' }}">
                                <input type="text" name="location" id="addressInput" class="form-control mt-2"
                                    value="{{ $profileData->caterer ? $profileData->caterer->Location : '' }}"
                                    placeholder="Click on the map or search to set your location"
                                    readonly style="cursor:default;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo</label>
                                <input class="form-control" id="image" name="photo" type="file">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Opening Time</label>
                                    <input type="time" name="opening_time" class="form-control" value="{{ $profileData->caterer ? $profileData->caterer->OpeningTime : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Closing Time</label>
                                    <input type="time" name="closing_time" class="form-control" value="{{ $profileData->caterer ? $profileData->caterer->ClosingTime : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <img id="showImage" class="wd-80 rounded-circle"
                                    src="{{ (!empty($profileData->Image) && file_exists(public_path('upload/admin_images/'.$profileData->Image))) ? url('upload/admin_images/'.$profileData->Image) : url('upload/no_image.jpg') }}"
                                    alt="profile">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        </form>
                    </div>
                </div>
            <div class="row mt-4">
                <div class="card bg-soft-danger" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Danger Zone</h6>
                        <p class="text-muted small mb-3">Permanently delete your caterer account and all associated data. This action cannot be undone.</p>
                        <form method="POST" action="{{ route('caterer.profile.delete') }}" id="deleteCatererForm">
                            @csrf
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteCaterer()">
                                Delete Caterer Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteCaterer() {
    Swal.fire({
        title: 'Are you absolutely sure?',
        text: "This will permanently delete your caterer account and all associated data. This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete my account!',
        background: '#1a1a1b',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteCatererForm').submit();
        }
    });
}
</script>
<script>
    $(document).ready(function() {
        $('#image').change(function(e) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#showImage').attr('src', e.target.result); }
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var existingLat = document.getElementById('latInput').value;
        var existingLng = document.getElementById('lngInput').value;

        var defaultLat = existingLat ? parseFloat(existingLat) : 30.0444;
        var defaultLng = existingLng ? parseFloat(existingLng) : 31.2357;

        var map = L.map('map').setView([defaultLat, defaultLng], existingLat ? 14 : 12);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

        var marker;
        if (existingLat && existingLng) {
            marker = L.marker([defaultLat, defaultLng]).addTo(map);
        }

        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: 'Search for your address...',
        }).on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var center = e.geocode.center;
            map.fitBounds(bbox);
            if (marker) { map.removeLayer(marker); }
            marker = L.marker(center).addTo(map);
            document.getElementById('latInput').value = center.lat;
            document.getElementById('lngInput').value = center.lng;
            document.getElementById('addressInput').value = e.geocode.name;
        }).addTo(map);

        map.on('click', function(e) {
            if (marker) { map.removeLayer(marker); }
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latInput').value = e.latlng.lat;
            document.getElementById('lngInput').value = e.latlng.lng;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}&accept-language=en`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.display_name)
                        document.getElementById('addressInput').value = data.display_name;
                });
        });

        setTimeout(function() { map.invalidateSize(); }, 300);
    });
</script>
@endsection
