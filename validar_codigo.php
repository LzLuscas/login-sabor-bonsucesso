<?php
    
    session_start(); // Iniciar sessão 

    ob_start(); // Limpar buffer de saída

    date_default_timezone_set('America/Sao_Paulo');
    // Incluir conexão com o BD
    include_once "./conexao.php";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="stylesheet" href="index2.css">
    <title>Document</title>
</head>
<body>
    <?php
        // Receber dados do formulario
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if(!empty($dados['ValCodigo'])){
            // var_dump($dados);
            // var_dump($_SESSION['id']);
            // var_dump($_SESSION['usuario']);
        

        // Recuperar dados do usuario no BD
        $query_usuario = "SELECT id, nome, usuario, senha_usuario
        FROM usuarios
        WHERE id =:id 
        AND usuario = :usuario
        AND codigo_autenticacao =:codigo_autenticacao
        LIMIT 1";

        // Preparar a query
        $result_usuario = $conn->prepare($query_usuario);
        // Substituir o link da query pelo valor que vem do formulario
        $result_usuario->bindParam(':id', $_SESSION['id']);
        $result_usuario->bindParam(':usuario', $_SESSION['usuario']);
        $result_usuario->bindParam(':codigo_autenticacao', $dados['codigo_autenticacao']);

        // Executar a query
        $result_usuario->execute();

        // Acessar o IF quando encontrar o usuario no BD
        if(($result_usuario) and ($result_usuario->rowCount() != 0)){
            // Ler os registros retornando do BD
            $row_usuario = $result_usuario->fetch(PDO::FETCH_ASSOC);

            // Query para salvar código e data gerada no BD
            $query_up_usuario = "UPDATE usuarios SET
                    codigo_autenticacao=NULL,
                    data_codigo_autenticacao=NULL
                    WHERE id =:id
                    LIMIT 1";

            // Preparar a query
            $result_up_usuario = $conn->prepare($query_up_usuario);

            //Substituir link da QUERY pelos valores
            $result_up_usuario->bindParam(':id', $_SESSION['id']);

            // Executar a query
            $result_up_usuario->execute();

            // Salvar os dados do usuario na sessão
            $_SESSION['nome'] = $row_usuario['nome'];
            $_SESSION['codigo_autenticacao'] = true;

            // Redirecionar usuario
            header("Location: dashboard.php");

        }else{
            $_SESSION['msg'] = "<p style='color: #f00;'>Erro: Código inválido!</p>";
            // header("Location: index.php");
            // exit();
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
                        <p class="p-1">Código de verificação.</p>
                    </div>
                    <div class="input-box"> 
                        <img src="img/comunicacao.png" alt="Figura de uma carta">
                        <input type="text" name="codigo_autenticacao" placeholder="Digite o código:" required>
                    </div>

                    <input type="submit" name= "ValCodigo" value= "Validar" class="next" onclick="nextStep()">  

                </div>
                <a href="reenviar_codigo.php" class="esqueceu-senha">Reenviar código?</a>

            </form>
        </div>

    </main>


    
    <!-- <form method="POST" action="">
        <label>Código: </label>
        <input type="text" name="codigo_autenticacao" placeholder="Digite o código:"><br><br>

        <input type="submit" name="ValCodigo" value="Validar"><br><br>

    </form><br> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script src="index.js"></script>

</body>
</html>