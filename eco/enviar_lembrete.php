<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ECOGESTOR";

// Conectar ao banco de dados
$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Incluir os arquivos do PHPMailer manualmente
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuração do SMTP
$servidor_smtp = "smtp.gmail.com"; // Alterar se necessário
$usuario_email = "seuemail@gmail.com"; // Seu e-mail SMTP
$senha_email = "suasenha"; // Senha do e-mail
$porta_smtp = 587; // Porta 587 para TLS ou 465 para SSL

// Buscar lembretes do dia
$data_hoje = date("Y-m-d");
$sql = "SELECT * FROM lembretes WHERE data_lembrete = '$data_hoje' AND enviado = 0";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $email_destino = $row['email'];
        $tipo = $row['tipo'];

        // Criar a mensagem automaticamente
        $mensagem = "Olá,\n\nEste é um lembrete automático sobre sua ";
        $mensagem .= ($tipo == "licenca") ? "licença ambiental" : "outorga de uso da água";
        $mensagem .= ".\n\nPor favor, verifique a situação e tome as providências necessárias.\n\nAtenciosamente,\nSistema ECOGESTOR.";

        // Enviar e-mail usando PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $servidor_smtp;
            $mail->SMTPAuth = true;
            $mail->Username = $usuario_email;
            $mail->Password = $senha_email;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $porta_smtp;

            $mail->setFrom($usuario_email, "Sistema ECOGESTOR");
            $mail->addAddress($email_destino);
            $mail->Subject = "📌 Lembrete Importante!";
            $mail->Body = $mensagem;

            $mail->send();

            // Marcar lembrete como enviado
            $id_lembrete = $row['id'];
            $mysqli->query("UPDATE lembretes SET enviado = 1 WHERE id = $id_lembrete");

            echo "E-mail enviado para: $email_destino <br>";
        } catch (Exception $e) {
            echo "Erro ao enviar e-mail para $email_destino: {$mail->ErrorInfo} <br>";
        }
    }
} else {
    echo "Nenhum lembrete para hoje.";
}

$mysqli->close();
?>
