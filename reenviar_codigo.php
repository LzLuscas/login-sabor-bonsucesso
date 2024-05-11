<?php
session_start();

// Incluir o arquivo de conexão com o BD
include_once "conexao.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    $_SESSION['msg'] = "<p style='color: red;'>Erro: Você precisa estar logado para reenviar o código!</p>";
    header("Location: index.php");
    exit();
}

// Gera novo código de autenticação
$novo_codigo = mt_rand(100000, 999999);

// Atualiza o banco de dados com o novo código
$query_update_codigo = "UPDATE usuarios SET codigo_autenticacao = :novo_codigo WHERE id = :id";
$stmt_update_codigo = $conn->prepare($query_update_codigo);
$stmt_update_codigo->bindParam(':novo_codigo', $novo_codigo);
$stmt_update_codigo->bindParam(':id', $_SESSION['id']);
$stmt_update_codigo->execute();

// Envio de email com novo código
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    require './lib/vendor/autoload.php';

    // Cria o objeto e instanciar a classe do phpmailer
    $mail = new PHPMailer(true);

    // Config do servidor de email
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7c334afca23f83';
    $mail->Password   = 'ed8d542eea422d';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 2525;

    // Remetente e destinatário
    $mail->setFrom('atendimento@lucas.com', 'Atendimento');
    $mail->addAddress($_SESSION['usuario'], $_SESSION['nome']);

    // Conteudo do email
    $mail->isHTML(true);
    $mail->Subject    = 'Reenvio de código de verificação';
    $mail->Body       = "Olá" . $_SESSION['nome'] . ",\n\nseu novo código de verificação é: $novo_codigo\n\n<br><br>Este código foi reenviado para verificar seu login.";
    
    // Envia email
    $mail->send();

    $_SESSION['msg'] = "<p style='color: green;'>Novo código enviado com sucesso!</p>";
    header("Location: validar_codigo.php");
    exit();
} catch (Exception $e) {
    $_SESSION['msg'] = "<p style='color: red;'>Erro ao enviar e-mail: {$mail->ErrorInfo}</p>";
    header("Location: validar_codigo.php");
    exit();
}
?>
