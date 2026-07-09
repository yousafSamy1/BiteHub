<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Agent Verification - BiteHub</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->
    <!-- core:css -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/core/core.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('backend/assets/fonts/feather-font/css/iconfont.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/demo1/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.png') }}" />
    <style>
        .auth-page { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f9fbfd; }
        .verify-card { width: 100%; max-width: 600px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: 12px; background: white; }
        .verify-header { text-align: center; margin-bottom: 30px; }
        .verify-header h4 { font-weight: 700; color: #1e2125; margin-bottom: 10px; }
        .verify-header p { color: #6c757d; font-size: 0.95rem; }
        .upload-area { border: 2px dashed #ff6b35; border-radius: 8px; padding: 40px; text-align: center; background: #fff5f2; cursor: pointer; transition: 0.2s; position: relative;}
        .upload-area:hover { background: #ffeae3; }
        .upload-area input[type=file] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .btn-brand { background: #ff6b35; color: white; border: none; padding: 12px 24px; font-weight: 600; border-radius: 6px; width: 100%; margin-top: 20px;}
        .btn-brand:hover { background: #e55a2b; color: white;}
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="page-wrapper full-page">
            <div class="page-content d-flex align-items-center justify-content-center auth-page">
                <div class="verify-card">
                    <div class="verify-header">
                        <h4>Welcome to BiteHub, {{ Auth::user()->FullName }}!</h4>
                        <p>We're excited to have you on the delivery team. Before you can start accepting orders, we need a few documents to verify your identity and vehicle.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('agent.verify.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Required Documents</label>
                            <ul class="text-muted mb-3" style="font-size: 0.9rem;">
                                <li>Valid National ID (Front and Back)</li>
                                <li>Driver's License (if applicable)</li>
                                <li>Vehicle Registration (if applicable)</li>
                            </ul>
                            
                            <div class="upload-area">
                                <i data-feather="upload-cloud" style="width: 48px; height: 48px; color: #ff6b35; margin-bottom: 10px;"></i>
                                <h5 class="mb-2">Click or drag files here to upload</h5>
                                <p class="text-muted">Supports JPG, PNG (Max 2MB each)</p>
                                <input type="file" name="attachments[]" multiple accept="image/png, image/jpeg, image/jpg" required id="file-input">
                            </div>
                            <div id="file-list" class="mt-3 text-success fw-bold"></div>
                        </div>

                        <button type="submit" class="btn btn-brand">Submit Verification Images <i data-feather="arrow-right" class="ms-1" style="width: 18px;"></i></button>

                        <div class="text-center mt-3">
                            <a href="{{ route('agent.logout') }}" class="text-danger" style="font-size: 0.9rem; text-decoration: none;">Logout from account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- core:js -->
    <script src="{{ asset('backend/assets/vendors/core/core.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('backend/assets/vendors/feather-icons/feather.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <script>
        feather.replace();

        document.getElementById('file-input').addEventListener('change', function(e) {
            let fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            for(let i=0; i<this.files.length; i++) {
                fileList.innerHTML += `<div>✓ ${this.files[i].name}</div>`;
            }
        });
    </script>
</body>
</html>
