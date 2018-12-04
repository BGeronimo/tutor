<?php
    require_once 'conection.php';
    session_start();

    $traerAlumno = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
    $traerAlumno->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerAlumno->execute();

    //////prueba
    $traerMateriasDisponibles = $pdo->prepare('SELECT materias.materia_id as id, materias.nombre as nombre, materias.descripcion as descripcion, materias.imagenmateria as imagen 
    FROM materias WHERE materias.materia_id NOT IN(SELECT materiaalumno.materia_id 
    FROM materiaalumno WHERE materias.materia_id =materiaalumno.materia_id AND  materiaalumno.alumno_id = :alumno_id)');
    $traerMateriasDisponibles->bindParam(':alumno_id',$_SESSION['user_id']);
    $traerMateriasDisponibles->execute();

    $traerMateriasAsignadas = $pdo->prepare('SELECT materias.materia_id as id, materias.nombre as nombre, materias.descripcion as descripcion, materias.imagenmateria as imagen 
    FROM materias WHERE materias.materia_id IN(SELECT materiaalumno.materia_id 
    FROM materiaalumno WHERE materias.materia_id =materiaalumno.materia_id AND  materiaalumno.alumno_id = :alumno_id)');
    $traerMateriasAsignadas->bindParam(':alumno_id',$_SESSION['user_id']);
    $traerMateriasAsignadas->execute();

    if(isset($_POST['escogerMateria'])){
        $contador = count($_POST['materia']);
        if($contador>0){
            $base = "";
            $vuelta = 0;
            foreach($_POST['materia'] as $valor){
                $vuelta +=1; 
                if($contador>1){
                    if($contador==$vuelta){
                        $base .= "(".$valor.",".$_SESSION['user_id'].")";
                    }else{
                        $base .= "(".$valor.",".$_SESSION['user_id']."),";
                    }
                }else{
                    $base = "(".$valor.",".$_SESSION['user_id'].")";
                }
            }
            $escogerMateria = $pdo->query('INSERT INTO materiaalumno (materia_id, alumno_id) VALUES '.$base.'');
            header('Location: materiasalumno.php');
        }else{
            echo 'elige una o mas materias primero';
        }
        

    }

    if(isset($_POST['desligar'])){
        $borrarMateriaTutor = $pdo->prepare('DELETE FROM materiaalumno WHERE alumno_id=:alumno_id AND materia_id=:materia_id');
        $borrarMateriaTutor->bindParam(':materia_id', $_POST['materia_id']);
        $borrarMateriaTutor->bindParam(':alumno_id', $_SESSION['user_id']);
        if($borrarMateriaTutor->execute()){
            header('Location: materiasalumno.php');
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
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
                    </div>';
                    echo '<a class="nav-item nav-link disabled" href="comprarpuntos.php">comprar Puntos</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr>

    <h1>Materias</h1>

<div class="container-fluid">
  <div class="row">
    <div class="col-sm">
        <?php
            echo '<h2>Materias disponibles</h2>'; 
            echo '<form action="materiasalumno.php" method="post">';
            echo '<input type="submit" value="escoger" class="btn btn-primary" name="escogerMateria">';
            while($row = $traerMateriasDisponibles->fetch(PDO::FETCH_ASSOC)){
                echo '<div class="card" style="width: 18rem;">';
                echo '<img class="card-img-top" alt="Card image cap" src="./imagenmateria/'.$row['imagen'].'">'; 
                echo '<div class="card-body">';
                echo '<h5 class="card-title">'.$row['nombre'].'</h5>';
                echo '<p class="card-text">'.$row['descripcion'].'</p>';
                echo '<input type="checkbox" name="materia[]" value="'.$row['id'].'">';
                echo '</div>';
                echo '</div>';
            }
            echo '</form>';
        ?>
    </div>
    <div class="col-sm">
        <?php
            echo '<h2>Materias asignadas</h2>'; 
            while($row = $traerMateriasAsignadas->fetch(PDO::FETCH_ASSOC)){
                echo '<form action="materiasalumno.php" method="post">';
                echo '<div class="card" style="width: 18rem;">';
                echo '<img class="card-img-top" alt="Card image cap" src="./imagenmateria/'.$row['imagen'].'">'; 
                echo '<div class="card-body">';
                echo '<h5 class="card-title">'.$row['nombre'].'</h5>';
                echo '<p class="card-text">'.$row['descripcion'].'</p>';
                echo '<input type="text" name="materia_id" value="'.$row['id'].'" hidden="true">';
                echo '<input type="submit" value="desligar" class="btn btn-secondary" name="desligar">';
                echo '</div>';
                echo '</div>';
                echo '</form>';   
            }
        ?>
    </div>
  </div>
</div>

<script type="text/javascript" src="auth.js"></script>
    
</body>
</html>