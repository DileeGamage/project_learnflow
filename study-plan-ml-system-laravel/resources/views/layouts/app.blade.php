<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LMS') - Smart Study Plan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('user_assets/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
        
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-graduation-cap"></i> <span>LearnFlow</span></h3>
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-content">
                <ul class="list-unstyled components">
                    @if(auth()->user()->is_admin)
                    <!-- Admin Section -->
                    <li class="sidebar-divider">
                        <hr class="sidebar-divider-line">
                        <span class="sidebar-divider-text">ADMIN PANEL</span>
                    </li>
                    <li class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <a href="#adminSubmenu" data-bs-toggle="collapse" class="dropdown-toggle" data-tooltip="Admin Panel">
                            <i class="fas fa-cogs"></i>
                            <span>Admin Panel</span>
                        </a>
                        <ul class="collapse list-unstyled {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">
                            <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt"></i> <span>Admin Dashboard</span></a></li>
                            <li><a href="{{ route('admin.users') }}"><i class="fas fa-users"></i> <span>User Management</span></a></li>
                            <li><a href="{{ route('admin.experiments') }}"><i class="fas fa-flask"></i> <span>A/B Experiments</span></a></li>
                            <li><a href="{{ route('admin.research') }}"><i class="fas fa-chart-line"></i> <span>Research Data</span></a></li>
                            <li><a href="{{ route('admin.analytics') }}"><i class="fas fa-chart-bar"></i> <span>System Analytics</span></a></li>
                        </ul>
                    </li>
                    
                    <!-- User Section -->
                    <li class="sidebar-divider">
                        <hr class="sidebar-divider-line">
                        <span class="sidebar-divider-text">USER FEATURES</span>
                    </li>
                    @endif
                    
                    <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" data-tooltip="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('notes.*') ? 'active' : '' }}">
                        <a href="{{ route('notes.index') }}" data-tooltip="Notes">
                            <i class="fas fa-sticky-note"></i>
                            <span>Notes</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('workplace.*') ? 'active' : '' }}">
                        <a href="{{ route('workplace.index') }}" data-tooltip="My Workplace">
                            <i class="fas fa-briefcase"></i>
                            <span>My Workplace</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('learning_journey.*') ? 'active' : '' }}">
                        <a href="{{ route('learning_journey.start') }}" data-tooltip="Start Learning">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Start Learning</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('questionnaire.*') ? 'active' : '' }}">
                        <a href="{{ route('questionnaire.index') }}" data-tooltip="Study Assessment">
                            <i class="fas fa-brain"></i>
                            <span>Study Assessment</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('questionnaires.*') ? 'active' : '' }}">
                        <a href="{{ route('questionnaires.index') }}" data-tooltip="Questionnaires">
                            <i class="fas fa-question-circle"></i>
                            <span>Questionnaires</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <a href="#analyticsSubmenu" data-bs-toggle="collapse" class="dropdown-toggle" data-tooltip="Analytics">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                        <ul class="collapse list-unstyled {{ request()->routeIs('analytics.*') ? 'show' : '' }}" id="analyticsSubmenu">
                            <li><a href="{{ route('analytics.index') }}"><i class="fas fa-chart-line"></i> <span>Overview</span></a></li>
                            <li><a href="{{ route('analytics.quiz') }}"><i class="fas fa-chart-pie"></i> <span>Quiz Analytics</span></a></li>
                            <li><a href="{{ route('analytics.performance') }}"><i class="fas fa-trophy"></i> <span>Performance</span></a></li>
                        </ul>
                    </li>
                    <li class="{{ request()->routeIs('gamification.*') ? 'active' : '' }}">
                        <a href="#gamificationSubmenu" data-bs-toggle="collapse" class="dropdown-toggle" data-tooltip="Gamification">
                            <i class="fas fa-gamepad"></i>
                            <span>Gamification</span>
                        </a>
                        <ul class="collapse list-unstyled {{ request()->routeIs('gamification.*') ? 'show' : '' }}" id="gamificationSubmenu">
                            <li><a href="{{ route('gamification.dashboard') }}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                            <li><a href="{{ route('gamification.achievements') }}"><i class="fas fa-trophy"></i> <span>Achievements</span></a></li>
                            <li><a href="{{ route('gamification.leaderboard') }}"><i class="fas fa-crown"></i> <span>Leaderboard</span></a></li>
                            <li><a href="{{ route('gamification.challenges') }}"><i class="fas fa-bullseye"></i> <span>Daily Challenges</span></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content" class="content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" id="mobileSidebarToggle" class="btn btn-outline-primary d-md-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="navbar-nav ms-auto">
                        <!-- Gamification Widget -->
                        <div class="nav-item me-3" id="gamification-widget">
                            <div class="d-flex align-items-center bg-primary text-white rounded px-3 py-1">
                                <span class="me-2"><i class="fas fa-gem"></i> <span id="user-points">0</span></span>
                                <span class="me-2"><i class="fas fa-level-up-alt"></i> Lvl <span id="user-level">1</span></span>
                                <span><i class="fas fa-fire"></i> <span id="user-streak">0</span></span>
                            </div>
                        </div>
                        
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> User
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('auth.profile') }}"><i class="fas fa-user-cog"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mt-3 px-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>

            <!-- Flash Messages -->
            <div class="px-3">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>

            <!-- Main Content -->
            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Enhanced Sidebar Script -->
    <script>
        $(document).ready(function () {
            const sidebar = $('#sidebar');
            const content = $('#content');
            const sidebarToggle = $('#sidebarToggle');
            const mobileSidebarToggle = $('#mobileSidebarToggle');
            const overlay = $('#overlay');

            // Load saved sidebar state
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                sidebar.addClass('collapsed');
                content.addClass('sidebar-collapsed');
            }

            // Desktop sidebar toggle
            sidebarToggle.on('click', function () {
                sidebar.toggleClass('collapsed');
                content.toggleClass('sidebar-collapsed');
                
                if (sidebar.hasClass('collapsed')) {
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            });

            // Mobile sidebar toggle
            mobileSidebarToggle.on('click', function () {
                sidebar.toggleClass('active');
                overlay.toggleClass('active');
            });

            // Close mobile sidebar when clicking overlay
            overlay.on('click', function () {
                sidebar.removeClass('active');
                overlay.removeClass('active');
            });

            // Close mobile sidebar when clicking on links
            $('.sidebar a').on('click', function () {
                if ($(window).width() <= 768) {
                    sidebar.removeClass('active');
                    overlay.removeClass('active');
                }
            });

            // Handle window resize
            $(window).on('resize', function () {
                if ($(window).width() > 768) {
                    sidebar.removeClass('active');
                    overlay.removeClass('active');
                }
            });

            // Enhanced dropdown behavior for collapsed sidebar
            $('.dropdown-toggle').on('click', function (e) {
                if (sidebar.hasClass('collapsed')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Enhanced sidebar scrolling functionality
            const sidebarContent = document.querySelector('.sidebar-content');
            const sidebarElement = document.getElementById('sidebar');
            
            if (sidebarContent && sidebarElement) {
                // Smooth scroll for sidebar
                sidebarContent.style.scrollBehavior = 'smooth';
                
                // Auto-scroll to active item on page load
                const activeItem = sidebarContent.querySelector('.active a');
                if (activeItem) {
                    setTimeout(() => {
                        activeItem.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }, 100);
                }
                
                // Add scroll indicators
                let scrollTimeout;
                sidebarContent.addEventListener('scroll', function() {
                    sidebarElement.classList.add('scrolling');
                    
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        sidebarElement.classList.remove('scrolling');
                    }, 150);
                });
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Make sure logout works
            $('#logout-form').on('submit', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    this.submit();
                }
            });

            // Load gamification widget data
            loadGamificationWidget();
        });

        function loadGamificationWidget() {
            fetch('{{ route("api.gamification.summary") }}')
                .then(response => response.json())
                .then(data => {
                    $('#user-points').text(data.points || 0);
                    $('#user-level').text(data.level || 1);
                    $('#user-streak').text(data.streak || 0);
                    
                    // Add hover tooltip with more info
                    $('#gamification-widget').attr('title', 
                        `Level: ${data.level_title || 'Novice Learner'}\\nRank: #${data.rank || 'N/A'}\\nProgress: ${data.level_progress || 0}%`
                    );
                })
                .catch(error => {
                    console.log('Gamification widget not loaded:', error);
                });
        }

        // Refresh widget after quiz completion or other point-earning activities
        window.refreshGamificationWidget = loadGamificationWidget;
    </script>
    
    <!-- Direct include of questionnaire JS -->
    <script src="{{ asset('js/questionnaire.js') }}"></script>
    
    <!-- Stacked Scripts -->
    @stack('scripts')
    
    @yield('scripts')
</body>
</html>