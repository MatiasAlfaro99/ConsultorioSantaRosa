<?php

namespace App\Services;

use App\Models\Solicitud;
use setasign\Fpdi\Fpdi;

class PdfService
{
    public function generarComprobante(Solicitud $solicitud): string
    {
        // 1. Iniciar PDF
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);

        // 2. LOGO
        $logoPath = public_path('logo_municipal.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, 15, 30); 
        }

        // 3. TÍTULO
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetXY(60, 20);
        $pdf->Cell(0, 10, utf8_decode('SOLICITUD DE PERMISO FUNCIONARIO'), 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(60, 28);
        $pdf->Cell(0, 10, utf8_decode('Departamento de Salud Municipal'), 0, 1, 'C');
        
        $pdf->Ln(15);

        // ---------------------------------------------------------
        // I. DATOS DEL FUNCIONARIO
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '1. DATOS DEL FUNCIONARIO');
        $pdf->SetFont('Helvetica', '', 10);

        // Fila 1: Nombre
        $this->dibujarCampoSimple($pdf, 'NOMBRE:', $solicitud->solicitante->name);
        
        // Fila 2: Cargo y Rut
        $pdf->Cell(95, 7, utf8_decode('CARGO: ' . ($solicitud->solicitante->cargo ?? '')), 1, 0);
        $pdf->Cell(95, 7, utf8_decode('RUT: ' . ($solicitud->solicitante->rut ?? '')), 1, 1);

        // Fila 3: Lugar de Trabajo (Hardcoded) y Departamento (Ignored/Blank)
        $pdf->Cell(95, 7, utf8_decode('LUGAR DE TRABAJO: CESFAM SANTA ROSA'), 1, 1);
        
        $pdf->Ln(5);

        // ---------------------------------------------------------
        // II. TIPO DE SOLICITUD
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '2. TIPO DE SOLICITUD (PERMISO)');
        $pdf->SetFont('Helvetica', '', 9);

        // Opciones con Checkbox simulado [ ] o [X]
        $types = [
            'feriado_legal' => 'FERIADO LEGAL',
            'administrativo' => 'PERMISO CON GOCE DE REMUNERACIONES',
            'sin_goce' => 'PERMISO SIN GOCE DE REMUNERACIONES',
            'devolucion' => 'DEVOLUCIÓN DE TIEMPO LIBRE',
            'otros' => 'OTROS PERMISOS'
        ];

        foreach ($types as $key => $label) {
            $marca = ($solicitud->tipo === $key) ? 'X' : ' ';
            $pdf->Cell(10, 6, '[' . $marca . ']', 0, 0, 'C');
            $pdf->Cell(0, 6, utf8_decode($label), 0, 1);
        }
        
        // Si es "otros", especificar motivo
        if ($solicitud->tipo === 'otros' && $solicitud->motivo) {
             $pdf->SetX(30);
             $pdf->MultiCell(0, 5, utf8_decode('Especificar: ' . $solicitud->motivo), 0, 'L');
        }

        $pdf->Ln(5);

        // ---------------------------------------------------------
        // III. DURACIÓN DEL PERMISO
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '3. DURACIÓN DEL PERMISO');
        $pdf->SetFont('Helvetica', '', 10);

        if ($solicitud->es_por_horas) {
            // POR HORAS
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(0, 7, utf8_decode('POR HORAS:'), 0, 1);
            $pdf->SetFont('Helvetica', '', 10);
            
            $pdf->Cell(30, 7, utf8_decode('FECHA:'), 1, 0, 'R');
            $pdf->Cell(40, 7, \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y'), 1, 0, 'C');
            
            $pdf->Cell(30, 7, utf8_decode('DESDE:'), 1, 0, 'R');
            $pdf->Cell(30, 7, $solicitud->hora_inicio . ' hrs', 1, 0, 'C');
            
            $pdf->Cell(30, 7, utf8_decode('HASTA:'), 1, 0, 'R');
            $pdf->Cell(30, 7, $solicitud->hora_fin . ' hrs', 1, 1, 'C');

        } else {
            // POR DÍAS
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(0, 7, utf8_decode('POR DÍAS:'), 0, 1);
            $pdf->SetFont('Helvetica', '', 10);

            $pdf->Cell(40, 7, utf8_decode('CANTIDAD DÍAS:'), 1, 0, 'R');
            $pdf->Cell(30, 7, $solicitud->dias_solicitados, 1, 1, 'C');

            $pdf->Cell(40, 7, utf8_decode('DESDE:'), 1, 0, 'R');
            $pdf->Cell(30, 7, \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y'), 1, 0, 'C');

            $pdf->Cell(40, 7, utf8_decode('HASTA:'), 1, 0, 'R');
            $pdf->Cell(30, 7, \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d/m/Y'), 1, 1, 'C');
        }

        $pdf->Ln(10);

        // ---------------------------------------------------------
        // IV. FIRMAS Y APROBACIONES
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '4. FIRMAS Y APROBACIONES (V°B°)');
        $pdf->Ln(15);

        $yFirmas = $pdf->GetY();
        
        // Columna 1: Funcionario
        $pdf->SetXY(20, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); 
        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('FUNCIONARIO'), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(50, 4, utf8_decode($solicitud->solicitante->name), 0, 1, 'C');

        // Columna 2: Jefe Directo
        $pdf->SetXY(80, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); 
        $pdf->SetXY(80, $yFirmas + 2);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('JEFE DIRECTO'), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(50, 4, utf8_decode($solicitud->jefeAprobador->name ?? '(Pendiente)'), 0, 1, 'C');

        // Columna 3: Dirección (o Gest. Administrativa)
        // Usamos la aprobación del director aquí
        $pdf->SetXY(140, $yFirmas);
        $pdf->Cell(50, 0, '', 'T'); 
        $pdf->SetXY(140, $yFirmas + 2);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50, 4, utf8_decode('DIRECCIÓN / GEST. ADMIN'), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(50, 4, utf8_decode($solicitud->directorAprobador->name ?? '(Pendiente)'), 0, 1, 'C');

        $pdf->Ln(20);

        // ---------------------------------------------------------
        // V. FECHAS Y OBSERVACIONES
        // ---------------------------------------------------------
        $this->dibujarTituloSeccion($pdf, '5. OBSERVACIONES');
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 190, 20); // Caja vacía para observaciones manuales
        $pdf->Ln(25);

        // FOOTER
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->Cell(0, 5, utf8_decode('Fecha de Emisión: ' . now()->format('d/m/Y H:i')), 0, 1, 'R');

        return $pdf->Output('S'); 
    }

    private function dibujarTituloSeccion(Fpdi $pdf, string $titulo)
    {
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(190, 8, utf8_decode($titulo), 1, 1, 'L', true);
        $pdf->Ln(2);
    }

    private function dibujarCampoSimple(Fpdi $pdf, string $label, string $valor)
    {
        $pdf->Cell(190, 7, utf8_decode($label . ' ' . $valor), 1, 1, 'L');
    }
}
