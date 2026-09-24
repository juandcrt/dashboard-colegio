@extends('layouts.app')

@section('title', 'Competencias - ' . ($student->name ?? 'Estudiante'))

@section('content')
<!-- ESTILOS EXCLUSIVOS PARA IMPRESIÓN FORMAL EN HOJA A4 -->
<style>
    @media print {
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        
        .no-print, header, nav, button, .print\:hidden, #themeToggleBtn {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
    }

    @media screen {
        .print-only {
            display: none !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto space-y-8 p-4 sm:p-6 lg:p-8 transition-colors duration-300">

    <!-- ========================================== -->
    <!-- 1. VISTA INTERACTIVA WEB (NO IMPRESIÓN)    -->
    <!-- ========================================== -->
    <div class="no-print space-y-8">
        <!-- Topbar: Botón Volver + Acciones + Toggle Tema + Usuario -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl shadow-xl transition-colors duration-300">
            <a href="{{ $student->classroom_id ? route('dashboard.classroom', ['id' => $student->classroom_id, 'course_id' => request('course_id', 'all')]) : route('dashboard.classroom') }}" 
               class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-xl p-1">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 group-hover:bg-indigo-600 border border-slate-200 dark:border-slate-700 group-hover:border-indigo-500 flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:text-white transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </div>
                <span>Volver al Dashboard del Aula</span>
            </a>

            <div class="flex items-center justify-between sm:justify-end gap-3 flex-wrap">
                <!-- Botón de Impresión -->
                <button onclick="window.print()" type="button" aria-label="Imprimir Informe" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 active:scale-95 transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Imprimir Informe Formal</span>
                </button>

                <!-- Toggle de Tema -->
                <button id="themeToggleBtn" type="button" aria-label="Cambiar Tema" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all duration-200 cursor-pointer" title="Cambiar tema">
                    <i id="themeToggleIcon" class="fa-solid fa-moon text-indigo-500 dark:text-amber-400 text-sm"></i>
                    <span id="themeToggleText" class="hidden sm:inline">Modo Oscuro</span>
                </button>

                @auth
                    <div class="flex items-center gap-3 border-l border-slate-200 dark:border-slate-800 pl-3">
                        <div class="relative">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white font-bold text-xs shadow-md shadow-indigo-500/20 ring-2 ring-indigo-400/40">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 border-2 border-white dark:border-slate-900 rounded-full"></span>
                        </div>
                        <div class="hidden md:flex flex-col">
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-200 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 capitalize font-semibold">{{ auth()->user()->role ?? 'Docente' }}</span>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        <!-- CÁLCULO DE COLECCIONES (Convertidas a Porcentajes y Letras) -->
        @php
            $selectedCourseFilter = request('course_id', 'all');
            $allCompetencies = collect($results)->flatten();
            $allScores = $allCompetencies->pluck('score')->filter(fn($val) => !is_null($val));
            
            // Calculo de porcentaje general
            $avgScore = $allScores->isNotEmpty() ? min(20.0, $allScores->avg()) : 0;
            $porcentajeGeneral = round(($avgScore / 20) * 100, 1);
            
            // Convertir promedio a letra general
            if($porcentajeGeneral >= 90) { $nivelGeneral = 'AD'; $nivelClase = 'emerald'; }
            elseif($porcentajeGeneral >= 70) { $nivelGeneral = 'A'; $nivelClase = 'blue'; }
            elseif($porcentajeGeneral >= 55) { $nivelGeneral = 'B'; $nivelClase = 'amber'; }
            else { $nivelGeneral = 'C'; $nivelClase = 'rose'; }

            $masteredCount = $allCompetencies->filter(fn($item) => ($item->score ?? 0) >= 11)->count();
        @endphp

        <!-- Header Perfil del Estudiante + KPIs Globales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Banner Principal del Alumno -->
            <div class="lg:col-span-2 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 flex flex-col justify-between shadow-xl transition-colors duration-300">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 dark:bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-violet-500/10 dark:bg-violet-600/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 border border-indigo-400/30 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-indigo-600/20">
                            {{ strtoupper(substr($student->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-indigo-600 border-2 border-white dark:border-slate-900 rounded-full flex items-center justify-center text-[10px] text-white shadow">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold mb-2">
                            <i class="fa-solid fa-id-card"></i> Expediente {{ $selectedCourseFilter !== 'all' ? '— Curso Filtrado' : '— Visión General' }}
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ $student->name }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-3 mt-2">
                            <span class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-medium">
                                <i class="fa-solid fa-school text-indigo-500 dark:text-indigo-400"></i>
                                {{ $student->classroom->name ?? 'Sin Aula Asignada' }}
                            </span>
                            <span class="text-slate-300 dark:text-slate-700">•</span>
                            <span class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                <i class="fa-regular fa-envelope text-slate-400"></i>
                                {{ $student->email ?? 'Sin correo registrado' }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="relative z-10 pt-6 mt-6 border-t border-slate-200 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Cursos Evaluados</span>
                        <span class="text-base font-bold text-slate-900 dark:text-white">{{ count($results) }} Asignatura(s)</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Comp. Aprobadas</span>
                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $masteredCount }} / {{ max(1, $allCompetencies->count()) }} Comp.</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Estado de Entrega</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-500/20 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span> Al día
                        </span>
                    </div>
                </div>
            </div>

            <!-- KPI Card Rendimiento General en Porcentaje y Letras -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-xl transition-colors duration-300">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Rendimiento General</span>
                
                <h3 class="text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $porcentajeGeneral }}%
                </h3>

                <div class="mt-4">
                    <span class="inline-flex items-center gap-1.5 text-lg font-black text-{{ $nivelClase }}-600 dark:text-{{ $nivelClase }}-400 bg-{{ $nivelClase }}-50 dark:bg-{{ $nivelClase }}-500/10 px-6 py-2 rounded-2xl border border-{{ $nivelClase }}-200 dark:border-{{ $nivelClase }}-500/20 shadow-sm">
                        NIVEL {{ $nivelGeneral }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-3">Promedio total de competencias</p>
            </div>
        </div>

        <!-- Desglose por Cursos y Competencias (SOLO LETRAS, PORCENTAJES Y MENSAJES) -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg flex items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-indigo-600 dark:text-indigo-400"></i> Desglose Detallado por Asignatura
                </h3>

                @if(count($results) > 0)
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="courseSearchInput" placeholder="Buscar asignatura..." 
                               class="w-full pl-9 pr-4 py-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>
                @endif
            </div>

            <div id="coursesContainer" class="space-y-6">
                @forelse($results as $courseName => $competencies)
                    <div class="course-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xl transition-colors duration-300 hover:border-slate-300 dark:hover:border-slate-700" data-course="{{ strtolower($courseName) }}">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-200 dark:border-slate-800">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-600/10 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs">
                                    <i class="fa-solid fa-book"></i>
                                </span>
                                {{ $courseName }}
                            </h4>
                            <span class="text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-700/50">
                                {{ count($competencies) }} Competencias Evaluadas
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach($competencies as $res)
                                @php 
                                    // Conversión a porcentaje y letra pura
                                    $scoreRaw = min(20.0, $res->score ?? 0); 
                                    $percentWidth = min(100, max(0, ($scoreRaw / 20) * 100));
                                    
                                    if ($scoreRaw >= 18.0) {
                                        $letra = 'AD';
                                        $color = 'emerald';
                                        $mensaje = 'Alumno con rendimiento destacado';
                                    } elseif ($scoreRaw >= 14.0) {
                                        $letra = 'A';
                                        $color = 'blue';
                                        $mensaje = 'Alumno con rendimiento satisfactorio';
                                    } elseif ($scoreRaw >= 11.0) {
                                        $letra = 'B';
                                        $color = 'amber';
                                        $mensaje = 'Alumno con rendimiento bueno';
                                    } else {
                                        $letra = 'C';
                                        $color = 'rose';
                                        $mensaje = 'En inicio, requiere apoyo docente';
                                    }
                                @endphp
                                <div class="competency-item bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800/60 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-indigo-200 dark:hover:border-slate-700/60 transition" data-competency="{{ strtolower($res->competency->name ?? '') }}">
                                    
                                    <div class="flex-1">
                                        <h5 class="font-bold text-slate-800 dark:text-slate-200 text-sm mb-2">
                                            {{ $res->competency->name ?? 'Competencia' }}
                                        </h5>
                                        <!-- MENSAJE DE OBSERVACIÓN APLICADO DEBAJO DE LA COMPETENCIA -->
                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-lg inline-block shadow-sm">
                                            Observación: <span class="text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $mensaje }}</span>
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-start md:items-end gap-2 w-full md:w-48">
                                        <!-- NOTA ÚNICAMENTE EN LETRAS Y % -->
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-slate-400">{{ round($percentWidth) }}%</span>
                                            <span class="block font-black text-{{ $color }}-600 dark:text-{{ $color }}-400 text-3xl leading-none">
                                                {{ $letra }}
                                            </span>
                                        </div>
                                        
                                        <!-- BARRA DE PORCENTAJE (SIN NÚMEROS) -->
                                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden p-0.5">
                                            <div class="h-full rounded-full transition-all duration-700 ease-out bg-{{ $color }}-500" 
                                                style="width: {{ $percentWidth }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center text-slate-500 dark:text-slate-400 shadow-xl transition-colors duration-300">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800/50 border rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h4 class="font-bold text-base">Sin Evaluaciones</h4>
                        <p class="text-xs mt-1 font-medium">Aún no existen calificaciones o competencias registradas para este estudiante.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 2. DOCUMENTO FORMAL IMPRESO A4 (SÓLO LETRAS, CERO NÚMEROS)              -->
    <!-- ========================================================================= -->
    <div class="print-only">
        
        <!-- ENCABEZADO OFICIAL -->
        <div style="border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 style="font-size: 18px; font-weight: 900; margin: 0; color: #0f172a; text-transform: uppercase;">
                    I.E. TUNGASUCA — COMAS
                </h1>
                <p style="font-size: 11px; color: #334155; margin: 2px 0 0 0; font-weight: bold;">
                    INFORME PEDAGÓGICO DE EVALUACIÓN POR COMPETENCIAS (CNEB)
                </p>
                <p style="font-size: 9px; color: #64748b; margin: 2px 0 0 0;">
                    UGEL 04 — LIMA METROPOLITANA
                </p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 10px; font-weight: 800; border: 1.5px solid #0f172a; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                    EXPEDIENTE ACADÉMICO
                </span>
                <p style="font-size: 9px; color: #64748b; margin: 4px 0 0 0;">
                    Emisión: {{ date('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        <!-- CUADRO DE DATOS DEL ALUMNO -->
        <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                <tr>
                    <td style="padding: 3px 0; width: 15%; font-weight: bold; color: #475569;">Estudiante:</td>
                    <td style="padding: 3px 0; width: 45%; font-weight: 800; font-size: 12px; color: #0f172a;">{{ strtoupper($student->name ?? 'N/A') }}</td>
                    <td style="padding: 3px 0; width: 15%; font-weight: bold; color: #475569;">Código:</td>
                    <td style="padding: 3px 0; width: 25%; font-weight: bold; color: #0f172a;">{{ $student->code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0; font-weight: bold; color: #475569;">Aula / Grado:</td>
                    <td style="padding: 3px 0; font-weight: bold; color: #0f172a;">{{ $student->classroom->name ?? 'N/A' }}</td>
                    <td style="padding: 3px 0; font-weight: bold; color: #475569;">Condición:</td>
                    <td style="padding: 3px 0; font-weight: bold; color: #15803d;">MATRICULADO / REGULAR</td>
                </tr>
            </table>
        </div>

        <!-- TABLA FORMAL STRICTLY LETTERS & OBSERVATIONS -->
        <h2 style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin-bottom: 8px; border-left: 3px solid #4f46e5; padding-left: 6px;">
            Consolidado Académico de Competencias Evaluadas
        </h2>

        <table style="width: 100%; border-collapse: collapse; font-size: 9.5px; margin-bottom: 16px;">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff; text-align: left;">
                    <th style="padding: 6px 8px; border: 1px solid #0f172a; width: 25%;">ASIGNATURA</th>
                    <th style="padding: 6px 8px; border: 1px solid #0f172a; width: 40%;">COMPETENCIA CURRICULAR</th>
                    <th style="padding: 6px 8px; border: 1px solid #0f172a; width: 10%; text-align: center;">NIVEL LOGRO</th>
                    <th style="padding: 6px 8px; border: 1px solid #0f172a; width: 25%;">OBSERVACIÓN DOCENTE</th>
                </tr>
            </thead>
            <tbody>
                @php $rowIndex = 0; @endphp
                @forelse($results as $courseName => $competencies)
                    @foreach($competencies as $res)
                        @php
                            $rowIndex++;
                            $scoreRaw = min(20.0, $res->score ?? 0);
                            
                            if ($scoreRaw >= 18.0) { $nivel = 'AD'; $obs = 'Alumno con rendimiento destacado'; $isApproved = true;}
                            elseif ($scoreRaw >= 14.0) { $nivel = 'A'; $obs = 'Alumno con rendimiento satisfactorio'; $isApproved = true;}
                            elseif ($scoreRaw >= 11.0) { $nivel = 'B'; $obs = 'Alumno con rendimiento bueno'; $isApproved = true;}
                            else { $nivel = 'C'; $obs = 'En inicio, requiere apoyo docente'; $isApproved = false;}

                            $bgColor = ($rowIndex % 2 == 0) ? '#f8fafc' : '#ffffff';
                        @endphp
                        <tr style="background-color: {{ $bgColor }};">
                            <td style="padding: 5px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #1e293b;">
                                {{ $courseName }}
                            </td>
                            <td style="padding: 5px 8px; border: 1px solid #cbd5e1; color: #334155;">
                                {{ $res->competency->name ?? 'Competencia' }}
                            </td>
                            <td style="padding: 5px 8px; border: 1px solid #cbd5e1; font-weight: 900; text-align: center; font-size: 13px; color: {{ $isApproved ? '#0f172a' : '#b91c1c' }};">
                                {{ $nivel }}
                            </td>
                            <td style="padding: 5px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #334155; font-size: 8.5px;">
                                {{ $obs }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" style="padding: 10px; border: 1px solid #cbd5e1; text-align: center; color: #64748b;">
                            No se registran datos de evaluación disponibles.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- RESUMEN FINAL Y FIRMAS EN IMPRESIÓN -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 14px;">
            <!-- Leyenda CNEB -->
            <div style="width: 55%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px; font-size: 8.5px; color: #475569; background-color: #f8fafc;">
                <strong>ESCALA PEDAGÓGICA CNEB OFICIAL:</strong>
                <ul style="margin: 3px 0 0 12px; padding: 0;">
                    <li><strong>AD:</strong> Logro destacado sobre lo esperado. (Excelente)</li>
                    <li><strong>A:</strong> Logro esperado de la competencia. (Satisfactorio)</li>
                    <li><strong>B:</strong> En proceso de alcanzar el logro. (Bueno)</li>
                    <li><strong>C:</strong> En inicio, requiere apoyo docente. (Deficiente)</li>
                </ul>
            </div>

            <!-- Promedio Consolidado Exclusivamente Letras/Porcentaje -->
            <div style="width: 40%; border: 1.5px solid #0f172a; border-radius: 4px; padding: 8px; text-align: center; background-color: #ffffff;">
                <span style="font-size: 9px; font-weight: bold; color: #475569; text-transform: uppercase; display: block;">RENDIMIENTO GENERAL</span>
                <span style="font-size: 24px; font-weight: 900; color: #0f172a; margin-top: 2px; display: block;">
                    {{ $porcentajeGeneral }}%
                </span>
                <span style="font-size: 11px; font-weight: 900; color: #4f46e5; margin-top: 2px; display: block;">
                    NIVEL DE LOGRO: {{ $nivelGeneral }}
                </span>
            </div>
        </div>

        <!-- SECCIÓN DE FIRMAS -->
        <div style="margin-top: 55px; display: flex; justify-content: space-around; text-align: center; font-size: 9px; color: #334155;">
            <div style="width: 40%; border-top: 1px solid #0f172a; padding-top: 4px;">
                <strong>FIRMA DEL DOCENTE / TUTOR</strong><br>
                <span>I.E. Tungasuca - Comas</span>
            </div>
            <div style="width: 40%; border-top: 1px solid #0f172a; padding-top: 4px;">
                <strong>DIRECCIÓN ACADÉMICA / SELLO</strong><br>
                <span>I.E. Tungasuca - Comas</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeToggleIcon');
        const themeText = document.getElementById('themeToggleText');
        const html = document.documentElement;

        function applyTheme(isDark) {
            if (isDark) {
                html.classList.add('dark');
                if (themeIcon) themeIcon.className = 'fa-solid fa-sun text-amber-400 text-sm';
                if (themeText) themeText.textContent = 'Modo Claro';
                localStorage.setItem('theme', 'dark');
            } else {
                html.classList.remove('dark');
                if (themeIcon) themeIcon.className = 'fa-solid fa-moon text-indigo-500 text-sm';
                if (themeText) themeText.textContent = 'Modo Oscuro';
                localStorage.setItem('theme', 'light');
            }
        }

        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initialIsDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
        
        applyTheme(initialIsDark);

        if (themeBtn) {
            themeBtn.addEventListener('click', function() {
                applyTheme(!html.classList.contains('dark'));
            });
        }

        // Filtro/Buscador Dinámico
        const searchInput = document.getElementById('courseSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                const courseCards = document.querySelectorAll('.course-card');

                courseCards.forEach(card => {
                    const courseName = card.getAttribute('data-course');
                    const compItems = card.querySelectorAll('.competency-item');
                    let matchInCompetency = false;

                    compItems.forEach(item => {
                        const compName = item.getAttribute('data-competency');
                        if (compName.includes(query)) {
                            item.style.display = '';
                            matchInCompetency = true;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (courseName.includes(query) || matchInCompetency) {
                        card.style.display = '';
                        if (courseName.includes(query)) {
                            compItems.forEach(i => i.style.display = '');
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection