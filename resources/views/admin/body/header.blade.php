<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <form class="search-form">
            <div class="input-group">
                <div class="input-group-text">
                    <i data-feather="search"></i>
                </div>
                <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
            </div>
        </form>
        <ul class="navbar-nav">
            @php
                $isAr = isset($_COOKIE['googtrans']) && str_contains($_COOKIE['googtrans'], '/en/ar');
            @endphp
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="toggleLanguage()" title="Toggle Language" style="display:flex;align-items:center;">
                    <i data-feather="globe"></i>
                    <span class="ms-1 fw-bold" style="font-size:0.75rem;">{{ $isAr ? 'EN' : 'AR' }}</span>
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="appsDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="grid"></i>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="appsDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p class="mb-0 fw-bold">Web Apps</p>
                        <a href="javascript:;" class="text-muted">Edit</a>
                    </div>
                    <div class="row g-0 p-1">
                        <div class="col-3 text-center">
                            <a href="pages/apps/chat.html"
                                class="dropdown-item d-flex flex-column align-items-center justify-content-center wd-70 ht-70"><i
                                    data-feather="message-square" class="icon-lg mb-1"></i>
                                <p class="tx-12">Chat</p>
                            </a>
                        </div>
                        <div class="col-3 text-center">
                            <a href="pages/apps/calendar.html"
                                class="dropdown-item d-flex flex-column align-items-center justify-content-center wd-70 ht-70"><i
                                    data-feather="calendar" class="icon-lg mb-1"></i>
                                <p class="tx-12">Calendar</p>
                            </a>
                        </div>
                        <div class="col-3 text-center">
                            <a href="pages/email/inbox.html"
                                class="dropdown-item d-flex flex-column align-items-center justify-content-center wd-70 ht-70"><i
                                    data-feather="mail" class="icon-lg mb-1"></i>
                                <p class="tx-12">Email</p>
                            </a>
                        </div>
                        <div class="col-3 text-center">
                            <a href="pages/general/profile.html"
                                class="dropdown-item d-flex flex-column align-items-center justify-content-center wd-70 ht-70"><i
                                    data-feather="instagram" class="icon-lg mb-1"></i>
                                <p class="tx-12">Profile</p>
                            </a>
                        </div>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="javascript:;">View all</a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="mail"></i>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="messageDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p>9 New Messages</p>
                        <a href="javascript:;" class="text-muted">Clear all</a>
                    </div>
                    <div class="p-1">
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="me-3">
                                <img class="wd-30 ht-30 rounded-circle"   src="{{ (!empty($profileData->Image) && file_exists(public_path('upload/admin_images/'.$profileData->Image))) ? url('upload/admin_images/'.$profileData->Image) : url('upload/no_image.jpg') }}"
                                    alt="userr">
                            </div>
                            <div class="d-flex justify-content-between flex-grow-1">
                                <div class="me-4">
                                    <p>Leonardo Payne</p>
                                    <p class="tx-12 text-muted">Project status</p>
                                </div>
                                <p class="tx-12 text-muted">2 min ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="me-3">
                                <img class="wd-30 ht-30 rounded-circle"   src="{{ (!empty($profileData->Image) && file_exists(public_path('upload/admin_images/'.$profileData->Image))) ? url('upload/admin_images/'.$profileData->Image) : url('upload/no_image.jpg') }}"
                                    alt="userr">
                            </div>
                            <div class="d-flex justify-content-between flex-grow-1">
                                <div class="me-4">
                                    <p>Carl Henson</p>
                                    <p class="tx-12 text-muted">Client meeting</p>
                                </div>
                                <p class="tx-12 text-muted">30 min ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="me-3">
                                <img class="wd-30 ht-30 rounded-circle" src="{{ (!empty(Auth::user()->Image) && file_exists(public_path('upload/admin_images/'.Auth::user()->Image))) ? url('upload/admin_images/'.Auth::user()->Image) : url('upload/no_image.jpg') }}"
                                    alt="userr">
                            </div>
                            <div class="d-flex justify-content-between flex-grow-1">
                                <div class="me-4">
                                    <p>Jensen Combs</p>
                                    <p class="tx-12 text-muted">Project updates</p>
                                </div>
                                <p class="tx-12 text-muted">1 hrs ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="me-3">
                                <img class="wd-30 ht-30 rounded-circle" src="{{ (!empty(Auth::user()->Image) && file_exists(public_path('upload/admin_images/'.Auth::user()->Image))) ? url('upload/admin_images/'.Auth::user()->Image) : url('upload/no_image.jpg') }}"
                                    alt="userr">
                            </div>
                            <div class="d-flex justify-content-between flex-grow-1">
                                <div class="me-4">
                                    <p>Amiah Burton</p>
                                    <p class="tx-12 text-muted">Project deatline</p>
                                </div>
                                <p class="tx-12 text-muted">2 hrs ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="me-3">
                                <img class="wd-30 ht-30 rounded-circle" src="{{ (!empty(Auth::user()->Image) && file_exists(public_path('upload/admin_images/'.Auth::user()->Image))) ? url('upload/admin_images/'.Auth::user()->Image) : url('upload/no_image.jpg') }}"
                                    alt="userr">
                            </div>
                            <div class="d-flex justify-content-between flex-grow-1">
                                <div class="me-4">
                                    <p>Yaretzi Mayo</p>
                                    <p class="tx-12 text-muted">New record</p>
                                </div>
                                <p class="tx-12 text-muted">5 hrs ago</p>
                            </div>
                        </a>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="javascript:;">View all</a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell"></i>
                    @if(isset($headerNotifications) && $headerNotifications->where('IsRead', false)->count() > 0)
                    <div class="indicator">
                        <div class="circle"></div>
                    </div>
                    @endif
                </a>
                <div class="dropdown-menu notify-dropdown" aria-labelledby="notificationDropdown">
                    <div class="notify-header">
                        <span>Notifications</span>
                        <a href="{{ route('notifications.clear') }}">Clear all</a>
                    </div>
                    <div class="notify-body">
                        @forelse($headerNotifications ?? [] as $notification)
                        <a href="{{ route('notifications.read', $notification->NotificationID) }}" class="notify-item {{ $notification->IsRead ? 'read' : 'unread' }}">
                            <div class="notify-icon icon-{{ strtolower($notification->Type) }}">
                                @php
                                    $icon = match($notification->Type) {
                                        'Order' => 'shopping-cart',
                                        'Promotion' => 'gift',
                                        'Chat' => 'message-square',
                                        default => 'bell',
                                    };
                                @endphp
                                <i data-feather="{{ $icon }}"></i>
                            </div>
                            <div class="notify-content">
                                <div class="notify-title">{{ $notification->Title }}</div>
                                <div class="notify-msg">{{ $notification->Message }}</div>
                                <div class="notify-time">{{ \Carbon\Carbon::parse($notification->CreatedAt)->diffForHumans() }}</div>
                            </div>
                        </a>
                        @empty
                        <div class="notify-empty">
                            <i data-feather="bell-off"></i>
                            <p>No notifications</p>
                        </div>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}" class="notify-footer">
                        View all
                    </a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @auth
                        @php
                            $user = Auth::user();
                            $kImg = null;
                            if($user->Role === 'KitchenOwner' && $user->kitchenOwner) {
                                $kn = strtolower($user->kitchenOwner->KitchenName);
                                $kImg = 'kitchen_default.png';
                                if(str_contains($kn, 'mama')) $kImg = 'avatar_mama.png';
                                elseif(str_contains($kn, 'rania')) $kImg = 'avatar_rania.png';
                                elseif(str_contains($kn, 'nour')) $kImg = 'drinks.png';
                                elseif(str_contains($kn, 'heba')) $kImg = 'healthy.png';
                                elseif(str_contains($kn, 'samira')) $kImg = 'seafood.png';
                            }
                            $profileUrl = (!empty($user->Image) && file_exists(public_path('upload/admin_images/'.$user->Image))) ? url('upload/admin_images/'.$user->Image) : ($kImg ? url('upload/website_assets/'.$kImg) : url('upload/no_image.jpg'));
                        @endphp
                        <img class="wd-30 ht-30 rounded-circle" src="{{ $profileUrl }}" alt="profile">
                    @else
                        <img class="wd-30 ht-30 rounded-circle" src="https://ui-avatars.com/api/?name=Guest&background=111&color=fff" alt="guest">
                    @endauth
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    @auth
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img class="wd-80 ht-80 rounded-circle" src="{{ $profileUrl }}" alt="">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{ Auth::user()->name ?? Auth::user()->FullName }}</p>
                            <p class="tx-12 text-muted">{{ Auth::user()->email ?? Auth::user()->Email }}</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        @php
                            $role = Auth::user()->Role;
                            $profileRoute  = match($role) {
                                'KitchenOwner'  => route('kitchen.profile'),
                                'Caterer'       => route('caterer.profile'),
                                'DeliveryAgent' => route('agent.profile'),
                                'Customer'      => route('dashboard.customer'),
                                default         => route('admin.profile'),
                            };
                            $passRoute = match($role) {
                                'KitchenOwner'  => route('kitchen.change.password'),
                                'Caterer'       => route('caterer.change.password'),
                                'DeliveryAgent' => route('agent.change.password'),
                                'Customer'      => '#',
                                default         => route('admin.change.password'),
                            };
                            $logoutRoute = match($role) {
                                'KitchenOwner'  => route('kitchen.logout'),
                                'Caterer'       => route('caterer.logout'),
                                'DeliveryAgent' => route('agent.logout'),
                                default         => null,
                            };
                        @endphp
                        <li class="dropdown-item py-2">
                            <a href="{{ $profileRoute }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="user"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        @if($role !== 'Customer')
                        <li class="dropdown-item py-2">
                            <a href="{{ $passRoute }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="edit"></i>
                                <span>Change Password</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="javascript:;" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="repeat"></i>
                                <span>{{ $role }}</span>
                            </a>
                        </li>
                        @endif
                        <li class="dropdown-item py-2">
                            @if($logoutRoute)
                            <a href="{{ $logoutRoute }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                            @else
                            <form method="POST" action="{{ route('logout') }}" id="logoutFormHeader">@csrf</form>
                            <a href="#" class="text-body ms-0" onclick="document.getElementById('logoutFormHeader').submit()">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                            @endif
                        </li>
                    </ul>
                    @else
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">Welcome, Guest!</p>
                            <p class="tx-12 text-muted">Join us today</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2">
                            <a href="{{ route('login') }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="log-in"></i>
                                <span>Login</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="{{ route('register') }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="user-plus"></i>
                                <span>Sign Up</span>
                            </a>
                        </li>
                    </ul>
                    @endauth
                </div>
            </li>
        </ul>
    </div>
</nav>
