<?php
    require_once 'conection.php';
    session_start();

    
    
    $traerAlumno = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
    $traerAlumno->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerAlumno->execute();

    $traerTutor = $pdo->prepare('SELECT tutores.nombres AS nombre, tutores.apellidos AS apellido, tutores.imagenperfil AS imagen FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_GET['id']);
    $traerTutor->execute();
    
    if(isset($_POST['calificar'])){
        $arreglar = $_SESSION['calificarTutor']+1;
        $calificar = $pdo->prepare('UPDATE calificaciones SET calificacion=:calificacion, comentario=:comentario WHERE tutor_id=:tutor AND alumno_id=:alumno');
        $calificar->bindParam(':calificacion', $_POST['punteo']);
        $calificar->bindParam(':comentario', $_POST['comentario']);
        $calificar->bindParam(':tutor', $arreglar);
        $calificar->bindParam(':alumno', $_SESSION['user_id']);
        $calificar->execute();
        header('Location: elegirtutor.php');
        $_SESSION['calificarTutor'] = "";
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

    <nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homealumno.php">"Logo"</a>
        <a class="nav-item nav-link" href="./actualizaralumno.php">Mi cuenta</a>
        <?php
            while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
                if($row['datoscompletos'] == 0){
                    echo '<a class="nav-item nav-link disabled" >Materias</a>';
                    echo '<a class="nav-item nav-link disabled" >Profesores</a>';
                }else{
                    echo '<a class="nav-item nav-link" href="materiasalumno.php">Materias</a>';
                    echo '        <a class="nav-link dropdown-toggle" href="elegirtutor.php" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Profesores
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                      <a class="dropdown-item" href="elegirtutor.php">Elegir Tutor</a>
                      <a class="dropdown-item" href="mistutores.php">Mis tutores</a>
                    </div>';
                }
            }
        ?>

        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr>

    <?php
        while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
            echo '<form action="calificartutor.php" method="post">';
            echo '<div>';
            echo '<img src="./imagentutor/'.$row['imagen'].'" style="height: 200px;">';
            echo '<h3>'.$row['nombre'].' '.$row['apellido'].'</h3>';
            echo '<div>';
            echo '<input type="radio" name="punteo" value="1"></input>';
            echo '<input type="radio" name="punteo" value="2"></input>';
            echo '<input type="radio" name="punteo" value="3"></input>';
            echo '<input type="radio" name="punteo" value="4"></input>';
            echo '<input type="radio" name="punteo" value="5"></input>';
            echo '<textarea name="comentario" cols="30" rows="10" placeholder="comentario (opcional)"></textarea>';
            echo '<input type="submit" value="calificar" name="calificar">';
            echo '</div>';
            echo '</div>';
            echo '</form>';
        }
    ?>
    
    <script type="text/javascript" src="auth.js"></script>
</body>
</html>