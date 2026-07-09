@php
    $isAr = isset($_COOKIE['googtrans']) && str_contains($_COOKIE['googtrans'], '/en/ar');
@endphp
<!DOCTYPE html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Responsive HTML Admin Dashboard Template based on Bootstrap 5">
    <meta name="author" content="NobleUI">
    <meta name="keywords"
        content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <title>BiteHub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- End fonts -->

    <!-- core:css -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/core/core.css') }}">
    <!-- endinject -->

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/sweetalert2/sweetalert2.min.css') }}">
    <!-- End plugin css for this page -->

    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('backend/assets/fonts/feather-font/css/iconfont.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <!-- endinject -->

    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/demo2/style.css') }}">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.png') }}" />

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

    <style>
        .page-wrapper {
            overflow-x: hidden !important;
        }

        .main-wrapper {
            overflow-x: hidden;
        }
        
        @media (max-width: 768px) {
            .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .card { max-width: 100vw !important; overflow-x: hidden !important; }
            .card-body { padding: 15px !important; }
        }

        /* ─── Notifications Dropdown (Premium) ─────────────────── */
        .nav-item.dropdown:hover .notify-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
            transition-delay: 0s !important;
        }

        .nav-item.dropdown:not(:hover) .notify-dropdown {
            transition-delay: 0.6s !important;
        }

        .notify-dropdown {
            width: 320px !important;
            padding: 0 !important;
            overflow: hidden !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4) !important;
            background: #1a1a1b !important;
            border: 1px solid #333 !important;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            margin-top: 5px !important;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
            display: block !important; /* Override bootstrap display none */
            z-index: 1000;
        }

        .notify-dropdown::before {
            content: '';
            position: absolute;
            top: -25px;
            left: 0;
            right: 0;
            height: 25px;
            background: transparent;
        }

        .notify-header {
            padding: 14px 18px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #232324;
        }

        .notify-header span {
            font-weight: 800;
            font-size: 0.95rem;
            color: #fff;
        }

        .notify-header a {
            font-size: 0.75rem;
            color: #ff6b35;
            font-weight: 600;
            text-decoration: none;
        }

        .notify-body {
            max-height: 380px;
            overflow-y: auto;
        }

        .notify-item {
            display: flex;
            gap: 14px;
            padding: 14px 18px;
            text-decoration: none !important;
            border-bottom: 1px solid #333;
            transition: 0.2s;
            align-items: flex-start;
        }

        .notify-item:last-child {
            border-bottom: none;
        }

        .notify-item:hover {
            background: rgba(255,255,255,0.05) !important;
        }

        .notify-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #ff6b35;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .notify-icon.icon-order     { background: #ff6b35; }
        .notify-icon.icon-promotion { background: #10b981; }
        .notify-icon.icon-chat      { background: #3b82f6; }

        .notify-content {
            flex: 1;
            min-width: 0;
        }

        .notify-title {
            font-weight: 700;
            font-size: 0.88rem;
            color: #fff;
            margin-bottom: 3px;
        }

        .notify-msg {
            font-size: 0.82rem;
            color: #999;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notify-time {
            font-size: 0.72rem;
            color: #777;
            margin-top: 6px;
        }

        .notify-empty {
            padding: 40px 20px;
            text-align: center;
            color: #777;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .notify-footer {
            display: block;
            padding: 14px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff !important;
            background: #232324;
            text-decoration: none !important;
            border-top: 1px solid #333;
        }

        .notify-footer:hover {
            color: #ff6b35 !important;
        }
    </style>
    @stack('custom-styles')
</head>

<body>
    <div class="main-wrapper">

        <!-- partial:partials/_sidebar.html -->

        @include('admin.body.sidebar')

        <!-- partial -->

        <div class="page-wrapper">

            <!-- partial:partials/_navbar.html -->
            @include('admin.body.header')
            <!-- partial -->

            @yield('admin')

            <!-- partial:partials/_footer.html -->
            @include('admin.body.footer')
            <!-- partial -->

        </div>
    </div>

    <!-- core:js -->
    <script src="{{ asset('backend/assets/vendors/core/core.js') }}"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <script src="{{ asset('backend/assets/vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="{{ asset('backend/assets/vendors/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/template.js') }}"></script>
    <!-- endinject -->

    <!-- Custom js for this page -->
    <script src="{{ asset('backend/assets/js/dashboard-dark.js') }}"></script>
    <!-- End custom js for this page -->
    <script src="{{ asset('backend/assets/vendors/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Universal confirm helper
        $(document).on('click', '.confirm-submit', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const message = $(this).data('message') || 'Are you sure you want to proceed?';
            const icon = $(this).data('icon') || 'warning';

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, proceed!',
                background: '#1a1a1b',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    </script>


    <script>
        @if (Session::has('message'))
            var type = "{{ Session::get('alert-type', 'info') }}"
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            switch (type) {
                case 'info':
                    toastr.info(" {{ Session::get('message') }} ");
                    break;

                case 'success':
                    toastr.success(" {{ Session::get('message') }} ");
                    break;

                case 'warning':
                    toastr.warning(" {{ Session::get('message') }} ");
                    break;

                case 'error':
                    toastr.error(" {{ Session::get('message') }} ");
                    break;
            }
        @endif
    </script>
    @stack('custom-scripts')

    <!-- ═══════════════ GOOGLE TRANSLATE ═══════════════ -->
    <style>
        /* Hide the Google Translate UI elements and prevent padding jump */
        .goog-te-banner-frame,
        .VIpgJd-ZVi9od-ORHb-OEVmcd,
        #goog-gt-tt,
        iframe.goog-te-banner-frame {
            display: none !important;
        }

        body {
            top: 0px !important;
        }

        #google_translate_element {
            display: none !important;
        }

        font {
            background: transparent !important;
            box-shadow: none !important;
        }
    </style>
    <div id="google_translate_element"></div>
    <script>
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({ pageLanguage: 'en', includedLanguages: 'ar,en', autoDisplay: false }, 'google_translate_element');
            }
        function toggleLanguage() {
            let c = document.cookie;
            let isAr = c.includes('googtrans=/en/ar');

            // Clear all possible combinations of the cookie
            ['', location.hostname, '.' + location.hostname].forEach(function (domain) {
                let d = domain ? '; domain=' + domain : '';
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/' + d;
            });

            if (!isAr) {
                document.cookie = "googtrans=/en/ar; path=/";
            } else {
                document.cookie = "googtrans=/en/en; path=/";
            }
            location.reload();
        }
    </script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>


    @auth
    <!-- Enhanced Real-time Updates -->
    <script>
        let lastUnreadCount = 0;
        function updateRealtimeData() {
            const range = new URLSearchParams(window.location.search).get('range') || 'today';
            fetch(`{{ route('admin.realtime.stats') }}?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    // 1. Notifications Count & Indicator
                    const dropdown = document.querySelector('#notificationDropdown');
                    if (dropdown) {
                        let indicator = dropdown.querySelector('.indicator');
                        if (data.unreadNotificationsCount > 0) {
                            if (!indicator) {
                                indicator = document.createElement('div');
                                indicator.className = 'indicator';
                                indicator.innerHTML = '<div class="circle"></div>';
                                dropdown.appendChild(indicator);
                            }
                            // Optional: Sound alert if count increased
                            if (data.unreadNotificationsCount > lastUnreadCount) {
                                // new Audio('/upload/notification_sound.mp3').play().catch(()=>{}); 
                            }
                        } else if (indicator) {
                            indicator.remove();
                        }
                        lastUnreadCount = data.unreadNotificationsCount;
                    }

                    // 2. Notification List Update
                    const notifyBody = document.querySelector('.notify-body');
                    if (notifyBody && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(n => {
                            const readClass = n.isRead ? 'read' : 'unread';
                            html += `
                                <a href="/notifications/read/${n.id}" class="notify-item ${readClass}">
                                    <div class="notify-icon icon-${n.type}">
                                        <i data-feather="${n.icon}"></i>
                                    </div>
                                    <div class="notify-content">
                                        <div class="notify-title">${n.title}</div>
                                        <div class="notify-msg">${n.msg}</div>
                                        <div class="notify-time">${n.time}</div>
                                    </div>
                                </a>
                            `;
                        });
                        notifyBody.innerHTML = html;
                        if (window.feather) feather.replace(); // Refresh icons
                    }

                    // 3. Dashboard KPI Updates (if elements exist)
                    if (data.kpis) {
                        const elOrders = document.getElementById('kpi-orders');
                        if (elOrders) elOrders.innerText = data.kpis.totalOrders.toLocaleString();

                        const elRevenue = document.getElementById('kpi-revenue');
                        if (elRevenue) elRevenue.innerText = data.kpis.totalRevenue.toLocaleString();

                        const elWallets = document.getElementById('kpi-wallets');
                        if (elWallets) elWallets.innerText = (data.kpis.totalWalletsSum || 0).toLocaleString();
                        
                        const elCommission = document.getElementById('kpi-commission');
                        if (elCommission) elCommission.innerText = (data.kpis.siteCommission || 0).toLocaleString();
                    }
                })
                .catch(err => console.error('Real-time sync failed', err));
        }

        // Sync every 5 seconds for snappy feel
        setInterval(updateRealtimeData, 5000);
        updateRealtimeData(); // Initial run
    </script>
    @endauth
</body>

</html>