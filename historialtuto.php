<?php
    require_once 'conection.php';
    session_start();

    $traerAlumno = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');

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
    <title>Tutorias</title>
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
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                    echo '<a class="nav-item nav-link disabled" href="comprarpuntos.php">comprar Puntos</a>';
                }else{
                    echo '<a class="nav-item nav-link" href="materiasalumno.php">Materias</a>';
                    echo '        <a class="nav-link dropdown-toggle" href="elegirtutor.php" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Profesores
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                      <a class="dropdown-item" href="elegirtutor.php">Elegir Tutor</a>
                      <a class="dropdown-item" href="mistutores.php">Mis tutores</a>
                      <a class="dropdown-item" href="historialtutores.php">Historial de Tutores</a>
                    </div>';
                    echo '<a class="nav-item nav-link disabled" href="comprarpuntos.php">comprar Puntos</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr> 

    <?php
        $traerHistorial = $pdo->prepare('SELECT DISTINCT tutores.tutor_id as id,tutores.nombres as nombres, tutores.apellidos as apellidos, tutores.email as email, tutores.cobra as cobra,tutores.imagenperfil as imagen 
        FROM tutores, calificaciones WHERE tutores.tutor_id=calificaciones.tutor_id AND calificaciones.alumno_id=:alumno_id AND calificaciones.sesion=0');
        $traerHistorial->bindParam(':alumno_id',$_SESSION['user_id']);
        $traerHistorial->execute();

        foreach($traerHistorial as $valor){
            $id = $valor['id'];
            $nombre = $valor['nombres'];
            $apellidos = $valor['apellidos'];
            $email = $valor['email'];
            $cobra = $valor['cobra'];
            $imagen = $valor['imagen'];

            echo '<form action="recomendartutor.php" method="post">';
            echo '<div>';
            echo '<img style="height: 200px;" src="./imagentutor/'.$imagen.'">';
            echo '<h3>'.$nombre.', '.$apellidos.'</h3>';
            echo '<h4>'.$email.'</h4>';
            echo '<p>Q.'.$cobra.' la hora</p>';
            echo '<input type="text" name="datos" value="'.$id.'|'.$nombre.'|'.$apellidos.'|'.$email.'|'.$cobra.'|'.$imagen.'" hidden="true">';
            echo '<input type="submit" value="compartir">';
            echo '<hr>';
            echo '</div>';
            echo '</form>';
        }
    ?>

    
</body>
</html>