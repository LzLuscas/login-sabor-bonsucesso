<?php
    
    session_start(); // Iniciar sessão 

    ob_start(); // Limpar buffer de saída

    // Importar classes Composer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    date_default_timezone_set('America/Sao_Paulo');
    // Incluir conexão com o BD
    include_once "./conexao.php";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>

    <?php
        // criptografar a senha
        // echo password_hash(12345678, PASSWORD_DEFAULT);
        // Receber dados do formulario
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if(!empty($dados['SendLogin'])){
            //var_dump($dados);
            // Recuperar dados do usuario no BD
            $query_usuario = "SELECT id, nome, usuario, senha_usuario
            FROM usuarios
            WHERE usuario = :usuario
            LIMIT 1";


            // Preparar a query
            $result_usuario = $conn->prepare($query_usuario);
            // Substituir o link da query pelo valor que vem do formulario
            $result_usuario->bindParam(':usuario', $dados['usuario']);
            // Executar a query
            $result_usuario->execute();
            
            // Acessar o IF quando encontrar o usuario no BD
            if(($result_usuario) and ($result_usuario->rowCount() != 0)){
                // Ler os registros retornando do BD
                $row_usuario = $result_usuario->fetch(PDO::FETCH_ASSOC);
                //var_dump($row_usuario);

                // Acessar o IF quando a senha é válida
                if(password_verify($dados['senha_usuario'], $row_usuario
                ['senha_usuario'])){

                // Salvar os dados do usuario na sessão
                $_SESSION['id'] = $row_usuario['id'];
                $_SESSION['usuario'] = $row_usuario['usuario'];

                // Recuperar data atual
                $data = date('Y-m-d H:i:s');

                // Gerar número randômico entre 100000 e 999999
                $codigo_autenticacao = mt_rand(100000, 999999);
                //var_dump($codigo_autenticacao);

                // Query para salvar código e data gerada no BD
                $query_up_usuario = "UPDATE usuarios SET
                      codigo_autenticacao =:codigo_autenticacao,
                      data_codigo_autenticacao =:data_codigo_autenticacao
                      WHERE id =:id
                      LIMIT 1";

                // Preparar a query
                $result_up_usuario = $conn->prepare($query_up_usuario);

                //Substituir link da QUERY pelos valores
                $result_up_usuario->bindParam(':codigo_autenticacao', $codigo_autenticacao);
                $result_up_usuario->bindParam(':data_codigo_autenticacao', $data);
                $result_up_usuario->bindParam(':id', $row_usuario['id']);

                // Executar a query
                $result_up_usuario->execute();
                
                // Incluir Composer
                require './lib/vendor/autoload.php';

                // Criar objeto e instanciar a classe do PHPMailer
                $mail = new PHPMailer(true);
                
                // Verificar se email foi enviado corretamente com o try catch
                try {
                    // Imprimir os erros com debug
                    // $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // Definir para usar SMTP
                    $mail->CharSet = 'UTF-8'; // Habilita o uso de caracteres especiais 
                    $mail->isSMTP(); //Servidor de envio de e-mail
                    $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Indicar que é necessario autenticar
                    $mail->SMTPAuth   = true; // Usuario/e-mail para enviar o email
                    $mail->Username   = '7c334afca23f83'; // Senha do email utilizado para enviar email
                    $mail->Password   = 'ed8d542eea422d'; // Ativar criptografia 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Porta para enviar email
                    $mail->Port       = 2525;
                    $mail->setFrom('atendimento@lucas.com', 'Atendimento');  // Email do remetente
                    $mail->addAddress($row_usuario['usuario'], $row_usuario['nome']); // Email de destino
                    $mail->isHTML(true); // Definir formatto de email para HTML
                    $mail->Subject    = 'Aqui está o código de verificação de 8 dígitos solicitado.'; //Título do email
                    $mail->Body       = "Olá " . $row_usuario['nome'] . ", Seu código de verificação de 8 dígitos é $codigo_autenticacao<br><br> 
                    Esse código foi enviado para verificar seu login.<br><br>"; // Conteudo do email em formato HTML
                    $mail->AltBody    = "Olá" . $row_usuario['nome'] . ", Autenticação multifator.\n\nSeu código de verificação de 8 dígitos é $codigo_autenticacao\n\n
                    Esse código foi enviado para verificar seu login."; // Conteudo do email em formato texto
                    $mail->send(); // Enviar email

                    // Redirecionar usuario
                    header('Location: validar_codigo.php');

                } catch (Exception $e) { // Acessa o catch quando não é enviado email corretamente
                    // echo "Message could not be sent. Mailer Error:
                    // {$mail->ErrorInfo}";
                    $_SESSION['msg'] = "<p style='color: #f00;'>Erro: E-mail enviado sem sucesso!</p>";
                }
                
                
                }else{
                    $_SESSION['msg'] = "<p style='color: #f00;'>Erro: Usuário ou senha inválida!</p>";
                }
            }else{
                $_SESSION['msg'] = "<p style='color: #f00;'>Erro: Usuário ou senha inválida!</p>";
            }
        }
        // Imprimir a mensagem da sessão 
        if(isset($_SESSION['msg'])){
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
         
    ?>

    <main class="d-flex align-items-center justify-content-center">
        <div class="form-container" id="form-container">
            <form method="POST" action="" id="multi-step-form">
                <div id="step1">
                    <div class="info-step-1">
                        <p class="p-1">Login</p>
                    </div>
                    <div class="input-box"> 
                        <img src="img/pessoa.png" alt="Figura de uma carta">
                        <input type="text" name="usuario" placeholder="Usuário" required>
                    </div>
                    <div class="input-box">
                        <img src="img/mostrar-senha.png" alt="Figura de uma pessoa">
                        <input type="password" name="senha_usuario" placeholder="Digite sua senha" required>
                    </div>

                    <input type="submit" name= "SendLogin" value= "Acessar" class="next" onclick="nextStep()">  

                </div>
                <a href="#" class="meu-cadastro"> Cadastre-se</a>

                <a href="#" class="esqueceu-senha">Esqueceu a senha?</a>
            </form>


        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script src="index.js"></script>
</body>

</html>