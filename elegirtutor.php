<?php
    session_start();
    require_once 'conection.php';

    $traerTutores = $pdo->prepare('SELECT DISTINCT tutores.tutor_id as tutorid ,tutores.nombres AS nombres, tutores.descripcion as descripcion,
    tutores.imagenperfil AS imagen FROM tutores, materiatutor, materiaalumno, alumnos WHERE materiatutor.materia_id = materiaalumno.materia_id 
    AND alumnos.alumno_id = :alumno_id AND tutores.tutor_id = materiatutor.tutor_id AND 
    alumnos.alumno_id = materiaalumno.alumno_id');
    $traerTutores->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerTutores->execute();

    $traerAlumno = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
    $traerAlumno->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerAlumno->execute();

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
    <title>elegir tutor</title>
</head>
<body>

    <nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homealumno.php">"Logo"</a>
        <a class="nav-item nav-link" href="./actualizaralumno.php">Mi cuenta</a>
        <?php
            while($row2 = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
                if($row2['datoscompletos'] == 0){
                    echo '<a class="nav-item nav-link disabled" >Materias</a>';
                    echo '<a class="nav-item nav-link disabled" >Profesores</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row2['puntos'].'puntos</a>';
                    echo '<a class="nav-item nav-link disabled" href="comprarpuntos.php">comprar Puntos</a>';
                }else{
                    echo '<a class="nav-item nav-link" href="materiasalumno.php">Materias</a>';
                    echo '        <a class="nav-link dropdown-toggle" href="elegirtutor.php" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Profesores
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                      <a class="dropdown-item" href="elegirtutor.php">Elegir Tutor</a>
                      <a class="dropdown-item" href="mistutores.php">Mis tutores</a>
                    </div>';
                    echo '<a class="nav-item nav-link disabled" href="comprarpuntos.php">comprar Puntos</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row2['puntos'].'puntos</a>';
                }
            }
        ?>

        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr>

    <h1>Elige un tutor</h1>
    
    <div>
    
        <h2>Profesores que enseñan tus materias deseadas:</h2>
        <?php
            while($row = $traerTutores->fetch(PDO::FETCH_ASSOC)){
                echo '<div class="container-fluid">';
                echo '<img style="height: 100px;"  src="./imagentutor/'.$row['imagen'].'">';
                echo '<h3>'.$row['nombres'].'</h3>';
                echo '<p>'.$row['descripcion'].'</p>';
                echo '<input type="text" name="tutor_id" value="'.$row['tutorid'].'" hidden="true">';
                echo '<a href="perfiltutor.php?id='.$row['tutorid'].'">ver mas</a>';
                echo '<hr/>';
                echo '</div>';
                $_SESSION['calificarTutor'] = $row['tutorid'];
                 
            }
        ?>
            
    </div>

    <script type="text/javascript" src="auth.js"></script>

</body>
</html>



                        
                        
                        
                        

                        
                        
                    
