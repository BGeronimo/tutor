<?php
    require_once 'conection.php';
    session_start();

    if(isset($_POST['subirImagen'])){
        
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
                
                $actualizarFoto = $pdo->prepare('UPDATE materias SET imagenmateria=:imagenmateria WHERE materia_id=:materia_id');
                $actualizarFoto->bindParam(':imagenmateria', $nombreArreglado);
                $actualizarFoto->bindParam(':materia_id', $_SESSION['materiaId']);
                if($actualizarFoto->execute()){
                    header('Location: materias.php');
                }
                
               
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
    <title>tutorias</title>
</head>
<body>

<?php
    if(isset($_GET['id'])){
        
    }
?>




    <div>
        <form action="actualizarmateria.php" method="post">
            <input type="text" name ="nombre">
            <input type="text" name ="descripcion">
            <input type="submit" value="actualizar">
        </form>

        <form action="actualizarmateria.php" method="post" enctype="multipart/form-data">
            <label >Escoge tu imagen de perfil</label>
            <div>
                <input type="file" name="imagen">
                <input type="submit" value="subir" name="subirImagen">
            </div>
        <br>
        </form>
    </div>
</body>
</html>