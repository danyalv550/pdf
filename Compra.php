<?php
    session_start();
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    include 'Connection.php';
    require 'vendor/autoload.php';
    require 'vendor/fpdf/fpdf.php';

    $total = $_POST['total'];
    $user_id = $_SESSION['user_id'];
    date_default_timezone_set('America/Mexico_City');
    $timestamp = time();
    $date = date("d-m-Y_H-i-s", $timestamp);

    $sql = "SELECT name, price, amount FROM carrito WHERE idusuario = '$user_id'";
	$result = $con->query($sql);

    $pdf = new FPDF();

    $pdf -> SetFont('Arial', 'B', 24);
    $pdf -> AddPage();
    $pdf -> Cell(30,10,'Factura',1,0,'C');

    $pdf -> Image('Imagenes/PizzaMarg.jpeg',10,8,33);
    $pdf-> SetFont('Times', '', 48);
    $pdf-> Cell(10);
    $pdf-> MultiCell(0, 10, 'Il Forno Di Napoli', 0, 'L');
    
    $pdf -> SetFont('Arial', 'B', 24);
    $pdf -> Cell(80);
    $pdf -> Ln(20);
    $pdf -> Cell(30,10,'Factura');
    $pdf -> Ln(10);

    $pdf -> SetFont('Arial', '', 18);
    
    $pdf->Cell(60,20, "Gracias por tu compra"." ".$_SESSION["user_user"]);

    $pdf -> SetFont('Arial', 'B', 12);

    $pdf->Ln(20); // Espacio antes de la tabla
    $pdf->Cell(60, 10, 'Concepto', 1);
    $pdf->Cell(40, 10, 'Cantidad', 1);
    $pdf->Cell(40, 10, 'Precio por unidad', 1);
    $pdf->Cell(40, 10, 'Subtotal', 1);
    $pdf->Ln(); // Salto de línea

    while ($rows = $result->fetch_assoc()) {
        $pdf->Cell(60, 10, $rows['name'], 1);
        $pdf->Cell(40, 10, $rows['amount'], 1);
        $pdf->Cell(40, 10, '$' . $rows['price'], 1);
        $costo_total = $rows['amount'] * $rows['price'];
        $pdf->Cell(40, 10, '$' . $costo_total, 1); // Agrega el costo total
        $pdf->Ln(); // Salto de línea
    }

    // Total
    $pdf->Cell(100);
    $pdf->Cell(40, 10, 'Total:', 1);
    $pdf->Cell(40, 10, '$' . $total, 1);

    $pdf->SetY(265); // Establece la posición para el pie de página
    $pdf->SetFont('Arial', 'I', 12); // Cambia la fuente para el pie de página
    $pdf->Cell(0, 10, "Fecha: $date", 0, 0, 'R');

    $namePdf = $user_id . "_" . $date . "_.pdf";
    $pdfFilePath = "pdf/" . $user_id . "_" . $date . "_.pdf";

    $pdf->Output("Compra.pdf","F");
    $pdf->Output($pdfFilePath, 'F');

    $webdav_url = 'https://tudireccionwebdav.com/tuarchivo.pdf';
    $local_pdf_path = 'ruta/del/tu/archivo.pdf';


    $outlook_mail = new PHPMailer();
    $outlook_mail->IsSMTP();
    $outlook_mail->Host = 'smtp-mail.outlook.com';
    //$outlook_mail->Host = 'smtp.office365.com';
    $outlook_mail->Port = 587;
    $outlook_mail->SMTPSecure = 'tls';
    $outlook_mail->SMTPDebug = 0;
    $outlook_mail->SMTPAuth = true;
    $outlook_mail->Username = 'IlFornoDiNapoli@outlook.com';
    $outlook_mail->Password = 'cortineando123';
     
    $outlook_mail->From = 'IlFornoDiNapoli@outlook.com';
    $outlook_mail->FromName = 'IlfornoDiNapoli.com';
    $outlook_mail->AddAddress($_SESSION['user_email'], $_SESSION['user_user']);  
     
    $outlook_mail->IsHTML(true);
     
    $outlook_mail->Subject = 'Recibo de compra';
    $outlook_mail->Body    = 'Gracias por tu compra!';
    $outlook_mail->AltBody = 'This is the body in plain text for non-HTML mail clients at https://onlinecode.org/';
    $outlook_mail->AddAttachment('Compra.pdf', '', $encoding = 'base64', $type = 'application/pdf');
     
    if(!$outlook_mail->Send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $outlook_mail->ErrorInfo;
    }
    else {
        echo 'Message of Send email using Outlook SMTP server has been sent';
        header("location: Home.php");
    }


?>
