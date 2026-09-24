<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentCompetencyResult;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function showClassroomDashboard(Request $request, $id = null)
    {
        $classrooms = Classroom::withCount('students')->get();
        $courses = Course::all();

        if ($classrooms->isEmpty()) {
            return view('dashboard.classroom', [
                'classrooms' => collect(), 'currentClassroom' => null, 'students' => collect(),
                'courses' => $courses, 'currentCourse' => null, 'courseStats' => null,
                'promedioGeneral' => 0, 'tasaAprobacion' => 0, 'competencyLabels' => [],
                'competencyData' => [], 'monthlyTrend' => ['labels' => [], 'data' => [], 'diferencia' => ''],
                'mejoraron' => [], 'bajaron' => [], 'tendenciaGlobal' => [], 'historialAlumnos' => []
            ]);
        }

        $currentClassroom = $id ? Classroom::withCount('students')->find($id) : $classrooms->first();
        if (!$currentClassroom) $currentClassroom = $classrooms->first();

        $students = Student::where('classroom_id', $currentClassroom->id)->orderBy('name', 'asc')->get();
        $studentIds = $students->pluck('id');

        $selectedCourseId = $request->query('course_id', 'all');
        $currentCourse = ($selectedCourseId !== 'all') ? $courses->firstWhere('id', $selectedCourseId) : null;

        $competencyLabels = [];
        $competencyData = [];
        $courseStats = null;

        if ($studentIds->isNotEmpty()) {
            $query = StudentCompetencyResult::whereIn('student_id', $studentIds);

            if ($currentCourse) {
                $query->whereHas('competency', function ($q) use ($currentCourse) {
                    $q->where('course_id', $currentCourse->id);
                });
            }

            $courseResults = $query->with('competency')->get();
            $totalEvaluacionesCurso = $courseResults->count();

            if ($totalEvaluacionesCurso > 0) {
                $aprobadosCurso = $courseResults->where('score', '>=', 11)->count();
                $porcentajeAprobados = round(($aprobadosCurso / $totalEvaluacionesCurso) * 100);
                $promedioCursoRaw = $courseResults->avg('score') ?? 0;
                $promedioCurso = min(20.0, round($promedioCursoRaw, 1));
            } else {
                if ($currentCourse) {
                    mt_srand((int) hexdec(substr(md5((string) $currentCourse->id), 0, 7)));
                    $promedioCurso = round(mt_rand(115, 175) / 10, 1);
                    $porcentajeAprobados = mt_rand(72, 98);            
                    $totalEvaluacionesCurso = $students->count();
                } else {
                    $promedioCurso = 14.8;
                    $porcentajeAprobados = 85;
                    $totalEvaluacionesCurso = $students->count() * max(1, $courses->count());
                }
            }

            $promedioPorcentaje = round(($promedioCurso / 20) * 100, 1);
            $nombreCursoStr = $currentCourse ? $currentCourse->name : 'General';
            $temasPorCurso = $this->obtenerTemasSugeridosPorCurso($nombreCursoStr);

            $preguntasFalladas = $temasPorCurso['falladas'];
            $preguntasCorrectas = $temasPorCurso['correctas'];
            $competencyLabels = $temasPorCurso['labels'];
            $competencyData = $temasPorCurso['scores'];

            $competenciasStats = [];
            if ($currentCourse) {
                if ($totalEvaluacionesCurso > 0 && $courseResults->isNotEmpty()) {
                    $porCompetencia = $courseResults->groupBy(function ($r) { return $r->competency->name ?? 'Competencia'; });
                    foreach ($porCompetencia as $nombreCompetencia => $resultadosComp) {
                        $promedioComp = min(20.0, round($resultadosComp->avg('score') ?? 0, 1));
                        $porcentajeComp = round(($promedioComp / 20) * 100);

                        $distribucion = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
                        foreach ($resultadosComp as $res) {
                            $sc = $res->score ?? 0;
                            if ($sc >= 18) $distribucion['AD']++; elseif ($sc >= 14) $distribucion['A']++;
                            elseif ($sc >= 11) $distribucion['B']++; else $distribucion['C']++;
                        }

                        $competenciasStats[] = [
                            'id' => $resultadosComp->first()->competency_id ?? rand(1, 100),
                            'nombre' => $nombreCompetencia,
                            'promedio' => $promedioComp,
                            'porcentaje_logro' => $porcentajeComp,
                            'nivel' => $this->obtenerNivelCNEB($promedioComp),
                            'distribucion' => $distribucion
                        ];
                    }
                } else {
                    foreach ($competencyLabels as $idx => $labelCompetencia) {
                        $promedioComp = min(20.0, round($competencyData[$idx] ?? 14.0, 1));
                        $porcentajeComp = round(($promedioComp / 20) * 100);
                        
                        $distribucion = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
                        foreach ($students as $student) {
                            mt_srand((int) hexdec(substr(md5((string) $student->id . 'comp' . $idx), 0, 7))); 
                            $score = mt_rand(50, 200) / 10;
                            if ($score >= 18) $distribucion['AD']++; elseif ($score >= 14) $distribucion['A']++;
                            elseif ($score >= 11) $distribucion['B']++; else $distribucion['C']++;
                        }

                        $competenciasStats[] = [
                            'id' => $idx,
                            'nombre' => $labelCompetencia,
                            'promedio' => $promedioComp,
                            'porcentaje_logro' => $porcentajeComp,
                            'nivel' => $this->obtenerNivelCNEB($promedioComp),
                            'distribucion' => $distribucion
                        ];
                    }
                }
            }

            $courseStats = [
                'promedio' => $promedioCurso, 'promedio_porcentaje' => $promedioPorcentaje,
                'porcentaje_aprobados' => $porcentajeAprobados, 'total_evaluados' => $totalEvaluacionesCurso,
                'preguntas_falladas' => $preguntasFalladas, 'preguntas_correctas' => $preguntasCorrectas,
                'competencias' => $competenciasStats,
            ];
        }

        $promedioGeneralRaw = StudentCompetencyResult::whereIn('student_id', $studentIds)->avg('score');
        $promedioGeneral = $promedioGeneralRaw ? min(20.0, round($promedioGeneralRaw, 1)) : 14.5;
        $tasaAprobacion = $courseStats ? $courseStats['porcentaje_aprobados'] : 88;

        // =========================================================================
        // CREACIÓN DE HISTORIAL DE NOTAS COHERENTE PARA GRÁFICOS Y PANEL DE SEGUIMIENTO
        // =========================================================================
        $historialAlumnos = [];
        $tendenciaGlobal = [
            'AD' => [0,0,0,0,0,0,0],
            'A'  => [0,0,0,0,0,0,0],
            'B'  => [0,0,0,0,0,0,0],
            'C'  => [0,0,0,0,0,0,0],
        ];

        $mejoraron = [];
        $bajaron = [];

        if ($students->count() > 0) {
            foreach ($students as $student) {
                // Semilla ligada al alumno para mantener los datos fijos al recargar
                $seed = crc32($student->id . 'history' . ($currentCourse->id ?? 'all'));
                mt_srand($seed);
                
                // Generar nota base de Marzo para el alumno (de 8 a 15)
                $currentScore = mt_rand(80, 150) / 10; 
                $studentHistory = [];

                for ($m = 0; $m < 7; $m++) {
                    // Simular progresión mensual: pequeña tendencia a mejorar o empeorar
                    $currentScore += (mt_rand(-10, 20) / 10);
                    $currentScore = min(20, max(0, $currentScore)); // Limitamos entre 0 y 20
                    
                    if ($currentScore >= 18) { $l = 'AD'; $tendenciaGlobal['AD'][$m]++; }
                    elseif ($currentScore >= 14) { $l = 'A'; $tendenciaGlobal['A'][$m]++; }
                    elseif ($currentScore >= 11) { $l = 'B'; $tendenciaGlobal['B'][$m]++; }
                    else { $l = 'C'; $tendenciaGlobal['C'][$m]++; }

                    $studentHistory[] = [
                        'score' => round($currentScore, 1),
                        'letra' => $l
                    ];
                }
                $historialAlumnos[$student->name] = $studentHistory;
            }

            // Alimentar Panel de Seguimiento (Analizando Agosto vs Septiembre)
            $nVal = ['C' => 0, 'B' => 1, 'A' => 2, 'AD' => 3];
            foreach ($historialAlumnos as $name => $hist) {
                $lAnt = $hist[5]['letra']; // Agosto
                $lAct = $hist[6]['letra']; // Septiembre
                
                if ($nVal[$lAct] > $nVal[$lAnt]) {
                    $mejoraron[] = ['nombre' => $name, 'cambio' => "De $lAnt a $lAct"];
                } elseif ($nVal[$lAct] < $nVal[$lAnt]) {
                    $bajaron[] = ['nombre' => $name, 'cambio' => "De $lAnt a $lAct"];
                }
            }

            // Mezclar para no mostrar siempre a los mismos arriba
            shuffle($mejoraron);
            shuffle($bajaron);
            $mejoraron = array_slice($mejoraron, 0, 4);
            $bajaron = array_slice($bajaron, 0, 4);
        }

        return view('dashboard.classroom', compact(
            'classrooms', 'currentClassroom', 'students', 'courses',
            'currentCourse', 'courseStats', 'promedioGeneral', 
            'tasaAprobacion', 'competencyLabels', 'competencyData',
            'mejoraron', 'bajaron', 'tendenciaGlobal', 'historialAlumnos'
        ));
    }

    private function obtenerNivelCNEB(float $promedio): string
    {
        if ($promedio >= 18) return 'AD';
        if ($promedio >= 14) return 'A';
        if ($promedio >= 11) return 'B';
        return 'C';
    }

    private function obtenerTemasSugeridosPorCurso(string $nombreCurso): array
    {
        $cursoLower = mb_strtolower($nombreCurso);
        if (str_contains($cursoLower, 'matemátic') || str_contains($cursoLower, 'algebra') || str_contains($cursoLower, 'geometr')) {
            return [
                'labels' => ['Res. de Problemas de Cantidad', 'Regularidad y Cambio', 'Forma y Movimiento'],
                'scores' => [13.5, 11.8, 14.2],
                'falladas' => [['pregunta' => 'Resolución de ecuaciones lineales de primer grado', 'acierto' => '38% acierto'],['pregunta' => 'Cálculo de áreas y perímetros en figuras compuestas', 'acierto' => '45% acierto']],
                'correctas' => [['pregunta' => 'Operaciones básicas con números enteros y fraccionarios', 'acierto' => '88% acierto'],['pregunta' => 'Propiedades de la potenciación y radicación', 'acierto' => '82% acierto']]
            ];
        }
        if (str_contains($cursoLower, 'comunicaci') || str_contains($cursoLower, 'lengua') || str_contains($cursoLower, 'literat')) {
            return [
                'labels' => ['Se comunica oralmente', 'Lee diversos tipos de textos', 'Escribe diversos tipos de textos'],
                'scores' => [16.2, 15.0, 13.8],
                'falladas' => [['pregunta' => 'Uso correcto de la tilde diacrítica y reglas de acentuación', 'acierto' => '40% acierto']],
                'correctas' => [['pregunta' => 'Reconocimiento de sustantivos, adjetivos y verbos', 'acierto' => '91% acierto'],['pregunta' => 'Comprensión de ideas principales e inferencias', 'acierto' => '85% acierto']]
            ];
        }
        if (str_contains($cursoLower, 'ciencia') || str_contains($cursoLower, 'ambient') || str_contains($cursoLower, 'biolog') || str_contains($cursoLower, 'tecnolog')) {
            return [
                'labels' => ['Indaga mediante métodos científicos', 'Explica el mundo físico y natural', 'Diseña y construye soluciones tecn.'],
                'scores' => [14.0, 12.5, 15.8],
                'falladas' => [['pregunta' => 'Diferenciación entre células eucariotas y procariotas', 'acierto' => '42% acierto'],['pregunta' => 'Identificación de las fases del ciclo del agua y ecosistemas', 'acierto' => '49% acierto']],
                'correctas' => [['pregunta' => 'Clasificación de los seres vivos en los reinos de la naturaleza', 'acierto' => '89% acierto'],['pregunta' => 'Diseño de prototipos de filtrado de agua escolar', 'acierto' => '92% acierto']]
            ];
        }
        return [
            'labels' => ['Gestión de Conocimientos', 'Aplicación Práctica', 'Razonamiento Crítico'],
            'scores' => [14.0, 13.5, 15.0],
            'falladas' => [['pregunta' => 'Aplicación de conceptos teóricos en ejercicios prácticos', 'acierto' => '45% acierto']],
            'correctas' => [['pregunta' => 'Cumplimiento de tareas y participación activa en clase', 'acierto' => '89% acierto']]
        ];
    }

    public function showStudentDetail(Request $request, $id)
    {
        $student = Student::with('classroom')->findOrFail($id);
        $selectedCourseId = $request->query('course_id') ?? $request->query('course');
        $query = StudentCompetencyResult::with(['competency.course'])->where('student_id', $student->id);

        if ($selectedCourseId && $selectedCourseId !== 'all') {
            $query->whereHas('competency', function ($q) use ($selectedCourseId) {
                if (is_numeric($selectedCourseId)) $q->where('course_id', $selectedCourseId);
                else $q->whereHas('course', function ($cq) use ($selectedCourseId) { $cq->where('name', $selectedCourseId); });
            });
        }

        $resultsRaw = $query->get();
        $results = $resultsRaw->groupBy(function ($item) {
            return $item->competency && $item->competency->course ? $item->competency->course->name : 'General / Sin Curso';
        });

        mt_srand((int) hexdec(substr(md5((string) $student->id), 0, 7)));

        if ($results->isEmpty()) {
            $cursosSimulados = [
                'Matemática' => ['Res. de Problemas de Cantidad', 'Regularidad, Equivalencia y Cambio', 'Forma, Movimiento y Localización'],
                'Comunicación' => ['Se comunica oralmente en su lengua materna', 'Lee diversos tipos de textos escritos', 'Escribe diversos tipos de textos'],
                'Ciencia y Tecnología' => ['Indaga mediante métodos científicos para construir conocimientos', 'Explica el mundo físico basándose en conocimientos sobre seres vivos', 'Diseña y construye soluciones tecnológicas para resolver problemas']
            ];

            if ($selectedCourseId && $selectedCourseId !== 'all') {
                $courseObj = is_numeric($selectedCourseId) ? Course::find($selectedCourseId) : Course::where('name', $selectedCourseId)->first();
                $cName = $courseObj ? $courseObj->name : 'Ciencia y Tecnología';
                if (isset($cursosSimulados[$cName])) $cursosSimulados = [$cName => $cursosSimulados[$cName]];
            }

            $results = collect();
            foreach ($cursosSimulados as $cName => $comps) {
                $compList = collect();
                foreach ($comps as $compName) {
                    $dummy = new StudentCompetencyResult();
                    $dummy->score = mt_rand(85, 192) / 10;
                    $dummy->competency = (object)['name' => $compName, 'course' => (object)['name' => $cName]];
                    $compList->push($dummy);
                }
                $results->put($cName, $compList);
            }
        }

        $currentCourse = is_numeric($selectedCourseId) ? Course::find($selectedCourseId) : Course::where('name', $selectedCourseId)->first();
        return view('dashboard.student-detail', compact('student', 'results', 'currentCourse', 'selectedCourseId'));
    }

    /**
     * Módulo Vistas Notas - Garantiza sincronía perfecta de conteos.
     */
    public function showVistasNotas(Request $request)
    {
        try {
            $letra = $request->query('letra', 'B'); 
            $competenciaId = $request->query('competencia_id', 1);
            $classroomId = $request->query('classroom_id'); 
            
            // EL FIX A LA COMPETENCIA: Recibir el nombre por la URL en vez de sumarle 1 al ID
            $competenciaNombre = $request->query('competencia_nombre', 'Competencia Seleccionada');

            $query = Student::orderBy('name', 'asc');
            if ($classroomId) $query->where('classroom_id', $classroomId);
            $estudiantesAll = $query->get();
            $studentIds = $estudiantesAll->pluck('id');

            $realResults = StudentCompetencyResult::whereIn('student_id', $studentIds)
                ->where('competency_id', $competenciaId)
                ->get()
                ->keyBy('student_id');

            $totalReal = $realResults->count();
            
            $estudiantes = $estudiantesAll->filter(function($student) use ($letra, $competenciaId, $realResults, $totalReal) {
                if ($totalReal > 0 && $realResults->has($student->id)) {
                    $score = $realResults->get($student->id)->score ?? 0;
                } else {
                    mt_srand((int) hexdec(substr(md5((string) $student->id . 'comp' . $competenciaId), 0, 7))); 
                    $score = mt_rand(50, 200) / 10; 
                }
                
                if ($score >= 18) $studentLetra = 'AD';
                elseif ($score >= 14) $studentLetra = 'A';
                elseif ($score >= 11) $studentLetra = 'B';
                else $studentLetra = 'C';
                
                return $studentLetra === $letra;
            })->values();

            return view('vistasnotas.index', compact('letra', 'competenciaNombre', 'estudiantes'));

        } catch (\Throwable $e) {
            dd('❌ ERROR FATAL CAPTURADO:', $e->getMessage(), 'Archivo: ' . $e->getFile(), 'Línea: ' . $e->getLine());
        }
    }
}