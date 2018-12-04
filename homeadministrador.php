<?php
    require_once 'conection.php';
    session_start();

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <title>administrador</title>
</head>
<body>
    <nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homeadministrador.php">"Logo"</a>
        <a class="nav-item nav-link" href="materias.php">Materias</a>
        <a class="nav-item nav-link" href="pagartutor.php">Pagar a Tutor</a>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>
    </nav>
    <hr>
    <h1>soy el jefe</h1>
    <?php
    $receptor = "";
    $receptor = "Admin|".$_SESSION['user_id'];
    $traerMensajes = $pdo->prepare('SELECT texto,emisor FROM mensajes WHERE receptor=:receptor AND visto=0');
    $traerMensajes->bindParam(':receptor',$receptor);
    $traerMensajes->execute();
    $cadena = array();
    $emisor = array();
    foreach($traerMensajes as $valor){
        $cadena = explode("|",$valor['texto']);
        $emisor = explode("|",$valor['emisor']);

        $cantidadCadena = count($cadena);

        $traerTutor = $pdo->prepare('SELECT nombres,apellidos,tutor_id FROM tutores WHERE tutor_id=:tutor_id');
        $traerTutor->bindParam(':tutor_id',$emisor[1]);
        $traerTutor->execute();
        $nombres = "";
        $apellidos = "";
        foreach($traerTutor as $valor){
            $nombres = $valor['nombres'];
            $apellidos = $valor['apellidos'];
        }

        echo '<form action="homeadministrador.php" method="post">';
        echo '<div>';
        echo '<h4>'.$nombres.', '.$apellidos.'</h4>';
        echo '<input type="text" value="'.$emisor[1].'" name="tutor_id" hidden="true">';
        echo '<input type="text" value="'.$cadena[0].'" name="cantidad" disabled>';
        if($cantidadCadena>1){
            echo '<input type="text" value="'.$cadena[1].'" name="direccion" disabled>';
        }
        echo '</div>';
        if($cantidadCadena>1){
            echo '<a href="pagartutor.php?id='.$emisor[1].'&cantidad='.$cadena[0].'&direccion='.$cadena[1].'">hacer cheque</a>';
        }else{
            echo '<a href="pagartutor.php?id='.$emisor[1].'&cantidad='.$cadena[0].'">hacer cheque</a>';
        }
        
        echo '<hr>';
        echo '</form>';
        echo '';

    }

        
            
            
            
        
        
   

    ?>
    

    <script type="text/javascript" src="auth.js"></script>
</body>
</html>