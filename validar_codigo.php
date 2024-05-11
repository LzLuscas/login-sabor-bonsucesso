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
    <title>Document</title>
</head>
<body>

    <h2>Digite o código enviado no e-mail cadastrado.</h2>
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


    
    <form method="POST" action="">
        <label>Código: </label>
        <input type="text" name="codigo_autenticacao" placeholder="Digite o código:"><br><br>

        <input type="submit" name="ValCodigo" value="Validar"><br><br>

    </form><br>


</body>
</html>