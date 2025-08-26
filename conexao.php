<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "login";
$port = 3306;



try{
    // Conexão com a porta 
    // $conn = new PDO("mysql:host=$host;port=$port;dbname=" .$dbname, $user, $pass); 
    
    // Conexão sem a porta 
    $conn = new PDO("mysql:host=$host;dbname=" . $dbname, $user, $pass); 
    // echo "Conexão com o banco de dados realizada com sucesso!";

}catch(PDOException $err){
    echo "Erro: Conexão com o banco de dados não realizada. Erro gerado: " . 
    $err->getMessage();

}  

?>