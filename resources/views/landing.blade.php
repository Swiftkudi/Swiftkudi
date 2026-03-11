<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftKudi - Hire Top Freelancers & Get Quality Work Done | Post Tasks, Manage Projects, Pay Securely</title>
    <meta name="description" content="The premier task posting marketplace for businesses. Post tasks, hire vetted freelancers, manage projects with secure escrow payments. Scale your workforce on-demand with quality guarantees.">
    <meta name="keywords" content="hire freelancers Nigeria, post tasks, task marketplace, freelance platform, escrow payments, business outsourcing, hire workers, project management,Milestone payments, workforce scaling">
    <meta name="author" content="SwiftKudi">
    <meta property="og:title" content="SwiftKudi - Hire Top Freelancers & Get Quality Work Done">
    <meta property="og:description" content="Post tasks, hire skilled freelancers, and manage your projects with secure escrow payments. The reliable platform for businesses who need results.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://swiftkudi.com">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SwiftKudi - Hire Top Freelancers & Get Quality Work Done">
    <meta name="twitter:description" content="Post tasks, hire skilled freelancers, and manage your projects with secure escrow payments.">
    <link rel="canonical" href="https://swiftkudi.com">
    
    {{-- Laravel Mix Assets - Tailwind CSS compiled via Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
    
    {{-- External Libraries --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --font-heading-name: 'Plus Jakarta Sans';
        }
        
        /* Dark mode base styles */
        body {
            background-color: #020617;
            color: #f1f5f9;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-text-alt {
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out infinite; animation-delay: 2s; }
        .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .review-card {
            transition: all 0.3s ease;
        }
        .review-card:hover {
            transform: translateY(-5px);
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 min-h-screen font-sans">
    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-pink-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/3 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="fixed top-0 left-0 h-full w-72 bg-dark-900 z-50 transform -translate-x-full transition-transform duration-300 md:hidden">
        <div class="p-4 border-b border-dark-700">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-coins text-white text-lg"></i>
                    </div>
                    <span class="ml-3 text-xl font-bold gradient-text">SwiftKudi</span>
                </a>
                <button onclick="closeMobileMenu()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <nav class="p-4 space-y-2">
            <a href="#why-businesses" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                <i class="fas fa-briefcase mr-3 w-5"></i>Why Businesses
            </a>
            <a href="#features" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                <i class="fas fa-star mr-3 w-5"></i>Features
            </a>
            <a href="#how-it-works" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                <i class="fas fa-route mr-3 w-5"></i>How It Works
            </a>
            <a href="#marketplace" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                <i class="fas fa-store mr-3 w-5"></i>Marketplace
            </a>
            <a href="#reviews" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                <i class="fas fa-comment mr-3 w-5"></i>Reviews
            </a>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-dark-700 space-y-3">
            <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-4 py-3 rounded-xl bg-dark-800 text-gray-300 hover:text-white transition-all">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </a>
            <a href="{{ route('register') }}?type=advertiser" class="w-full flex items-center justify-center px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-semibold transition-all">
                <i class="fas fa-briefcase mr-2"></i>Post a Task
            </a>
        </div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-dark-900/80 border-b border-dark-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800" onclick="openMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all">
                            <i class="fas fa-coins text-white text-base sm:text-lg"></i>
                        </div>
                        <span class="ml-2 sm:ml-3 text-lg sm:text-xl font-bold font-heading gradient-text">SwiftKudi</span>
                    </a>
                </div>
                <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                    <a href="#why-businesses" class="text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">Why Businesses</a>
                    <a href="#features" class="text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">Features</a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">How It Works</a>
                    <a href="#marketplace" class="text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">Marketplace</a>
                    <a href="#reviews" class="text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">Reviews</a>
                </nav>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 hover:text-indigo-400 transition-all">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </a>
                    <a href="{{ route('register') }}?type=advertiser" class="inline-flex items-center px-3 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all transform hover:scale-105">
                        <i class="fas fa-briefcase mr-2"></i>
                        <span class="hidden sm:inline">Post a Task</span>
                        <span class="sm:hidden">Post Task</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative py-16 md:py-24 lg:py-32 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center px-4 py-2 bg-indigo-500/20 rounded-full mb-6 border border-indigo-500/30">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-sm font-medium text-indigo-300">Trusted by 2,500+ Businesses</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-heading font-extrabold mb-6 leading-tight">
                        <span class="text-white">Get Quality Work.</span><br>
                        <span class="gradient-text">Build Your Team.</span><br>
                        <span class="text-white">Scale Faster.</span>
                    </h1>
                    <p class="text-base sm:text-lg md:text-xl text-gray-400 mb-8 max-w-xl mx-auto lg:mx-0">
                        The #1 marketplace for businesses who need results. Post tasks, hire vetted freelancers, and manage projects with <span class="font-semibold text-indigo-400">secure escrow payments</span>. Only pay when you're satisfied.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('tasks.create.new') }}" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-xl shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all transform hover:scale-105">
                            <i class="fas fa-briefcase mr-2"></i>
                            Post Your First Task
                        </a>
                        <a href="#how-it-works" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 sm:py-4 bg-dark-800 text-gray-300 font-bold rounded-xl shadow-lg border-2 border-dark-700 hover:border-indigo-500 transition-all">
                            <i class="fas fa-play-circle mr-2 text-indigo-400"></i>
                            See How It Works
                        </a>
                    </div>
                    
                    <!-- Trust Badges for Advertisers -->
                    <div class="mt-8 lg:mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6">
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-shield-alt text-green-500"></i>
                            <span>Secure Escrow</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-check-circle text-blue-500"></i>
                            <span>Vetted Workers</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-headset text-purple-500"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image/Stats Card -->
                <div class="relative">
                    <div class="glass-card rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xl">
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                            <div class="text-center p-3 sm:p-4 md:p-6 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-xl sm:rounded-2xl">
                                <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold gradient-text">50K+</div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 sm:mt-2">Tasks Completed</div>
                            </div>
                            <div class="text-center p-3 sm:p-4 md:p-6 bg-gradient-to-br from-green-500/10 to-emerald-500/10 rounded-xl sm:rounded-2xl">
                                <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold gradient-text-alt">2.5K+</div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 sm:mt-2">Businesses</div>
                            </div>
                            <div class="text-center p-3 sm:p-4 md:p-6 bg-gradient-to-br from-pink-500/10 to-rose-500/10 rounded-xl sm:rounded-2xl">
                                <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-pink-500">98%</div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 sm:mt-2">Success Rate</div>
                            </div>
                            <div class="text-center p-3 sm:p-4 md:p-6 bg-gradient-to-br from-yellow-500/10 to-orange-500/10 rounded-xl sm:rounded-2xl">
                                <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-yellow-500">4.9★</div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 sm:mt-2">Satisfaction</div>
                            </div>
                        </div>
                        
                        <!-- Mini Activity Feed - Business Focused -->
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center gap-3 p-3 bg-green-500/10 rounded-xl">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">TechStartup Inc posted a new task</p>
                                    <p class="text-xs text-gray-500">UI Design • 2 min ago</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-blue-500/10 rounded-xl">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Marketer Co hired a freelancer</p>
                                    <p class="text-xs text-gray-500">Content Writer • 5 min ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Businesses Choose SwiftKudi -->
    <section id="why-businesses" class="py-16 md:py-20 bg-dark-900/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-emerald-500/20 rounded-full mb-4">
                    <i class="fas fa-briefcase text-emerald-400 mr-2"></i>
                    <span class="text-sm font-medium text-emerald-300">Why Businesses Choose SwiftKudi</span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Everything You Need to <span class="gradient-text">Get Work Done</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-400 max-w-2xl mx-auto">
                    From startups to enterprises, thousands of businesses trust SwiftKudi to get quality work delivered on time, every time.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <!-- Benefit 1 -->
                <div class="group p-6 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 hover:shadow-xl hover:shadow-indigo-500/10 transition-all">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-shield-halved text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Secure Escrow Payments</h3>
                    <p class="text-gray-400">Your funds are protected until work is approved. Only pay for quality results, not promises.</p>
                </div>
                
                <!-- Benefit 2 -->
                <div class="group p-6 bg-pink-500/10 rounded-2xl border border-pink-500/20 hover:shadow-xl hover:shadow-pink-500/10 transition-all">
                    <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-pink-500/30">
                        <i class="fas fa-users-gear text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Vetted Freelancers</h3>
                    <p class="text-gray-400">Access verified, skilled workers ready to deliver quality work. Review ratings and past performance.</p>
                </div>
                
                <!-- Benefit 3 -->
                <div class="group p-6 bg-emerald-500/10 rounded-2xl border border-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/10 transition-all">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/30">
                        <i class="fas fa-chart-pie text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Milestone Management</h3>
                    <p class="text-gray-400">Break projects into milestones. Release payments gradually as work gets delivered.</p>
                </div>
                
                <!-- Benefit 4 -->
                <div class="group p-6 bg-orange-500/10 rounded-2xl border border-orange-500/20 hover:shadow-xl hover:shadow-orange-500/10 transition-all">
                    <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30">
                        <i class="fas fa-layer-group text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Scale On Demand</h3>
                    <p class="text-gray-400">Hire one freelancer or build a whole team. Scale your workforce up or down as needed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-blue-500/20 rounded-full mb-4">
                    <i class="fas fa-sparkles text-blue-400 mr-2"></i>
                    <span class="text-sm font-medium text-blue-300">Powerful Tools for Business</span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Tools That <span class="gradient-text">Drive Results</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-400 max-w-2xl mx-auto">
                    Everything you need to post tasks, manage freelancers, and ensure quality delivery.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1 -->
                <div class="feature-card group p-6 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 hover:shadow-xl hover:shadow-indigo-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30 transition-transform">
                        <i class="fas fa-tasks text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Easy Task Posting</h3>
                    <p class="text-gray-400">Post tasks in minutes. Set requirements, deadlines, and budget. Get matched with qualified workers.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card group p-6 bg-pink-500/10 rounded-2xl border border-pink-500/20 hover:shadow-xl hover:shadow-pink-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-pink-500/30 transition-transform">
                        <i class="fas fa-file-signature text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Milestone Payments</h3>
                    <p class="text-gray-400">Break projects into milestones. Approve each stage before releasing payment. Control your cash flow.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card group p-6 bg-emerald-500/10 rounded-2xl border border-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/30 transition-transform">
                        <i class="fas fa-magnifying-glass-chart text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Worker Verification</h3>
                    <p class="text-gray-400">View worker profiles, ratings, completed tasks, and reviews. Make informed hiring decisions.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card group p-6 bg-orange-500/10 rounded-2xl border border-orange-500/20 hover:shadow-xl hover:shadow-orange-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30 transition-transform">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Built-in Messaging</h3>
                    <p class="text-gray-400">Communicate directly with freelancers. Share files, discuss requirements, and track progress.</p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card group p-6 bg-blue-500/10 rounded-2xl border border-blue-500/20 hover:shadow-xl hover:shadow-blue-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-blue-500/30 transition-transform">
                        <i class="fas fa-clipboard-check text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Work Approval</h3>
                    <p class="text-gray-400">Review submissions before approval. Request revisions or approve and release payment.</p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card group p-6 bg-violet-500/10 rounded-2xl border border-violet-500/20 hover:shadow-xl hover:shadow-violet-500/10 transition-all">
                    <div class="feature-icon w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-violet-500/30 transition-transform">
                        <i class="fas fa-headset text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Dispute Resolution</h3>
                    <p class="text-gray-400">Our team helps resolve any issues. Fair mediation protects both businesses and workers.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-16 md:py-20 bg-dark-900/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-purple-500/20 rounded-full mb-4">
                    <i class="fas fa-route text-purple-400 mr-2"></i>
                    <span class="text-sm font-medium text-purple-300">Simple Process</span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Get Work Done in <span class="gradient-text">3 Steps</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-400">From posting to payment - get quality work delivered</p>
            </div>
            
            <div class="relative">
                <!-- Connection Line -->
                <div class="hidden md:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 transform -translate-y-1/2 rounded-full"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 relative">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="bg-dark-800 rounded-2xl p-6 md:p-8 shadow-xl border-2 border-green-500/30 hover:border-green-400 transition-all">
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                                1
                            </div>
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-green-500/30">
                                <i class="fas fa-pen-to-square text-white text-2xl md:text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 text-center">Post Your Task</h3>
                            <p class="text-gray-400 text-center">Describe your requirements, set your budget, and define deadlines. Our platform makes it easy.</p>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="bg-dark-800 rounded-2xl p-6 md:p-8 shadow-xl border-2 border-blue-500/30 hover:border-blue-400 transition-all">
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                                2
                            </div>
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/30">
                                <i class="fas fa-user-check text-white text-2xl md:text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 text-center">Hire the Best</h3>
                            <p class="text-gray-400 text-center">Review proposals, check worker ratings, and hire the best match for your project.</p>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="relative">
                        <div class="bg-dark-800 rounded-2xl p-6 md:p-8 shadow-xl border-2 border-purple-500/30 hover:border-purple-400 transition-all">
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                                3
                            </div>
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/30">
                                <i class="fas fa-check-double text-white text-2xl md:text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 text-center">Approve & Pay</h3>
                            <p class="text-gray-400 text-center">Review the completed work, approve quality, and release payment securely.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Marketplace Section -->
    <section id="marketplace" class="py-16 md:py-20 bg-gradient-to-br from-indigo-950/30 via-purple-950/30 to-pink-950/30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-purple-500/20 rounded-full mb-4">
                    <i class="fas fa-store text-purple-400 mr-2"></i>
                    <span class="text-sm font-medium text-purple-300">What You Can Outsource</span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Find Skills for <span class="gradient-text">Any Project</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-400 max-w-2xl mx-auto">
                    From simple micro-tasks to complex professional projects. Get the help you need.
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Micro Tasks -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-2xl hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-2 cursor-pointer">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative">
                        <div class="text-4xl md:text-5xl mb-4">⚡</div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Micro Tasks</h3>
                        <p class="text-indigo-100 text-sm mb-4">Quick tasks for fast results</p>
                        <ul class="text-xs text-indigo-200 space-y-1">
                            <li><i class="fas fa-check mr-1"></i> Social Media Marketing</li>
                            <li><i class="fas fa-check mr-1"></i> Reviews & Testimonials</li>
                            <li><i class="fas fa-check mr-1"></i> App Testing</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Professional Services -->
                <div class="group relative overflow-hidden bg-dark-800 rounded-2xl p-6 border-2 border-dark-700 hover:border-emerald-500 hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative">
                        <div class="text-4xl md:text-5xl mb-4">💼</div>
                        <h3 class="text-lg md:text-xl font-bold text-white mb-2">Pro Services</h3>
                        <p class="text-gray-400 text-sm mb-4">Expert freelancers for any need</p>
                        <ul class="text-xs text-gray-400 space-y-1">
                            <li><i class="fas fa-check text-emerald-500 mr-1"></i> Graphic Design</li>
                            <li><i class="fas fa-check text-emerald-500 mr-1"></i> Web Development</li>
                            <li><i class="fas fa-check text-emerald-500 mr-1"></i> Content Writing</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Growth Services -->
                <div class="group relative overflow-hidden bg-dark-800 rounded-2xl p-6 border-2 border-dark-700 hover:border-orange-500 hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative">
                        <div class="text-4xl md:text-5xl mb-4">📈</div>
                        <h3 class="text-lg md:text-xl font-bold text-white mb-2">Growth</h3>
                        <p class="text-gray-400 text-sm mb-4">Scale your business</p>
                        <ul class="text-xs text-gray-400 space-y-1">
                            <li><i class="fas fa-check text-orange-500 mr-1"></i> SEO & Backlinks</li>
                            <li><i class="fas fa-check text-orange-500 mr-1"></i> Influencer Marketing</li>
                            <li><i class="fas fa-check text-orange-500 mr-1"></i> Lead Generation</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Digital Products -->
                <div class="group relative overflow-hidden bg-dark-800 rounded-2xl p-6 border-2 border-dark-700 hover:border-blue-500 hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative">
                        <div class="text-4xl md:text-5xl mb-4">🛒</div>
                        <h3 class="text-lg md:text-xl font-bold text-white mb-2">Digital Products</h3>
                        <p class="text-gray-400 text-sm mb-4">Templates & resources</p>
                        <ul class="text-xs text-gray-400 space-y-1">
                            <li><i class="fas fa-check text-blue-500 mr-1"></i> Business Templates</li>
                            <li><i class="fas fa-check text-blue-500 mr-1"></i> Marketing Assets</li>
                            <li><i class="fas fa-check text-blue-500 mr-1"></i> Software Tools</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-yellow-500/20 rounded-full mb-4">
                    <i class="fas fa-star text-yellow-400 mr-2"></i>
                    <span class="text-sm font-medium text-yellow-300">What Businesses Say</span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Trusted by <span class="gradient-text">2,500+ Businesses</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-400">Real results from business owners who got work done</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Review 1 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"We needed 50 product descriptions written in a week. Posted the task on Monday, had quality submissions by Wednesday. Game changer for our marketing."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">T</div>
                        <div>
                            <h4 class="font-bold text-white">Tunde Industries</h4>
                            <p class="text-sm text-gray-500">Lagos • E-commerce</p>
                        </div>
                    </div>
                </div>
                
                <!-- Review 2 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"The escrow system gives us peace of mind. We only pay when work is approved. Found an amazing designer who's now our go-to freelancer."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold">S</div>
                        <div>
                            <h4 class="font-bold text-white">Sarah's Boutique</h4>
                            <p class="text-sm text-gray-500">Abuja • Retail</p>
                        </div>
                    </div>
                </div>
                
                <!-- Review 3 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"As a startup, we can't hire full-time staff for everything. SwiftKudi lets us access skilled professionals for specific projects. Huge cost saver."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center text-white font-bold">T</div>
                        <div>
                            <h4 class="font-bold text-white">TechVentures Ltd</h4>
                            <p class="text-sm text-gray-500">Port Harcourt • Tech Startup</p>
                        </div>
                    </div>
                </div>
                
                <!-- Review 4 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star-half-alt text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"We use SwiftKudi for all our social media growth. Posted tasks for likes, follows, and reviews. Our engagement has tripled in 2 months."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-full flex items-center justify-center text-white font-bold">O</div>
                        <div>
                            <h4 class="font-bold text-white">Oladipo Marketing</h4>
                            <p class="text-sm text-gray-500">Ibadan • Digital Agency</p>
                        </div>
                    </div>
                </div>
                
                <!-- Review 5 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"Needed a website redesign for our restaurant. Found an amazing developer through the platform. The milestone payment system kept them on track."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center text-white font-bold">G</div>
                        <div>
                            <h4 class="font-bold text-white">Golden Fork Restaurants</h4>
                            <p class="text-sm text-gray-500">Kano • Hospitality</p>
                        </div>
                    </div>
                </div>
                
                <!-- Review 6 -->
                <div class="review-card bg-dark-800 rounded-2xl p-6 shadow-lg border border-dark-700">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 mb-6">"The ability to scale our workforce up or down is incredible. We hired 10 freelancers for a product launch, then scaled back. Perfect for our business model."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">N</div>
                        <div>
                            <h4 class="font-bold text-white">Nova Events Co</h4>
                            <p class="text-sm text-gray-500">Enugu • Events Company</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Trust Indicators -->
            <div class="mt-12 md:mt-16 flex flex-wrap justify-center items-center gap-4 md:gap-8">
                <div class="flex items-center gap-2 px-4 md:px-6 py-2 md:py-3 bg-green-500/10 rounded-full">
                    <i class="fas fa-shield-alt text-green-500"></i>
                    <span class="text-sm font-medium text-green-400">Escrow Protected</span>
                </div>
                <div class="flex items-center gap-2 px-4 md:px-6 py-2 md:py-3 bg-blue-500/10 rounded-full">
                    <i class="fas fa-building text-blue-500"></i>
                    <span class="text-sm font-medium text-blue-400">2,500+ Businesses</span>
                </div>
                <div class="flex items-center gap-2 px-4 md:px-6 py-2 md:py-3 bg-purple-500/10 rounded-full">
                    <i class="fas fa-award text-purple-500"></i>
                    <span class="text-sm font-medium text-purple-400">98% Success Rate</span>
                </div>
                <div class="flex items-center gap-2 px-4 md:px-6 py-2 md:py-3 bg-yellow-500/10 rounded-full">
                    <i class="fas fa-star text-yellow-500"></i>
                    <span class="text-sm font-medium text-yellow-400">4.9/5 Rating</span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 md:py-20 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-heading font-bold text-white mb-4 md:mb-6">Ready to Get Work Done?</h2>
            <p class="text-lg md:text-xl text-indigo-100 mb-6 md:mb-8 max-w-2xl mx-auto">Join 2,500+ businesses already using SwiftKudi to hire top freelancers and get quality work delivered.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('tasks.create.new') }}" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 sm:py-4 bg-white text-indigo-600 font-bold rounded-xl shadow-xl hover:bg-gray-100 transition-all transform hover:scale-105">
                    <i class="fas fa-briefcase mr-2"></i>
                    Post Your First Task
                </a>
                <a href="#how-it-works" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 sm:py-4 bg-transparent text-white font-bold rounded-xl border-2 border-white/30 hover:bg-white/10 transition-all">
                    <i class="fas fa-info-circle mr-2"></i>
                    Learn More
                </a>
            </div>
            <p class="mt-6 text-indigo-200 text-sm">No credit card required • Free to post tasks • Pay only for approved work</p>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 md:py-20 bg-dark-900/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                    Frequently Asked <span class="gradient-text">Questions</span>
                </h2>
            </div>
            
            <div class="space-y-4">
                <details class="group bg-dark-800 rounded-xl p-4 md:p-6 cursor-pointer">
                    <summary class="flex justify-between items-center font-semibold text-white">
                        <span>How does escrow payment work?</span>
                        <i class="fas fa-chevron-down text-gray-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-gray-400">When you fund a task, your money is held securely in escrow. It is only released to the freelancer once you approve the completed work. This protects you from scams and ensures quality delivery.</p>
                </details>
                
                <details class="group bg-dark-800 rounded-xl p-4 md:p-6 cursor-pointer">
                    <summary class="flex justify-between items-center font-semibold text-white">
                        <span>How do I know the workers are reliable?</span>
                        <i class="fas fa-chevron-down text-gray-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-gray-400">All workers have profiles showing their ratings, completed tasks, and reviews from previous clients. You can also require verification badges and check their work history before hiring.</p>
                </details>
                
                <details class="group bg-dark-800 rounded-xl p-4 md:p-6 cursor-pointer">
                    <summary class="flex justify-between items-center font-semibold text-white">
                        <span>What if I'm not satisfied with the work?</span>
                        <i class="fas fa-chevron-down text-gray-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-gray-400">You can request revisions until the work meets your requirements. If issues can't be resolved, our dispute resolution team will mediate. Since funds are in escrow, you're protected until you approve the work.</p>
                </details>
                
                <details class="group bg-dark-800 rounded-xl p-4 md:p-6 cursor-pointer">
                    <summary class="flex justify-between items-center font-semibold text-white">
                        <span>How much does it cost to post tasks?</span>
                        <i class="fas fa-chevron-down text-gray-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-gray-400">Posting tasks is free! You only pay when you approve completed work. The platform takes a small service fee from the total project budget, which covers payment processing and platform maintenance.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark-950 border-t border-dark-700 py-8 md:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 mb-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-coins text-white text-sm"></i>
                        </div>
                        <span class="ml-3 font-bold text-white text-lg">SwiftKudi</span>
                    </div>
                    <p class="text-gray-400 text-sm">The #1 marketplace for businesses to hire freelancers and get quality work done with secure escrow payments.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3 md:mb-4">For Businesses</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#features" class="hover:text-indigo-400 transition-colors">Features</a></li>
                        <li><a href="#how-it-works" class="hover:text-indigo-400 transition-colors">How It Works</a></li>
                        <li><a href="{{ route('tasks.create.new') }}" class="hover:text-indigo-400 transition-colors">Post a Task</a></li>
                        <li><a href="#pricing" class="hover:text-indigo-400 transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3 md:mb-4">Marketplace</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/tasks" class="hover:text-indigo-400 transition-colors">Micro Tasks</a></li>
                        <li><a href="/services" class="hover:text-indigo-400 transition-colors">Pro Services</a></li>
                        <li><a href="/growth" class="hover:text-indigo-400 transition-colors">Growth</a></li>
                        <li><a href="/products" class="hover:text-indigo-400 transition-colors">Digital Products</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3 md:mb-4">Connect</h4>
                    <div class="flex gap-3">
                        <a href="#" class="w-9 h-9 md:w-10 md:h-10 bg-dark-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-400 hover:bg-dark-700 transition-all">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-9 h-9 md:w-10 md:h-10 bg-dark-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-400 hover:bg-dark-700 transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-9 h-9 md:w-10 md:h-10 bg-dark-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-400 hover:bg-dark-700 transition-all">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="w-9 h-9 md:w-10 md:h-10 bg-dark-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-400 hover:bg-dark-700 transition-all">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-dark-700 pt-6 md:pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-sm text-gray-500">© {{ date('Y') }} SwiftKudi. All rights reserved.</p>
                <div class="flex gap-4 md:gap-6 mt-4 md:mt-0">
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-400">Privacy Policy</a>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-400">Terms of Service</a>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-400">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu functions
        function openMobileMenu() {
            document.getElementById('mobile-menu').classList.remove('-translate-x-full');
            document.getElementById('mobile-menu-overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            document.getElementById('mobile-menu').classList.add('-translate-x-full');
            document.getElementById('mobile-menu-overlay').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
