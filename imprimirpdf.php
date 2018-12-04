<?php
    if(isset($_GET['id']) && isset($_GET['monto']) && isset($_GET['horas'])){
        $id = $_GET['id'];
        $monto = $_GET['monto'];
        $horas = $_GET['horas'];
    }
    require 'fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(200,15,'tu clave de activacion es:',0,1);
    $pdf->SetFont('Arial','B',30);
    $pdf->Cell(200,15,$id,0,1);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(200,15,'por la cantidad de:',0,1);
    $pdf->SetFont('Arial','B',30);
    $pdf->Cell(200,15,'Q'.$monto,0,1);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(200,15,'y por:',0,1);
    $pdf->SetFont('Arial','B',30);
    $pdf->Cell(200,15,$horas.'Horas');

    $pdf->Output();
?>