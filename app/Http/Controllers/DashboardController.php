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
        $selectedCourseId = $request->query('course_id', 'all');
        $currentCourse = ($selectedCourseId !== 'all') ? $courses->firstWhere('id', $selectedCourseId) : null;
        $nombreCursoStr = $currentCourse ? $currentCourse->name : 'Matemática';

        // ======================================================================================
        // SISTEMA UNIFICADO DE DATOS - FUENTE ÚNICA DE VERDAD COHERENTE POR CURSO
        // ======================================================================================
        $historialAlumnos = [];
        $tendenciaGlobal = ['AD' => array_fill(0,7,0), 'A' => array_fill(0,7,0), 'B' => array_fill(0,7,0), 'C' => array_fill(0,7,0)];
        
        $distribuciones = [
            0 => ['AD'=>0, 'A'=>0, 'B'=>0, 'C'=>0],
            1 => ['AD'=>0, 'A'=>0, 'B'=>0, 'C'=>0],
            2 => ['AD'=>0, 'A'=>0, 'B'=>0, 'C'=>0],
        ];

        $sumAverages = 0;
        $aprobadosCount = 0;

        foreach ($students as $student) {
            $history = $this->getStudentUnifiedData($student->id, $currentClassroom->id, $nombreCursoStr);
            $curr = $history[6]; // Mes actual (Setiembre)

            $student->promedio_pct = $curr['avg_pct'];
            $student->letra = $curr['avg_letra'];
            $student->color = $this->getColorCNEB($curr['avg_letra']);

            $sumAverages += $curr['avg_score'];
            if ($curr['avg_score'] >= 10.5) $aprobadosCount++;

            foreach ($curr['comps'] as $idx => $compData) {
                if (isset($distribuciones[$idx])) {
                    $distribuciones[$idx][$compData['letra']]++;
                }
            }

            for ($m=0; $m<7; $m++) {
                $tendenciaGlobal[$history[$m]['avg_letra']][$m]++;
            }

            $historialAlumnos[$student->name] = $history;
        }

        $totalEvaluacionesCurso = $students->count();
        $promedioCurso = $totalEvaluacionesCurso > 0 ? min(20.0, round($sumAverages / $totalEvaluacionesCurso, 1)) : 0;
        $porcentajeAprobados = $totalEvaluacionesCurso > 0 ? round(($aprobadosCount / $totalEvaluacionesCurso) * 100) : 0;
        $promedioPorcentaje = round(($promedioCurso / 20) * 100, 1);

        $temasPorCurso = $this->obtenerTemasSugeridosPorCurso($nombreCursoStr);
        $competencyLabels = $temasPorCurso['labels'];

        $competenciasStats = [];
        if ($currentCourse && $totalEvaluacionesCurso > 0) {
            foreach ($competencyLabels as $idx => $labelCompetencia) {
                $sumComp = 0;
                foreach ($students as $student) {
                    $sumComp += $historialAlumnos[$student->name][6]['comps'][$idx]['score'];
                }
                $avgComp = min(20.0, round($sumComp / $totalEvaluacionesCurso, 1));

                $competenciasStats[] = [
                    'id' => $idx,
                    'nombre' => $labelCompetencia,
                    'promedio' => $avgComp,
                    'porcentaje_logro' => round(($avgComp / 20) * 100),
                    'nivel' => $this->obtenerNivelCNEB($avgComp),
                    'distribucion' => $distribuciones[$idx] ?? ['AD'=>0, 'A'=>0, 'B'=>0, 'C'=>0]
                ];
            }
        }

        $courseStats = [
            'promedio' => $promedioCurso, 'promedio_porcentaje' => $promedioPorcentaje,
            'porcentaje_aprobados' => $porcentajeAprobados, 'total_evaluados' => $totalEvaluacionesCurso,
            'preguntas_falladas' => $temasPorCurso['falladas'], 'preguntas_correctas' => $temasPorCurso['correctas'],
            'competencias' => $competenciasStats,
        ];

        // Panel de Seguimiento (Agosto vs Setiembre)
        $mejoraron = [];
        $bajaron = [];
        $nVal = ['C' => 0, 'B' => 1, 'A' => 2, 'AD' => 3];
        
        foreach ($historialAlumnos as $name => $hist) {
            $lAnt = $hist[5]['avg_letra']; // Agosto
            $lAct = $hist[6]['avg_letra']; // Setiembre
            
            if ($nVal[$lAct] > $nVal[$lAnt]) {
                $mejoraron[] = ['nombre' => $name, 'cambio' => "De $lAnt a $lAct"];
            } elseif ($nVal[$lAct] < $nVal[$lAnt]) {
                $bajaron[] = ['nombre' => $name, 'cambio' => "De $lAnt a $lAct"];
            }
        }

        shuffle($mejoraron); shuffle($bajaron);
        $mejoraron = array_slice($mejoraron, 0, 4);
        $bajaron = array_slice($bajaron, 0, 4);

        $promedioGeneral = $promedioCurso;
        $tasaAprobacion = $porcentajeAprobados;

        return view('dashboard.classroom', compact(
            'classrooms', 'currentClassroom', 'students', 'courses',
            'currentCourse', 'courseStats', 'promedioGeneral', 
            'tasaAprobacion', 'competencyLabels', 'temasPorCurso',
            'mejoraron', 'bajaron', 'tendenciaGlobal', 'historialAlumnos'
        ));
    }

    /**
     * Devuelve las competencias oficiales del CNEB según el curso de prueba evaluado
     */
    private function getStudentUnifiedData($studentId, $classroomId, $courseStr) {
        $courseLower = mb_strtolower($courseStr);
        
        if (str_contains($courseLower, 'matemátic')) {
            $comps = [
                'Resuelve problemas de cantidad', 
                'Resuelve problemas de regularidad, equivalencia y cambio', 
                'Resuelve problemas de forma, movimiento y localización'
            ];
        } elseif (str_contains($courseLower, 'comunicaci')) {
            $comps = [
                'Se comunica oralmente en su lengua materna', 
                'Lee diversos tipos de textos escritos', 
                'Escribe diversos tipos de textos'
            ];
        } elseif (str_contains($courseLower, 'ciencia') || str_contains($courseLower, 'tecnolog')) {
            $comps = [
                'Indaga mediante métodos científicos', 
                'Explica el mundo físico', 
                'Diseña y construye soluciones tecnológicas'
            ];
        } else {
            $comps = ['Competencia 1', 'Competencia 2', 'Competencia 3'];
        }

        $history = [];
        for ($m=0; $m<7; $m++) { $history[$m] = ['comps' => []]; }

        foreach ($comps as $idx => $compName) {
            mt_srand((int) hexdec(substr(md5($classroomId . '-' . $studentId . '-' . $courseStr . '-' . $idx), 0, 7)));
            $baseScore = mt_rand(85, 185) / 10; 

            for ($m=0; $m<7; $m++) {
                $score = $baseScore + ($m * 0.4) + (mt_rand(-12, 18) / 10);
                $score = min(20.0, max(0.0, $score));
                $letra = $this->obtenerNivelCNEB($score);
                
                $history[$m]['comps'][] = [
                    'name' => $compName,
                    'score' => round($score, 1),
                    'letra' => $letra,
                    'pct' => min(100, round(($score/20)*100))
                ];
            }
        }

        for ($m=0; $m<7; $m++) {
            $sum = 0;
            $bestScore = -1;
            $bestComp = null;
            
            foreach ($history[$m]['comps'] as $c) {
                $sum += $c['score'];
                if ($c['score'] > $bestScore) {
                    $bestScore = $c['score'];
                    $bestComp = $c;
                }
            }
            
            $avg = $sum / count($comps);
            $history[$m]['avg_score'] = round($avg, 1);
            $history[$m]['avg_letra'] = $this->obtenerNivelCNEB($avg);
            $history[$m]['avg_pct'] = min(100, round(($avg/20)*100));
            $history[$m]['best_comp_name'] = $bestComp['name'];
            $history[$m]['best_comp_letra'] = $bestComp['letra'];
        }

        return $history;
    }

    private function obtenerNivelCNEB(float $promedio): string {
        if ($promedio >= 18) return 'AD';
        if ($promedio >= 14) return 'A';
        if ($promedio >= 11) return 'B';
        return 'C';
    }

    private function getColorCNEB(string $letra): string {
        return match($letra) { 'AD' => 'emerald', 'A' => 'blue', 'B' => 'amber', default => 'rose' };
    }

    private function obtenerTemasSugeridosPorCurso(string $nombreCurso): array {
        $cursoLower = mb_strtolower($nombreCurso);
        if (str_contains($cursoLower, 'matemátic')) {
            $labels = ['Resuelve problemas de cantidad', 'Resuelve problemas de regularidad, equivalencia y cambio', 'Resuelve problemas de forma, movimiento y localización'];
        } elseif (str_contains($cursoLower, 'comunicaci')) {
            $labels = ['Se comunica oralmente en su lengua materna', 'Lee diversos tipos de textos escritos', 'Escribe diversos tipos de textos'];
        } elseif (str_contains($cursoLower, 'ciencia') || str_contains($cursoLower, 'tecnolog')) {
            $labels = ['Indaga mediante métodos científicos', 'Explica el mundo físico', 'Diseña y construye soluciones tecnológicas'];
        } else {
            $labels = ['Competencia 1', 'Competencia 2', 'Competencia 3'];
        }

        return [
            'labels' => $labels,
            'falladas' => [['pregunta' => 'Aplicación de conceptos teóricos en ejercicios', 'acierto' => '45%']],
            'correctas' => [['pregunta' => 'Participación activa y desarrollo de tareas', 'acierto' => '89%']]
        ];
    }

    public function showStudentDetail(Request $request, $id)
    {
        $student = Student::with('classroom')->findOrFail($id);
        $selectedCourseId = $request->query('course_id') ?? $request->query('course');
        
        $currentCourse = null;
        if ($selectedCourseId && $selectedCourseId !== 'all') {
            if (is_numeric($selectedCourseId)) {
                $currentCourse = Course::find($selectedCourseId);
            } else {
                $currentCourse = Course::where('name', $selectedCourseId)->first();
            }
        }

        // Asignamos estrictamente el nombre del curso actual (Matemática, Comunicación o Ciencia)
        $courseName = $currentCourse ? $currentCourse->name : 'Matemática';

        // Extraemos exactamente las competencias del curso correspondiente
        $historialReal = $this->getStudentUnifiedData($student->id, $student->classroom_id, $courseName);
        $notasActuales = $historialReal[6]['comps'];

        $results = collect([
            $courseName => collect($notasActuales)->map(function($c) {
                $obj = new \stdClass();
                $obj->score = $c['score'];
                $obj->competency = new \stdClass();
                $obj->competency->name = $c['name'];
                return $obj;
            })
        ]);

        return view('dashboard.student-detail', compact('student', 'results', 'currentCourse', 'selectedCourseId'));
    }

    public function showVistasNotas(Request $request)
    {
        try {
            $letra = $request->query('letra', 'B'); 
            $competenciaId = (int) $request->query('competencia_id', 0);
            $classroomId = $request->query('classroom_id', 1); 
            $competenciaNombre = $request->query('competencia_nombre', 'Competencia Seleccionada');
            $courseName = $request->query('course_name', 'General');
            $courseId = $request->query('course_id', 'all'); 

            $query = Student::orderBy('name', 'asc');
            if ($classroomId) $query->where('classroom_id', $classroomId);
            $estudiantesAll = $query->get();
            
            $estudiantes = $estudiantesAll->filter(function($student) use ($letra, $competenciaId, $classroomId, $courseName) {
                $historial = $this->getStudentUnifiedData($student->id, $classroomId, $courseName);
                $compLetra = $historial[6]['comps'][$competenciaId]['letra'] ?? 'C';
                return $compLetra === $letra;
            })->values();

            return view('vistasnotas.index', compact('letra', 'competenciaNombre', 'estudiantes', 'courseId', 'courseName'));

        } catch (\Throwable $e) {
            dd('❌ ERROR FATAL CAPTURADO:', $e->getMessage());
        }
    }
}