<?php
    
    session_start(); // Iniciar sessão 

    ob_start(); // Limpar buffer de saída

    // DESTRUIR as sessões
    unset($_SESSION['id'], $_SESSION['nome'], $_SESSION['usuario'], $_SESSION['codigo_autenticacao']);
  
    // Acessar o IF quando o usuaio não esta logado e redirecionar para pg login
    if((!isset($_SESSION['id'])) and (!isset($_SESSION['usuario'])) and (!isset($_SESSION['codigo_autenticacao']))){
        $_SESSION['msg'] = "<p style='color: green;'>Erro: Desconectado com sucesso!</p>";

        //Redirecionar usuario 
        header("Location: index.php");

        // Pausar processamento
        exit();
    }
?>