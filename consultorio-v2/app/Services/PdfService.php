<?php

namespace App\Services;

use App\Models\Solicitud;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;

class PdfService
{
    public function generarComprobante(Solicitud $solicitud): string
    {
        // 1. Iniciar PDF
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false); // Importante para el footer

        // 2. LOGO
        $logoPath = public_path('logo_municipal.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 25);
        }

        // 3. ENCABEZADO
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetXY(50, 15);
        $pdf->Cell(0, 8, utf8_decode('SOLICITUD DE PERMISO FUNCIONARIO'), 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(50, 22);
        $pdf->Cell(0, 6, utf8_decode('Departamento de Salud Municipal'), 0, 1, 'C');

        $pdf->Ln(10);

        // ---------------------------------------------------------
        // I. DATOS DEL FUNCIONARIO
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '1. DATOS DEL FUNCIONARIO');
        $pdf->SetFont('Helvetica', '', 9);

        $nombre = strtoupper($solicitud->solicitante->name ?? '');
        $cargo  = strtoupper($solicitud->solicitante->cargo ?? 'FUNCIONARIO');
        $rut    = strtoupper($solicitud->solicitante->rut ?? '');

        // Fila 1: Nombre
        $this->dibujarCampoSimple($pdf, 'NOMBRE:', $nombre);

        // Fila 2: Cargo y Rut
        $pdf->Cell(90, 6, utf8_decode('CARGO: ' . $cargo), 1, 0);
        $pdf->Cell(90, 6, utf8_decode('RUT: ' . $rut), 1, 1);

        // Fila 3: Lugar de Trabajo
        $pdf->Cell(180, 6, utf8_decode('LUGAR DE TRABAJO: CESFAM SANTA ROSA'), 1, 1);

        $pdf->Ln(4);

