@extends('layouts.app')

@section('title', 'Dashboard de Aula - ' . ($currentClassroom->name ?? 'Salón') . ' | I.E. Tungasuca')

@section('content')
<div class="flex flex-col lg:flex-row min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <aside class="w-full lg:w-72 bg-white/90 dark:bg-slate-900/90 border-r border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-6 flex-shrink-0 backdrop-blur-2xl">
        <div class="flex items-center gap-3 px-2">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-sm tracking-wide uppercase">Cursos</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Áreas Curriculares</p>
            </div>
        </div>

        <nav class="space-y-2">
            @php 
                $selectedCourseId = request('course_id', 'all');
                $isAllActive = empty($selectedCourseId) || $selectedCourseId === 'all'; 
            @endphp
            <a href="{{ route('dashboard.classroom', ['id' => $currentClassroom->id ?? 1, 'course_id' => 'all']) }}" 
               class="flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all duration-200 border {{ $isAllActive ? 'bg-gradient-to-r from-indigo-600 to-violet-600 border-indigo-400/50 text-white shadow-lg shadow-indigo-500/30 scale-[1.02]' : 'bg-slate-100/60 dark:bg-slate-800/40 border-slate-200/60 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:bg-slate-200/80 dark:hover:bg-slate-700/60' }}">
                <div class="flex items-center gap-3 overflow-hidden">
                    <i class="fa-solid fa-border-all text-sm"></i>
                    <span class="truncate">Todos los Cursos</span>
                </div>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <div class="pt-2 pb-1 px-2 border-t border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Asignaturas Específicas</span>
            </div>

            @forelse($courses as $course)
                @php 
                    $isActive = isset($currentCourse) && $currentCourse->id === $course->id && !$isAllActive;
                @endphp
                <a href="{{ route('dashboard.classroom', ['id' => $currentClassroom->id ?? 1, 'course_id' => $course->id]) }}" 
                   class="flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all duration-200 border {{ $isActive ? 'bg-gradient-to-r from-indigo-600 to-violet-600 border-indigo-400/50 text-white shadow-lg shadow-indigo-500/30 scale-[1.02]' : 'bg-slate-100/60 dark:bg-slate-800/40 border-slate-200/60 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:bg-slate-200/80 dark:hover:bg-slate-700/60' }}">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <i class="fa-solid {{ $isActive ? 'fa-folder-open' : 'fa-folder' }} text-sm"></i>
                        <span class="truncate">{{ $course->name }}</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </a>
            @empty
                <p class="text-xs text-slate-500 px-2 py-4">No hay cursos registrados.</p>
            @endforelse
        </nav>
    </aside>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-8 overflow-x-hidden">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-slate-200 dark:border-slate-700/60 p-4 rounded-3xl shadow-xl dark:shadow-2xl transition-colors duration-300">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-2 hidden sm:inline-block">Aulas:</span>
                @foreach($classrooms as $classroom)
                    <a href="{{ route('dashboard.classroom', ['id' => $classroom->id, 'course_id' => request('course_id', 'all')]) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl font-semibold text-xs transition-all duration-300 border {{ isset($currentClassroom) && $currentClassroom->id === $classroom->id ? 'bg-gradient-to-r from-indigo-600 to-violet-600 border-indigo-400/50 text-white shadow-lg shadow-indigo-500/30 scale-105' : 'bg-slate-100 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700/80' }}">
                        <i class="fa-solid fa-graduation-cap text-xs"></i> 
                        {{ $classroom->name }}
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-extrabold {{ isset($currentClassroom) && $currentClassroom->id === $classroom->id ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                            {{ $classroom->students_count ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="flex items-center justify-between lg:justify-end gap-4 pl-0 lg:pl-6 border-t lg:border-t-0 border-slate-200 dark:border-slate-800 pt-3 lg:pt-0">
                <button id="themeToggleBtn" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200" title="Cambiar tema">
                    <i id="themeToggleIcon" class="fa-solid fa-moon text-indigo-500 dark:text-amber-400 text-sm"></i>
                    <span id="themeToggleText" class="hidden sm:inline">Modo Oscuro</span>
                </button>
            </div>
        </div>

        @if($currentClassroom)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 relative overflow-hidden bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-6 sm:p-8 flex flex-col justify-between shadow-xl dark:shadow-2xl transition-colors duration-300">
                    <div class="absolute -top-20 -right-20 w-72 h-72 bg-indigo-500/10 dark:bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-violet-500/10 dark:bg-violet-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-400/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold mb-3 shadow-inner">
                            <i class="fa-solid fa-school text-indigo-500 dark:text-indigo-400"></i> I.E. Tungasuca - Comas
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-2">
                            {{ $currentClassroom->name }} — {{ ($currentCourse && !$isAllActive) ? $currentCourse->name : 'Visión General' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-xl font-medium leading-relaxed">
                            {{ ($currentCourse && !$isAllActive) ? 'Análisis detallado sobre el rendimiento por competencias para el área de ' . $currentCourse->name : 'Análisis consolidado en tiempo real sobre el rendimiento por competencias de todas las asignaturas.' }}
                        </p>
                    </div>

                    <div class="relative z-10 pt-6 mt-6 border-t border-slate-200 dark:border-slate-800/80 flex flex-wrap gap-8 items-center justify-between">
                        <div class="flex flex-wrap gap-8 items-center">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Estado de Aula</span>
                                <span class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 rounded-xl border border-emerald-200 dark:border-emerald-500/30 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-ping"></span> Activo
                                </span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Total Alumnos</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $students->count() }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Matriculados</span></span>
                            </div>
                        </div>

                        <a href="#tabla-alumnos" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-600 hover:text-white border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-all duration-300 shadow-sm group">
                            <span>Ver Estudiantes</span>
                            <i class="fa-solid fa-arrow-down text-xs group-hover:translate-y-0.5 transition-transform duration-200"></i>
                        </a>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                @if($currentCourse && !$isAllActive && isset($courseStats['competencias']) && count($courseStats['competencias']) > 0)
                    <div class="flex-1 group bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-5 backdrop-blur-2xl shadow-xl transition-all duration-300 flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nivel de Logro por Competencia</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ $currentCourse->name }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>
                        <div class="space-y-4 overflow-y-auto max-h-[300px] pr-1">
                            @foreach($courseStats['competencias'] as $index => $competencia)
                                @if($index < 3) 
                                @php
                                    $pctComp = round($competencia['porcentaje_logro'] ?? 0);
                                    $nivelComp = $competencia['nivel'] ?? 'B';
                                    
                                    $compClasses = match($nivelComp) {
                                        'AD' => ['text' => 'text-emerald-600 dark:text-emerald-400', 'bar' => 'bg-emerald-500'],
                                        'A'  => ['text' => 'text-blue-600 dark:text-blue-400', 'bar' => 'bg-blue-500'],
                                        'B'  => ['text' => 'text-amber-600 dark:text-amber-400', 'bar' => 'bg-amber-500'],
                                        default => ['text' => 'text-rose-600 dark:text-rose-400', 'bar' => 'bg-rose-500'],
                                    };

                                    // ENVIAMOS EL ID DEL CURSO PARA QUE LA FICHA FUNCIONE CORRECTAMENTE
                                    $urlBaseParams = [
                                        'competencia_id' => $competencia['id'] ?? $index, 
                                        'competencia_nombre' => $competencia['nombre'],
                                        'course_name' => $currentCourse->name,
                                        'course_id' => $currentCourse->id,
                                        'classroom_id' => $currentClassroom->id ?? 1
                                    ];
                                @endphp
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate pr-2" title="{{ $competencia['nombre'] }}">
                                            {{ $competencia['nombre'] }}
                                        </span>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <span class="text-xs font-black {{ $compClasses['text'] }}">{{ $pctComp }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden mb-3">
                                        <div class="h-full rounded-full {{ $compClasses['bar'] }}" style="width: {{ min($pctComp, 100) }}%"></div>
                                    </div>
                                    
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <a href="{{ route('vistasnotas.index', array_merge($urlBaseParams, ['letra' => 'AD'])) }}" class="py-1 px-1 rounded-md text-[9px] font-bold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-colors flex justify-between items-center group">
                                            <span>AD</span>
                                            <span class="bg-emerald-200/50 dark:bg-emerald-900/50 px-1 rounded text-[8px] group-hover:bg-white/20">{{ $competencia['distribucion']['AD'] ?? 0 }}</span>
                                        </a>
                                        <a href="{{ route('vistasnotas.index', array_merge($urlBaseParams, ['letra' => 'A'])) }}" class="py-1 px-1 rounded-md text-[9px] font-bold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 hover:bg-blue-500 hover:text-white transition-colors flex justify-between items-center group">
                                            <span>A</span>
                                            <span class="bg-blue-200/50 dark:bg-blue-900/50 px-1 rounded text-[8px] group-hover:bg-white/20">{{ $competencia['distribucion']['A'] ?? 0 }}</span>
                                        </a>
                                        <a href="{{ route('vistasnotas.index', array_merge($urlBaseParams, ['letra' => 'B'])) }}" class="py-1 px-1 rounded-md text-[9px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-colors flex justify-between items-center group">
                                            <span>B</span>
                                            <span class="bg-amber-200/50 dark:bg-amber-900/50 px-1 rounded text-[8px] group-hover:bg-white/20">{{ $competencia['distribucion']['B'] ?? 0 }}</span>
                                        </a>
                                        <a href="{{ route('vistasnotas.index', array_merge($urlBaseParams, ['letra' => 'C'])) }}" class="py-1 px-1 rounded-md text-[9px] font-bold bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 hover:bg-rose-500 hover:text-white transition-colors flex justify-between items-center group">
                                            <span>C</span>
                                            <span class="bg-rose-200/50 dark:bg-rose-900/50 px-1 rounded text-[8px] group-hover:bg-white/20">{{ $competencia['distribucion']['C'] ?? 0 }}</span>
                                        </a>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex-1 group bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-500/50 rounded-3xl p-5 backdrop-blur-2xl flex items-center justify-between shadow-xl transition-all duration-300 hover:translate-y-[-2px]">
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Promedio General</p>
                            @php
                                $notaVigesimal = $courseStats['promedio'] ?? $promedioGeneral;
                                $porcentajePromedio = round(($notaVigesimal / 20) * 100, 1);
                            @endphp
                            <div class="flex items-center gap-2 mt-1">
                                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ $porcentajePromedio }}%</h4>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">Rendimiento medio relativo del aula</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-lg shadow-indigo-500/10 flex-shrink-0">
                            <i class="fa-solid fa-percent"></i>
                        </div>
                    </div>
                @endif
                </div>
            </div>

            <!-- PANEL DE SEGUIMIENTO DINÁMICO E HISTÓRICO -->
            <div class="bg-gradient-to-br from-indigo-50 to-white dark:from-slate-900 dark:to-slate-800 border border-indigo-100 dark:border-slate-700 rounded-3xl p-6 sm:p-8 shadow-xl dark:shadow-2xl backdrop-blur-2xl transition-colors duration-300">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-lg">
                        <i class="fa-solid fa-ranking-star"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-lg">Panel de Seguimiento y Progreso</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Estudiantes que han variado su nivel de logro en la última evaluación</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white/60 dark:bg-slate-900/60 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-900/50">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 uppercase tracking-wide">
                                <i class="fa-solid fa-arrow-trend-up"></i> Mejoraron su rendimiento
                            </h4>
                            <span class="text-[9px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 rounded-md border border-emerald-100 dark:border-emerald-800/30 uppercase tracking-widest shadow-sm">
                                Agosto <i class="fa-solid fa-arrow-right mx-0.5"></i> Setiembre
                            </span>
                        </div>
                        <ul class="space-y-2">
                            @forelse($mejoraron ?? [] as $alumno)
                            <li class="flex justify-between items-center bg-white dark:bg-slate-800 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50 shadow-sm text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-semibold truncate"><i class="fa-solid fa-user text-slate-400 text-xs mr-2"></i> {{ $alumno['nombre'] }}</span> 
                                <span class="text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-1 rounded-lg whitespace-nowrap">{{ $alumno['cambio'] }}</span>
                            </li>
                            @empty
                            <li class="text-xs text-slate-500">Ningún estudiante registró subidas este mes.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="bg-white/60 dark:bg-slate-900/60 p-4 rounded-2xl border border-rose-200 dark:border-rose-900/50">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2 uppercase tracking-wide">
                                <i class="fa-solid fa-arrow-trend-down"></i> Bajaron su rendimiento
                            </h4>
                            <span class="text-[9px] font-black text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2.5 py-1 rounded-md border border-rose-100 dark:border-rose-800/30 uppercase tracking-widest shadow-sm">
                                Agosto <i class="fa-solid fa-arrow-right mx-0.5"></i> Setiembre
                            </span>
                        </div>
                        <ul class="space-y-2">
                            @forelse($bajaron ?? [] as $alumno)
                            <li class="flex justify-between items-center bg-white dark:bg-slate-800 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50 shadow-sm text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-semibold truncate"><i class="fa-solid fa-user text-slate-400 text-xs mr-2"></i> {{ $alumno['nombre'] }}</span> 
                                <span class="text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 px-2 py-1 rounded-lg whitespace-nowrap">{{ $alumno['cambio'] }}</span>
                            </li>
                            @empty
                            <li class="text-xs text-slate-500">Ningún estudiante registró bajadas este mes.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- LOS 4 GRÁFICOS ESCALABLES (AD, A, B, C) -->
            <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-6 sm:p-8 shadow-xl dark:shadow-2xl backdrop-blur-2xl transition-colors duration-300">
                <div class="mb-6">
                    <h3 class="font-black text-slate-900 dark:text-white text-xl flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        Evolución Histórica por Niveles de Logro
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Gráficos que representan la cantidad de estudiantes que alcanzaron <strong class="text-indigo-500">AD, A, B y C</strong> durante los meses del año. Da clic en los puntos para ver sus nombres.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-inner group">
                        <h4 class="text-xs font-bold text-center text-slate-700 dark:text-slate-300 mb-4 group-hover:text-emerald-500 transition-colors">Nivel AD (Destacado)</h4>
                        <div class="relative h-48 w-full cursor-pointer"><canvas id="chartAD"></canvas></div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-inner group">
                        <h4 class="text-xs font-bold text-center text-slate-700 dark:text-slate-300 mb-4 group-hover:text-blue-500 transition-colors">Nivel A (Esperado)</h4>
                        <div class="relative h-48 w-full cursor-pointer"><canvas id="chartA"></canvas></div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-inner group">
                        <h4 class="text-xs font-bold text-center text-slate-700 dark:text-slate-300 mb-4 group-hover:text-amber-500 transition-colors">Nivel B (En Proceso)</h4>
                        <div class="relative h-48 w-full cursor-pointer"><canvas id="chartB"></canvas></div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-inner group">
                        <h4 class="text-xs font-bold text-center text-slate-700 dark:text-slate-300 mb-4 group-hover:text-rose-500 transition-colors">Nivel C (En Inicio)</h4>
                        <div class="relative h-48 w-full cursor-pointer"><canvas id="chartC"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Estudiantes (Datos Sincronizados) -->
            <div id="tabla-alumnos" class="bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl overflow-hidden shadow-xl dark:shadow-2xl backdrop-blur-2xl transition-colors duration-300 scroll-mt-6">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-xl flex items-center gap-2">
                            <i class="fa-solid fa-user-graduate text-indigo-600 dark:text-indigo-400"></i> Estudiantes Matriculados
                        </h3>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" onclick="mostrarResultados()" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center gap-2 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-play text-xs"></i>
                            <span>Mostrar Resultados</span>
                        </button>

                        <div class="relative">
                            <input type="text" id="studentSearch" placeholder="Buscar alumno..." class="bg-slate-100 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 pl-3 pr-3 py-2 rounded-xl focus:outline-none focus:border-indigo-500 transition shadow-sm w-44 sm:w-52">
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-slate-800/60" id="studentsList">
                    @forelse($students as $student)
                        @php
                            $firstLetter = strtoupper(substr(trim($student->name ?? 'A'), 0, 1));
                        @endphp
                        <div class="student-item p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200"
                             data-name="{{ mb_strtolower($student->name ?? '', 'UTF-8') }}" 
                             data-id="{{ $student->id }}">
                            
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500/20 via-slate-100 to-violet-500/20 dark:from-indigo-600/30 dark:via-slate-800 dark:to-violet-600/30 border border-indigo-300 dark:border-indigo-500/40 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-black text-base shadow-inner flex-shrink-0">
                                    {{ $firstLetter }}
                                </div>
                                <div>
                                    <h4 class="student-name font-bold text-slate-800 dark:text-slate-100 text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $student->name }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 font-medium">
                                        <i class="fa-regular fa-envelope text-indigo-500 dark:text-indigo-400/70"></i> {{ $student->email ?? 'Sin correo asignado' }}
                                    </p>
                                </div>
                            </div>

                            <div class="student-results-container hidden flex-col items-end sm:items-center text-xs font-semibold">
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl border bg-{{ $student->color }}-50 dark:bg-{{ $student->color }}-500/10 text-{{ $student->color }}-600 dark:text-{{ $student->color }}-400 border-{{ $student->color }}-200 dark:border-{{ $student->color }}-500/20 text-sm font-black shadow-sm transition-all">
                                    {{ $student->letra }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold mt-1">Logro Promedio: {{ $student->promedio_pct }}%</span>
                            </div>

                            <div class="flex items-center gap-3 self-end sm:self-auto">
                                <a href="{{ route('dashboard.student', $student->id) }}?course_id={{ request('course_id', 'all') }}" 
                                   class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-indigo-500/40 active:scale-95">
                                    <span>Ver Ficha</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-500 dark:text-slate-400">
                            <h4 class="font-bold text-slate-800 dark:text-white text-base">Sin Alumnos Inscritos</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </main>
</div>

<!-- MODAL MEJORADO (Muestra la Competencia Específica) -->
<div id="chartModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700 transform scale-95 transition-all flex flex-col max-h-[90vh]">
        
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight" id="modalTitle">Reporte de Rendimiento</h3>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider" id="modalSubtitle">Detalle de estudiantes</p>
            </div>
            <button onclick="cerrarModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="col-span-1 bg-slate-100 dark:bg-slate-800 p-4 rounded-2xl text-center border border-slate-200 dark:border-slate-700/50">
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase mb-1">Nivel Analizado</p>
                <p id="modalLevel" class="text-3xl font-black text-indigo-600 dark:text-indigo-400 leading-none">A</p>
            </div>
            <div class="col-span-2 bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-2xl flex items-center justify-between border border-indigo-100 dark:border-indigo-800/30">
                <div>
                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Total en este margen</p>
                    <p class="text-[10px] text-indigo-400 dark:text-indigo-500 mt-0.5">Alumnos correspondientes</p>
                </div>
                <span id="modalStudentCount" class="font-black text-2xl text-indigo-600 dark:text-indigo-300">0</span>
            </div>
        </div>

        <div class="overflow-hidden border border-slate-200 dark:border-slate-700/60 rounded-2xl flex-1 flex flex-col min-h-0">
            <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-3 flex items-center justify-between border-b border-slate-200 dark:border-slate-700/60">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider w-1/2">Estudiante</span>
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center w-1/4">Nota (Letra)</span>
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-1/4">Logro (%)</span>
            </div>
            <div class="overflow-y-auto max-h-72 p-2 space-y-1" id="modalStudentList">
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 text-right">
            <button onclick="cerrarModal()" class="bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow-lg shadow-slate-900/20 dark:shadow-indigo-600/20">
                Entendido
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        const searchInput = document.getElementById('studentSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                document.querySelectorAll('.student-item').forEach(item => {
                    const name = item.getAttribute('data-name');
                    if (name.includes(query)) item.classList.remove('hidden');
                    else item.classList.add('hidden');
                });
            });
        }

        const tendenciaGlobal = {!! json_encode($tendenciaGlobal ?? ['AD'=>[], 'A'=>[], 'B'=>[], 'C'=>[]]) !!};
        const historialAlumnos = {!! json_encode($historialAlumnos ?? []) !!};

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        const yAxisCountConfig = {
            min: 0, 
            suggestedMax: 15,
            grid: { color: gridColor },
            ticks: { color: textColor, stepSize: 2, font: { size: 10, weight: 'bold' } }
        };

        const onClickChart = (e, elements, chart) => {
            if (elements.length > 0) {
                const dataIndex = elements[0].index;
                const mes = chart.data.labels[dataIndex];
                const studentCount = chart.data.datasets[0].data[dataIndex]; 
                const labelName = chart.data.datasets[0].label; 

                let letra = 'C';
                if (labelName.includes('AD')) letra = 'AD';
                else if (labelName.includes('A')) letra = 'A';
                else if (labelName.includes('B')) letra = 'B';
                
                document.getElementById('modalTitle').innerText = `Mes: ${mes}`;
                document.getElementById('modalSubtitle').innerText = `Alumnos que alcanzaron el ${labelName}`;
                document.getElementById('modalLevel').innerText = letra;
                document.getElementById('modalStudentCount').innerText = studentCount;
                
                const listaContainer = document.getElementById('modalStudentList');
                listaContainer.innerHTML = ''; 

                if (studentCount === 0) {
                    listaContainer.innerHTML = `<div class="p-6 text-center text-slate-500 dark:text-slate-400 text-sm font-semibold">No hay alumnos registrados en este nivel durante este mes.</div>`;
                } else {
                    const levelConfig = {
                        'AD': { color: 'emerald', text: 'Logro Destacado' },
                        'A':  { color: 'blue', text: 'Logro Esperado' },
                        'B':  { color: 'amber', text: 'En Proceso' },
                        'C':  { color: 'rose', text: 'En Inicio' }
                    };
                    const conf = levelConfig[letra];

                    Object.keys(historialAlumnos).forEach(name => {
                        const history = historialAlumnos[name][dataIndex]; 
                        
                        if (history.avg_letra === letra) { 
                            
                            const compText = (letra === 'C' || letra === 'B') 
                                ? `Requiere apoyo en: ${history.best_comp_name}` 
                                : `Destacó en: ${history.best_comp_name}`;

                            listaContainer.innerHTML += `
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl border border-slate-100 dark:border-slate-700 transition-colors">
                                    <div class="w-1/2 pr-2">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">${name}</h4>
                                        <p class="text-[9px] text-slate-500 dark:text-slate-400 font-bold mt-1 truncate">
                                            <i class="fa-solid fa-star text-amber-400 mr-1"></i> ${compText}
                                        </p>
                                    </div>
                                    <div class="w-1/4 text-center border-l border-r border-slate-100 dark:border-slate-700">
                                        <span class="inline-block px-3 py-1 rounded-lg text-xs font-black bg-${conf.color}-50 text-${conf.color}-600 dark:bg-${conf.color}-500/10 dark:text-${conf.color}-400 border border-${conf.color}-200 dark:border-${conf.color}-500/20 shadow-sm">
                                            ${letra}
                                        </span>
                                    </div>
                                    <div class="w-1/4 pl-3">
                                        <div class="flex justify-between items-end mb-1 text-[10px] font-black text-slate-600 dark:text-slate-300">
                                            <span>Logro</span>
                                            <span class="text-${conf.color}-500">${history.avg_pct}%</span>
                                        </div>
                                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-${conf.color}-500 h-full rounded-full transition-all" style="width: ${history.avg_pct}%"></div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                }

                const modal = document.getElementById('chartModal');
                const modalDiv = document.querySelector('#chartModal > div');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modalDiv.classList.remove('scale-95');
                    modalDiv.classList.add('scale-100');
                }, 10);
            }
        };

        const createChart = (id, data, colorHex, labelName) => {
            const canvas = document.getElementById(id);
            if(!canvas) return;
            const ctx = canvas.getContext('2d');
            
            let gradient = ctx.createLinearGradient(0, 0, 0, 180);
            gradient.addColorStop(0, colorHex + '66'); 
            gradient.addColorStop(1, colorHex + '00'); 

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set'],
                    datasets: [{
                        label: labelName, data: data, borderColor: colorHex, backgroundColor: gradient,
                        fill: true, borderWidth: 2.5, tension: 0.4, pointRadius: 5, pointHoverRadius: 8,
                        pointBackgroundColor: colorHex, pointBorderColor: '#fff', pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#fff', pointHoverBorderColor: colorHex
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, onClick: onClickChart,
                    interaction: { mode: 'index', intersect: true },
                    plugins: { 
                        legend: { display: false }, 
                        tooltip: { 
                            backgroundColor: isDark ? '#0f172a' : '#fff',
                            titleColor: isDark ? '#fff' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#64748b',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1, padding: 10,
                            callbacks: {
                                label: (ctx) => {
                                    return ` Alumnos en ${labelName}: ${ctx.raw}`;
                                }
                            }
                        } 
                    },
                    scales: { 
                        y: yAxisCountConfig,
                        x: { grid: { color: gridColor }, ticks: { color: textColor, font: {size: 10} } }
                    }
                }
            });
        };

        createChart('chartAD', tendenciaGlobal['AD'] || [0,0,0,0,0,0,0], '#10b981', 'Nivel AD'); 
        createChart('chartA',  tendenciaGlobal['A']  || [0,0,0,0,0,0,0], '#3b82f6', 'Nivel A'); 
        createChart('chartB',  tendenciaGlobal['B']  || [0,0,0,0,0,0,0], '#f59e0b', 'Nivel B'); 
        createChart('chartC',  tendenciaGlobal['C']  || [0,0,0,0,0,0,0], '#ef4444', 'Nivel C'); 
    });

    function cerrarModal() {
        const modalDiv = document.querySelector('#chartModal > div');
        modalDiv.classList.remove('scale-100');
        modalDiv.classList.add('scale-95');
        setTimeout(() => document.getElementById('chartModal').classList.add('hidden'), 150);
    }

    function mostrarResultados() {
        const containers = document.querySelectorAll('.student-results-container');
        containers.forEach(container => {
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                container.classList.add('flex');
            } else {
                container.classList.add('hidden');
                container.classList.remove('flex');
            }
        });
    }
</script>
@endsection