<?php
require '../vendor/setasign/fpdf/fpdf.php';
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

class PDF extends FPDF
{
    // Encabezado
    function Header()
    {
        // Logo
        $this->Image('../assets/img/clausulasyetc/Logo.png', 10, 10, 30);
        // Título
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, mb_convert_encoding('Contrato de Préstamo Individual', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Separador de sección
    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(0, 10, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C', true);
        $this->Ln(5);
    }

    // Texto con estilo
    function StyledText($label, $value)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(50, 10, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8') . ':', 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'), 0, 1);
    }
}

// Obtener datos del préstamo desde la base de datos
$id_prestamo = isset($_GET['id_prestamo']) ? intval($_GET['id_prestamo']) : 0;

try {
    // Consulta SQL
    $sql = "SELECT p.monto_autorizado AS monto_prestamo, p.monto_total, p.plazo_semanas, 
            p.pago_por_periodo AS pago_semanal, p.fecha_solicitud AS lugar_fecha, 
            c.nombre_completo AS nombre_cliente, d.direccion, c.telefono, 
            p.tasa_interes AS interes
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            INNER JOIN direcciones d ON c.id = d.cliente_id
            WHERE p.id = :id_prestamo LIMIT 1";

    $stmt = $GLOBALS['conn']->prepare($sql);
    $stmt->bindParam(':id_prestamo', $id_prestamo, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        die('No se encontró el préstamo con el ID proporcionado.');
    }

    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 10);

    // Sección: Datos Generales
    $pdf->SectionTitle('DATOS GENERALES');
    $pdf->StyledText('Nombre', $contrato['nombre_cliente']);
    $pdf->StyledText('Monto del Préstamo', '$' . number_format($contrato['monto_prestamo'], 2));
    $pdf->StyledText('Domicilio Particular', $contrato['direccion']);
    $pdf->StyledText('Teléfono', $contrato['telefono']);
    $pdf->Ln(5);

    // Sección: Detalles del Préstamo
    $pdf->SectionTitle('DETALLES DEL PRÉSTAMO');
    $pdf->StyledText('Monto Total a Pagar', '$' . number_format($contrato['monto_total'], 2));
    $pdf->StyledText('Plazo', $contrato['plazo_semanas'] . ' semanas');
    $pdf->StyledText('Pago Semanal', '$' . number_format($contrato['pago_semanal'], 2));
    $pdf->StyledText('Días de Pago', ($contrato['plazo_semanas'] * 7) . ' días');
    $pdf->StyledText('Interés Total', '$' . number_format($contrato['interes'], 2));
    $pdf->Ln(5);

    // Sección: Lugar y Fecha
    $pdf->SectionTitle('LUGAR Y FECHA');
    $pdf->StyledText('Lugar y Fecha de Expedición', $contrato['lugar_fecha']);
    $pdf->Ln(5);

    // Sección: Consentimientos
    $pdf->SectionTitle('CONSENTIMIENTOS');
    $pdf->StyledText('Consentimiento', "Otorgo mi consentimiento para los términos y condiciones aquí establecidos.");
    $pdf->Ln(10);

    // Nueva página para las imágenes
    $pdf->AddPage();
    // Sección: Declaraciones y Cláusulas
    $pdf->SectionTitle('DECLARACIONES Y CLÁUSULAS');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 10, mb_convert_encoding("El cliente declara que ha leído y comprende las condiciones del presente contrato, las cuales incluyen las cláusulas detalladas a continuación.", 'ISO-8859-1', 'UTF-8'), 0, 'J');
    $pdf->Ln(10);

    // Imagen de Declaraciones
    if (file_exists('../assets/img/clausulasyetc/Declaraciones.png')) {
        $pdf->Image('../assets/img/clausulasyetc/Declaraciones.png', 29, 70, 160, 0, 'PNG');
    } else {
        $pdf->Cell(0, 10, 'Imagen de Declaraciones no encontrada', 0, 1, 'C');
    }

    // Espaciado entre imágenes
    $pdf->Ln(100);

    // Imagen de Cláusulas
    if (file_exists('../assets/img/clausulasyetc/Clausulas.png')) {
        $pdf->Image('../assets/img/clausulasyetc/Clausulas.png', 29, 110, 160, 0, 'PNG');
    } else {
        $pdf->Cell(0, 10, 'Imagen de Cláusulas no encontrada', 0, 1, 'C');
    }

    // Salida del PDF
    $pdf->Output('I', 'Contrato.pdf');
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
}