        // ---------------------------------------------------------
        // II. TIPO DE SOLICITUD
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '2. TIPO DE SOLICITUD (PERMISO)');
        $pdf->SetFont('Helvetica', '', 9);

        $types = [
            'vacaciones'     => 'FERIADO LEGAL',
            'administrativo' => 'PERMISO CON GOCE DE REMUNERACIONES',
            'sin_goce'       => 'PERMISO SIN GOCE DE REMUNERACIONES',
            'devolucion'     => 'DEVOLUCIÓN DE TIEMPO LIBRE',
            'otros'          => 'OTROS PERMISOS'
        ];

        foreach ($types as $key => $label) {
            $marca = ($solicitud->tipo === $key) ? 'X' : ' ';
            $pdf->Cell(10, 5, '[' . $marca . ']', 0, 0, 'C');
            $pdf->Cell(0, 5, utf8_decode($label), 0, 1);
        }

        if ($solicitud->tipo === 'otros' && $solicitud->motivo) {
             $pdf->SetX(25);
             $pdf->MultiCell(0, 5, utf8_decode('Especificar: ' . $solicitud->motivo), 0, 'L');
        }

        $pdf->Ln(4);

        // ---------------------------------------------------------
        // III. DURACIÓN DEL PERMISO
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '3. DURACIÓN DEL PERMISO');
        $pdf->SetFont('Helvetica', '', 9);

        // --- FILA 1: POR DÍAS ---
        $diasTxt   = (!$solicitud->es_por_horas) ? $solicitud->dias_solicitados : '';
        $desdeDias = (!$solicitud->es_por_horas && $solicitud->fecha_inicio) ? Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y') : '';
        $hastaDias = (!$solicitud->es_por_horas && $solicitud->fecha_fin)    ? Carbon::parse($solicitud->fecha_fin)->format('d/m/Y')    : '';

        $pdf->Cell(25, 6, utf8_decode('POR DÍAS:'), 0, 0);
        $pdf->Cell(20, 6, utf8_decode('CANTIDAD:'), 1, 0, 'R');
        $pdf->Cell(15, 6, $diasTxt, 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode('DESDE:'), 1, 0, 'R');
        $pdf->Cell(30, 6, $desdeDias, 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode('HASTA:'), 1, 0, 'R');
        $pdf->Cell(30, 6, $hastaDias, 1, 1, 'C');

        // --- FILA 2: POR HORAS ---
        $fechaHoras = ($solicitud->es_por_horas && $solicitud->fecha_inicio) ? Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y') : '';
        $horaInicio = ($solicitud->es_por_horas && $solicitud->hora_inicio)  ? substr($solicitud->hora_inicio, 0, 5) : '';
        $horaFin    = ($solicitud->es_por_horas && $solicitud->hora_fin)     ? substr($solicitud->hora_fin, 0, 5)    : '';

        $pdf->Cell(25, 6, utf8_decode('POR HORAS:'), 0, 0);
        $pdf->Cell(20, 6, utf8_decode('FECHA:'), 1, 0, 'R');
        $pdf->Cell(15, 6, $fechaHoras, 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode('DESDE:'), 1, 0, 'R');
        $pdf->Cell(30, 6, $horaInicio, 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode('HASTA:'), 1, 0, 'R');
        $pdf->Cell(30, 6, $horaFin, 1, 1, 'C');

        $pdf->Ln(4);
        $this->dibujarCampoSimple($pdf, 'MOTIVO:', strtoupper($solicitud->motivo ?? 'NO ESPECIFICADO'));

        $pdf->Ln(8);

        // ---------------------------------------------------------
        // IV. FIRMAS Y APROBACIONES (V°B°)
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '4. FIRMAS Y APROBACIONES (V°B°)');
        $pdf->Ln(10);

        $yFirmas = $pdf->GetY();

        // ============================
        // COLUMNA 1: FUNCIONARIO
        // ============================
        $pdf->SetXY(15, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); // Línea
        $pdf->Ln(2);

        // Título
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('FUNCIONARIO'), 0, 1, 'C');

        // Nombre
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(15, $yFirmas + 7);
        $pdf->MultiCell(50, 3, utf8_decode($nombre), 0, 'C');

        // RUT (Nuevo)
        $pdf->SetXY(15, $yFirmas + 12);
        $pdf->Cell(50, 3, utf8_decode('RUT: ' . $rut), 0, 1, 'C');


        // ============================
        // COLUMNA 2: JEFE DIRECTO
        // ============================
        $pdf->SetXY(75, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); // Línea
        $pdf->SetXY(75, $yFirmas + 2);

        // Título
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('JEFE DIRECTO'), 0, 1, 'C');

        // Nombre
        $pdf->SetFont('Helvetica', '', 7);
        $nombreJefe = isset($solicitud->jefeAprobador) ? strtoupper($solicitud->jefeAprobador->name) : '';
        $rutJefe    = isset($solicitud->jefeAprobador) ? strtoupper($solicitud->jefeAprobador->rut ?? '') : '';

        $pdf->SetXY(75, $yFirmas + 7);
        $pdf->MultiCell(50, 3, utf8_decode($nombreJefe), 0, 'C');

        // RUT (Nuevo)
        if($nombreJefe) {
             $pdf->SetXY(75, $yFirmas + 12);
             $pdf->Cell(50, 3, utf8_decode('RUT: ' . $rutJefe), 0, 1, 'C');
        }

        // Fecha aprobación
        if($solicitud->fecha_aprobacion_jefe) {
            $pdf->SetXY(75, $yFirmas + 16);
            $pdf->Cell(50, 3, Carbon::parse($solicitud->fecha_aprobacion_jefe)->format('d/m/Y H:i'), 0, 0, 'C');
        }


        // ============================
        // COLUMNA 3: DIRECCIÓN
        // ============================
        $pdf->SetXY(135, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); // Línea
        $pdf->SetXY(135, $yFirmas + 2);

        // Título
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('DIRECCIÓN'), 0, 1, 'C');

        // Nombre
        $pdf->SetFont('Helvetica', '', 7);
        $nombreDirector = isset($solicitud->directorAprobador) ? strtoupper($solicitud->directorAprobador->name) : '';
        $rutDirector    = isset($solicitud->directorAprobador) ? strtoupper($solicitud->directorAprobador->rut ?? '') : '';

        $pdf->SetXY(135, $yFirmas + 7);
        $pdf->MultiCell(50, 3, utf8_decode($nombreDirector), 0, 'C');

        // RUT (Nuevo)
        if($nombreDirector) {
             $pdf->SetXY(135, $yFirmas + 12);
             $pdf->Cell(50, 3, utf8_decode('RUT: ' . $rutDirector), 0, 1, 'C');
        }

        // Fecha Aprobación
        if($solicitud->fecha_aprobacion_director) {
            $pdf->SetXY(135, $yFirmas + 16);
            $pdf->Cell(50, 3, Carbon::parse($solicitud->fecha_aprobacion_director)->format('d/m/Y H:i'), 0, 0, 'C');
        }

        // ---------------------------------------------------------
        // FOOTER
        // ---------------------------------------------------------
        $pdf->SetY(-20);
        $pdf->SetFont('Helvetica', 'I', 7);
        $pdf->Cell(0, 5, utf8_decode('Fecha de Emisión: ' . now()->format('d/m/Y H:i') . ' - Folio #' . $solicitud->id), 0, 1, 'C');

        return $pdf->Output('S');
    }

    private function dibujarTituloSeccion(Fpdi $pdf, string $titulo)
    {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(180, 6, utf8_decode($titulo), 1, 1, 'L', true);
        $pdf->Ln(2);
    }

    private function dibujarCampoSimple(Fpdi $pdf, string $label, string $valor)
    {
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(35, 6, utf8_decode($label), 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(145, 6, utf8_decode($valor), 1, 1, 'L');
    }
}
