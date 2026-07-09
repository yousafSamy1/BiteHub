@extends('admin.admin_dashboard')

@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <div class="page-content">
        <div class="row profile-body">
            <!-- left wrapper start -->
            <div class="d-none d-md-block col-md-4 col-xl-4 left-wrapper">
                <div class="card rounded">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">

                            @php
                                $kImg = 'default_k.png';
                                $un = strtolower($profileData->username);
                                if(str_contains($un, 'mama')) $kImg = 'mama.png';
                                elseif(str_contains($un, 'rania')) $kImg = 'rania.png';
                                elseif(str_contains($un, 'amira')) $kImg = 'hero.png';
                                elseif(str_contains($un, 'fatma')) $kImg = 'upper_egypt.png';
                                elseif(str_contains($un, 'nour')) $kImg = 'mediterranean.png';
                                elseif(str_contains($un, 'heba') || str_contains($un, 'healthy')) $kImg = 'healthy.png';
                                elseif(str_contains($un, 'samira') || str_contains($un, 'seafood') || str_contains($un, 'alex')) $kImg = 'seafood.png';

                                $kRawImg = $profileData->photo ?? $profileData->Image ?? null;
                                $adminProfileImg = (!empty($kRawImg) && !str_contains($kRawImg, 'no_image') && file_exists(public_path('upload/admin_images/'.$kRawImg))) ? asset('upload/admin_images/'.$kRawImg) : asset('upload/website_assets/'.$kImg);
                            @endphp
                            <div>
                                <img class="wd-100 rounded-circle"
                                    src="{{ $adminProfileImg }}"
                                    alt="profile" style="width:70px;height:70px;object-fit:cover;border:2px solid var(--primary)">
                                <span class="h4 ms-3 text-white">{{ $profileData->username }}</span>
                            </div>

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
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Phone:</label>
                            <p class="text-muted">{{ $profileData->phone }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Adderss:</label>
                            <p class="text-muted">{{ $profileData->address }}</p>
                        </div>
                        <div class="mt-3 d-flex social-links">
                            <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
                                <i data-feather="github"></i>
                            </a>
                            <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
                                <i data-feather="twitter"></i>
                            </a>
                            <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
                                <i data-feather="instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- left wrapper end -->
            <!-- middle wrapper start -->
            <div class="col-md-8 col-xl-8 middle-wrapper">
                <div class="row">
                    <div class="card">
                        <div class="card-body">

                            <h6 class="card-title">Update Admin Profile</h6>

                            <form class="forms-sample" method="POST" enctype="multipart/form-data" action="{{route('admin.store')}}" >
                                @csrf
                                <div class="mb-3">
                                    <label for="exampleInputUsername1" class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" id="exampleInputUsername1"
                                        value="{{ $profileData->username }}" placeholder="Username">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" id="name"
                                        placeholder="Name" value="{{ $profileData->name }}">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" id="email"
                                        placeholder="Email" value="{{ $profileData->email }}" readonly>
                                    <small class="text-muted">Email cannot be changed.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" id="phone"
                                        placeholder="Phone" value="{{ $profileData->phone }}">
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" id="address"
                                        placeholder="Address" value="{{ $profileData->address }}">
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Photo</label>
                                    <input class="form-control" id="image" name="photo" type="file" id="formFile">
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label"></label>
                                    <img id="showImage" class="wd-80 rounded-circle"
                                        src="{{ $adminProfileImg }}"
                                        alt="profile" style="width:80px;height:80px;object-fit:cover;border:2px solid var(--primary)">
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Save Changes</button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
            <!-- middle wrapper end -->
            <!-- right wrapper start -->
            <!-- right wrapper end -->
        </div>

    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#image').change(function(e) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#showImage').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            });
        });
    </script>

    
@endsection
