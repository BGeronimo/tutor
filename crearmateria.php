<?php
    require_once 'conection.php';

    if(isset($_POST['saveMateria'])){
        
        $tipoImagen = $_FILES['imagen']['type'];
        $nombreImagen = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());
        $sizeImagen = $_FILES['imagen']['size'];

        if($sizeImagen<3000000){
            if($tipoImagen == "image/jpeg" || $tipoImagen == "image/jpg" || $tipoImagen == "image/png"){
                $lugarGuardado = $_SERVER['DOCUMENT_ROOT'].'/tutores/php/imagenmateria/';
                switch($tipoImagen){
                    case "image/jpeg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpeg');
                        $nombreArreglado = $nombreImagen.'.jpeg';
                    break;
                    case "image/jpg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpg');
                        $nombreArreglado = $nombreImagen.'.jpg';
                    break;
                    case "image/png":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.png');
                        $nombreArreglado = $nombreImagen.'.png';
                    break;
                }
                
                $saveMateria = $pdo->prepare('INSERT INTO materias (nombre, descripcion, imagenmateria) values (:nombre, :descripcion, :imagenmateria)');
                $saveMateria->bindParam(':nombre', $_POST['nombre']);
                $saveMateria->bindParam(':descripcion', $_POST['descripcion']);
                $saveMateria->bindParam(':imagenmateria', $nombreArreglado);
                $saveMateria->execute();

               
            }else{
                echo 'solo se permite subir archivos de tipo .jpeg, .jpg y .png';
            }
        }else{
            echo 'la imagen des demasiado grande';
        }
    }
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
    <title>Tutores</title>
</head>
<body>
    <nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homeadministrador.php">"Logo"</a>
        <a class="nav-item nav-link" href="actualizarAdministrador.php">Mi cuenta</a>
        <a class="nav-item nav-link" href="materias.php">Materias</a>
        <a class="nav-item nav-link" href="pagartutor.php">Pagar a Tutor</a>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>
    </nav>
    <hr>

    <h1>Crear tutor</h1>

    <form action="crearmateria.php" method="post" enctype="multipart/form-data">
        <div>
            <label>Nombre de la materia</label>
            <input type="text" name="nombre">
            <label>Descripcion</label>
            <input type="text" name="descripcion"> 
        </div>
        
        <label >Escoge tu imagen de perfil</label>
        <div>
            <input type="file" name="imagen">
            
        </div>
        <input type="submit" value="subir" name="saveMateria">
    <br>
    </form>

    <script type="text/javascript" src="auth.js"></script>
</body>
</html>