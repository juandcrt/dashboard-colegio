@extends('layouts.app')

@section('title', 'Alumnos con Nivel ' . ($letra ?? '') . ' | I.E. Tungasuca')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300 p-4 sm:p-6 lg:p-8">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Barra de Navegación / Regresar -->
        <div class="flex items-center justify-between bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-xl transition-colors duration-300">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-xs font-bold transition-all px-2 py-1 rounded-xl">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </div>
                <span>Volver a Competencias</span>
            </a>

            <button id="themeToggleBtn" type="button" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i id="themeToggleIcon" class="fa-solid fa-moon text-indigo-500 dark:text-amber-400 text-sm"></i>
                <span class="hidden sm:inline">Modo Oscuro</span>
            </button>
        </div>

        @php
            $letra = $letra ?? 'B';
            $configuraciones = [
                'AD' => ['color' => 'emerald', 'desc' => 'Logro Destacado', 'icon' => 'fa-star'],
                'A'  => ['color' => 'blue', 'desc' => 'Logro Esperado', 'icon' => 'fa-circle-check'],
                'B'  => ['color' => 'amber', 'desc' => 'En Proceso', 'icon' => 'fa-spinner'],
                'C'  => ['color' => 'rose', 'desc' => 'En Inicio', 'icon' => 'fa-triangle-exclamation'],
            ];
            $config = $configuraciones[$letra] ?? ['color' => 'slate', 'desc' => 'Sin Especificar', 'icon' => 'fa-user'];
            $c = $config['color'];
        @endphp

        <!-- Banner Principal de la Vista -->
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl transition-all duration-300">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-{{$c}}-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex-1">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-{{$c}}-50 dark:bg-{{$c}}-500/10 border border-{{$c}}-200 dark:border-{{$c}}-500/20 text-{{$c}}-600 dark:text-{{$c}}-400 text-xs font-bold mb-3">
                    <i class="fa-solid fa-layer-group"></i> Filtro por Nivel de Logro
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    {{ $competenciaNombre ?? 'Competencia Seleccionada' }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium">
                    Relación de alumnos que han obtenido esta calificación en la evaluación actual.
                </p>
            </div>

            <!-- Insignia de la Nota -->
            <div class="relative z-10 flex flex-col items-center justify-center w-28 h-28 rounded-3xl bg-{{$c}}-50 dark:bg-{{$c}}-500/10 border-2 border-{{$c}}-200 dark:border-{{$c}}-500/30 shadow-lg shadow-{{$c}}-500/20 flex-shrink-0">
                <span class="text-4xl font-black text-{{$c}}-600 dark:text-{{$c}}-400">{{ $letra }}</span>
                <span class="text-[10px] font-extrabold text-{{$c}}-700 dark:text-{{$c}}-300 uppercase tracking-widest mt-1 text-center leading-tight">
                    {{ $config['desc'] }}
                </span>
            </div>
        </div>

        <!-- Lista de Estudiantes Filtrados -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-xl overflow-hidden transition-colors duration-300">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg flex items-center gap-2">
                    <i class="fa-solid fa-users text-{{$c}}-500"></i> Estudiantes Encontrados
                </h3>
                <span class="text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    {{ isset($estudiantes) ? $estudiantes->count() : 0 }} Alumnos
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($estudiantes ?? [] as $estudiante)
                    @php
                        $firstLetter = strtoupper(substr(trim($estudiante->name ?? 'A'), 0, 1));
                    @endphp
                    <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 border border-slate-300 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-300 font-black text-lg shadow-inner">
                                {{ $firstLetter }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $estudiante->name }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 font-medium">
                                    <i class="fa-solid {{ $config['icon'] }} text-{{$c}}-400"></i> Nivel Consolidado: {{ $letra }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('dashboard.student', $estudiante->id ?? 1) }}" 
                           class="inline-flex items-center justify-center gap-2 bg-white dark:bg-slate-800 hover:bg-{{$c}}-50 dark:hover:bg-{{$c}}-900/40 text-slate-700 dark:text-slate-300 hover:text-{{$c}}-600 dark:hover:text-{{$c}}-400 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                            <span>Ver Ficha Completa</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                @empty
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-3xl flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-slate-600 text-3xl shadow-inner">
                            <i class="fa-solid fa-ghost"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-white text-lg">Ningún estudiante en este nivel</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-sm mx-auto">No hay estudiantes que hayan obtenido la calificación <span class="font-bold text-{{$c}}-500">{{ $letra }}</span> en esta competencia.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeToggleIcon');
        
        function updateThemeUI(isDark) {
            if (themeIcon) {
                themeIcon.className = isDark ? 'fa-solid fa-sun text-amber-400 text-sm' : 'fa-solid fa-moon text-indigo-500 text-sm';
            }
        }

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                const isDarkMode = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
                updateThemeUI(isDarkMode);
            });
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            updateThemeUI(true);
        } else {
            updateThemeUI(false);
        }
    });
</script>
@endsection