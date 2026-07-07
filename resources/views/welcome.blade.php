<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SigaInv - Sistema de Gestión Administrativa</title>

    <link rel="icon" href="{{ asset('images/sigaweb-icon.ico') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#22c55e',
                            'green-dark': '#16a34a',
                            blue: '#3b82f6',
                            teal: '#14b8a6',
                        }
                    },
                    fontFamily: {
                        syne:    ['Plus Jakarta Sans', 'sans-serif'],
                        dm:      ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; }

        .hero-title-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.08;
        }

        .bg-mesh {
            background-color: #080f1e;
            background-image:
                radial-gradient(ellipse 80% 50% at 0% 0%,   rgba(34,197,94,0.12)  0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 100% 10%, rgba(59,130,246,0.10) 0%, transparent 60%),
                radial-gradient(ellipse 50% 60% at 50% 100%, rgba(20,184,166,0.06) 0%, transparent 60%);
        }

        .glass-nav {
            background: rgba(8, 15, 30, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #22c55e 0%, #10b981 40%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glow-green  { background: radial-gradient(circle, rgba(34,197,94,0.18)  0%, transparent 70%); }
        .glow-blue   { background: radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 70%); }

        .animate-float {
            animation: float 7s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-18px); }
        }

        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            transition: background 0.3s, border-color 0.3s, transform 0.3s;
        }
        .feature-card:hover {
            background: rgba(34,197,94,0.06);
            border-color: rgba(34,197,94,0.25);
            transform: translateY(-4px);
        }

        .module-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255,255,255,0.07);
            transition: all 0.3s;
        }
        .module-card:hover {
            border-color: rgba(34,197,94,0.3);
            box-shadow: 0 0 30px rgba(34,197,94,0.08);
        }

        .mockup-window {
            background: #0d1526;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05);
        }
        .mockup-bar {
            background: #111827;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dot { width: 10px; height: 10px; border-radius: 50%; }

        .stat-badge {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.2);
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #080f1e; }
        ::-webkit-scrollbar-thumb { background: rgba(34,197,94,0.3); border-radius: 4px; }
    </style>
</head>
<body class="antialiased bg-mesh text-slate-300 min-h-screen">
    <nav class="fixed top-0 w-full z-50 glass-nav h-18">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 h-full py-4 flex items-center justify-between">
            <div class="flex items-center">
                <img src="{{ asset('images/sigaweb-logo.svg') }}" alt="SigaWeb" class="h-12">
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/clientes/login') }}"
                   class="text-sm font-medium text-slate-300 hover:text-brand-green transition-colors">
                   Portal Clientes
                </a>
                <a href="{{ url('/pos') }}"
                   class="text-sm font-medium text-slate-300 hover:text-brand-green transition-colors">
                   <i class="fas fa-cash-register"></i> Punto de Venta
                </a>
                <a href="{{ url('/admin/login') }}"
                   class="group flex items-center gap-2 px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-slate-900 font-bold text-sm rounded-full transition-all duration-300 shadow-lg shadow-brand-green/20 active:scale-95">
                    Acceder
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </nav>

    <main class="relative pt-28 pb-16 px-6 lg:px-12 overflow-hidden">
        <div class="absolute top-0 left-0 w-[600px] h-[600px] glow-green opacity-40 pointer-events-none"></div>
        <div class="absolute top-20 right-0 w-[500px] h-[500px] glow-blue opacity-30 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12 lg:gap-20 min-h-[80vh]">
            <div class="flex-1 text-center lg:text-left z-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-brand-green/10 border border-brand-green/20 rounded-full text-brand-green text-sm font-bold mb-8 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 bg-brand-green rounded-full animate-pulse"></span>
                    Sistema de Gestión Administrativa
                </div>

                <h1 class="hero-title-text text-5xl lg:text-6xl text-white mb-6">
                    El control total<br>de su
                    <span class="hero-gradient"> Empresa<br>e Inventario</span>
                </h1>

                <p class="text-xl text-slate-400 max-w-xl lg:mx-0 mx-auto mb-10 leading-relaxed font-light">
                    Solución integral para gestionar inventarios, compras, ventas, cotizaciones, clientes, proveedores, caja y bancos. Diseñado para la eficiencia operativa.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-16">
                    <a href="{{ url('/admin/login') }}"
                       class="group flex items-center justify-center gap-2 px-8 py-3.5 bg-brand-green hover:bg-brand-green-dark text-slate-900 font-bold rounded-full transition-all duration-300 shadow-xl shadow-brand-green/20 active:scale-95">
                        Panel Admin
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <a href="{{ url('/clientes/login') }}"
                       class="flex items-center justify-center gap-2 px-8 py-3.5 bg-emerald-600/30 hover:bg-emerald-600/50 border border-emerald-500/30 text-white font-bold rounded-full transition-all duration-300 active:scale-95">
                        Portal Clientes
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([
                        ['Inventarios', 'Stock en tiempo real', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                        ['Finanzas',    'Caja, Bancos y Pagos',  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                        ['Comercial',   'Ventas y Compras',      'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['Contactos',   'Clientes y Proveedores', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
                    ] as [$title, $sub, $icon])
                    <div class="stat-badge rounded-xl p-3 flex flex-col gap-1">
                        <svg class="w-4 h-4 text-brand-green mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                        </svg>
                        <span class="text-sm font-bold text-white font-syne">{{ $title }}</span>
                        <span class="text-sm text-slate-400">{{ $sub }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 relative animate-float lg:block hidden z-10">
                <div class="absolute -inset-10 glow-green opacity-30 rounded-full pointer-events-none blur-3xl"></div>
                <div class="mockup-window">
                    <div class="mockup-bar">
                        <div class="dot bg-red-500/60"></div>
                        <div class="dot bg-yellow-500/60"></div>
                        <div class="dot bg-green-500/60"></div>
                        <div class="ml-4 flex-1 bg-slate-800 rounded-md h-5 flex items-center px-3">
                            <span class="text-[10px] text-slate-500">sigainve.test/admin</span>
                        </div>
                    </div>
                    <div class="flex h-[380px]">
                        <div class="w-44 bg-slate-900/80 border-r border-white/5 p-4 flex flex-col gap-1 shrink-0">
                            <div class="text-[9px] uppercase tracking-widest text-slate-600 mb-2 px-2">Administración</div>
                            @foreach([
                                ['Escritorio',   '#22c55e', true],
                                ['Usuarios',     '#94a3b8', false],
                                ['Clientes',     '#94a3b8', false],
                                ['Proveedores',  '#94a3b8', false],
                                ['Inventario',   '#94a3b8', false],
                                ['Compras',      '#94a3b8', false],
                                ['Ventas',       '#94a3b8', false],
                            ] as [$label, $color, $active])
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg {{ $active ? 'bg-brand-green/10' : '' }}">
                                <div class="w-1.5 h-1.5 rounded-full" style="background:{{ $color }}"></div>
                                <span class="text-[11px] {{ $active ? 'text-brand-green font-semibold' : 'text-slate-500' }}">{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="flex-1 p-5 overflow-hidden">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs font-syne font-bold text-white">Escritorio</span>
                                <div class="w-6 h-6 bg-brand-green rounded-full flex items-center justify-center">
                                    <span class="text-[8px] font-bold text-slate-900">JO</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                @foreach([
                                    ['Clientes', '248', '+12%', '#22c55e'],
                                    ['Productos', '1,834', '+5%',  '#3b82f6'],
                                    ['Ventas',   '$4.2M', '+18%', '#14b8a6'],
                                ] as [$k, $v, $pct, $c])
                                <div class="rounded-xl p-3 border border-white/5" style="background:rgba(255,255,255,0.03)">
                                    <div class="text-[9px] text-slate-500 mb-1">{{ $k }}</div>
                                    <div class="text-sm font-syne font-bold text-white">{{ $v }}</div>
                                    <div class="text-[9px] font-bold mt-0.5" style="color:{{ $c }}">{{ $pct }}</div>
                                </div>
                                @endforeach
                            </div>
                            <div class="rounded-xl border border-white/5 p-3 mb-3" style="background:rgba(255,255,255,0.02)">
                                <div class="text-[9px] text-slate-500 mb-2">Ventas últimos 7 días</div>
                                <div class="flex items-end gap-1 h-10">
                                    @foreach([40,65,45,80,55,90,70] as $h)
                                    <div class="flex-1 rounded-sm" style="height:{{ $h }}%; background: linear-gradient(to top, #22c55e, #10b981); opacity:0.7"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                @foreach(['Compra #1042','Venta #2891','Cliente nuevo'] as $row)
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-brand-green/60"></div>
                                    <div class="text-[10px] text-slate-500">{{ $row }}</div>
                                    <div class="ml-auto h-2 bg-slate-800 rounded w-12"></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section class="py-24 px-6 lg:px-12">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-up">
                <span class="text-sm uppercase tracking-widest text-brand-green font-bold">Por qué SigaInve</span>
                <h2 class="text-4xl lg:text-5xl font-syne font-bold text-white mt-3 mb-4">Diseñado para su negocio</h2>
                <p class="text-base text-slate-400 max-w-xl mx-auto">Una plataforma completa que centraliza toda la operación de su empresa en un solo lugar.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach([
                    ['Tiempo Real',       'Información actualizada al instante. Tome decisiones con datos precisos en el momento que los necesita.',       'M13 10V3L4 14h7v7l9-11h-7z',       '#22c55e'],
                    ['Fácil de Usar',     'Interfaz intuitiva diseñada para que su equipo la adopte rápidamente sin necesidad de capacitación extensa.',   'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', '#3b82f6'],
                    ['Seguro y Confiable','Control de acceso por roles. Cada usuario ve y hace solo lo que le corresponde. Sus datos siempre protegidos.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', '#14b8a6'],
                ] as [$title, $desc, $icon, $color])
                <div class="feature-card rounded-2xl p-8 fade-up">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background: {{ $color }}20; border: 1px solid {{ $color }}30">
                        <svg class="w-6 h-6" style="color:{{ $color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-syne font-bold text-white mb-3">{{ $title }}</h3>
                    <p class="text-slate-400 text-base leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 px-6 lg:px-12 relative">
        <div class="absolute inset-0 glow-green opacity-10 pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center mb-16 fade-up">
                <span class="text-sm uppercase tracking-widest text-brand-green font-bold">Módulos</span>
                <h2 class="text-4xl lg:text-5xl font-syne font-bold text-white mt-3 mb-4">Todo lo que necesita</h2>
                <p class="text-base text-slate-400 max-w-xl mx-auto">Módulos integrados que trabajan juntos para dar visibilidad total a su operación.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['📦', 'Inventario',    'Control de stock, entradas y salidas'],
                    ['🛒', 'Compras',       'Órdenes, proveedores y recepción'],
                    ['💼', 'Ventas',        'Cotizaciones, facturas y despachos'],
                    ['👥', 'Contactos',     'Clientes y proveedores centralizados'],
                    ['💰', 'Caja',          'Movimientos diarios y cierres'],
                    ['🏦', 'Bancos',        'Cuentas, transferencias y conciliación'],
                    ['📊', 'Reportes',      'Indicadores y análisis en tiempo real'],
                    ['⚙️', 'Configuración', 'Usuarios, roles y parámetros'],
                ] as [$emoji, $title, $desc])
                <div class="module-card rounded-2xl p-6 fade-up">
                    <span class="text-3xl mb-4 block">{{ $emoji }}</span>
                    <h4 class="font-syne font-bold text-white text-base mb-2">{{ $title }}</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 px-6 lg:px-12">
        <div class="max-w-3xl mx-auto text-center fade-up">
            <div class="bg-gradient-to-br from-brand-green/10 to-brand-blue/10 border border-brand-green/20 rounded-3xl p-12">
                <h2 class="text-4xl font-syne font-bold text-white mb-4">¿Listo para comenzar?</h2>
                <p class="text-lg text-slate-400 mb-8">Acceda ahora y tome el control total de su empresa.</p>
                <a href="{{ url('/admin/login') }}"
                   class="group inline-flex items-center gap-2 px-10 py-4 bg-brand-green hover:bg-brand-green-dark text-slate-900 font-bold rounded-full transition-all duration-300 shadow-xl shadow-brand-green/20 active:scale-95 text-lg">
                    Ingresar al sistema
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/5 py-14 px-6 lg:px-12">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center">
                <img src="{{ asset('images/sigaweb-icon.svg') }}" alt="SigaWeb" class="h-10">
            </div>
            <p class="text-sm text-slate-400">
                SIGAInv es desarrollado por SIGA Software © {{ date('Y') }}. Todos los derechos reservados. {{ config('app.version') }}
            </p>
            <a href="mailto:joseforozco@gmail.com" class="text-sm text-slate-400 hover:text-brand-green transition-colors font-medium">
                joseforozco@gmail.com
            </a>
        </div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((e, i) => {
                if (e.isIntersecting) {
                    setTimeout(() => e.target.classList.add('visible'), i * 80);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    </script>
</body>
</html>
