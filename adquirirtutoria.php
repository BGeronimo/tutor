<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    
    session_start();
    //echo zonaHoraria("Y-m-d H:i:s");

    $traerDatosTutor = $pdo->query('SELECT cobra FROM tutores WHERE tutor_id='.$_SESSION['tutorId'].'');
    $traerPuntos = $pdo->query('SELECT puntos FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');

    $traerAlumno = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
    $traerAlumno->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerAlumno->execute();

    if(isset($_POST['enviar'])){


        $horas = (int)$_POST['cantidadHoras'];
        $cobra = 0;
        foreach($traerDatosTutor as $valor){
            $cobra = $valor['cobra'];
        }
        $tiene = 0;
        foreach($traerPuntos as $valor){
            $tiene = $valor['puntos'];
        }
        $cantidadCobrar = $horas*$cobra;

        if($cantidadCobrar>$tiene){
            echo'<script type="text/javascript">
            alert("no tienes el dinero suficiente");
            location.href="comprarpuntos.php";
            </script>';
        }else{
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $random = rand(1000, 9999);
            $token = str_shuffle("abcdefghijklmno14725".uniqid());
            $puntosNuevos = $tiene-$cantidadCobrar;
            $actualizarPuntos = $pdo->query('UPDATE alumnos SET puntos='.$puntosNuevos.' WHERE alumno_id='.$_SESSION['user_id'].'');
            $ingresarBancoPuntos = $pdo->prepare('INSERT INTO bancopuntos (bancopuntos_id,alumno_id,tutor_id,fechainicio,claveconfirmacion,puntos,horasclase) 
            VALUES (:bancopuntos_id,:alumno_id,:tutor_id,:fechainicio,:claveconfirmacion,:puntos,:horasclase)');
            $ingresarBancoPuntos->bindParam(':bancopuntos_id',$token);
            $ingresarBancoPuntos->bindParam(':alumno_id',$_SESSION['user_id']);
            $ingresarBancoPuntos->bindParam(':tutor_id',$_SESSION['tutorId']);
            $ingresarBancoPuntos->bindParam(':fechainicio',$fecha);
            $ingresarBancoPuntos->bindParam(':claveconfirmacion',$random);
            $ingresarBancoPuntos->bindParam(':puntos',$cantidadCobrar);
            $ingresarBancoPuntos->bindParam(':horasclase',$horas);
            if($ingresarBancoPuntos->execute()){
                $sesion = 1;
                $insertCalificacion = $pdo->prepare('INSERT INTO calificaciones (tutor_id,alumno_id,sesion,claveconfirmacion) VALUES (:tutor_id,:alumno_id,:sesion,:claveconfirmacion)');
                $insertCalificacion->bindParam(':tutor_id', $_SESSION['tutorId']);
                $insertCalificacion->bindParam(':alumno_id',$_SESSION['user_id']);
                $insertCalificacion->bindParam(':sesion',$sesion);
                $insertCalificacion->bindParam(':claveconfirmacion',$random);
                $insertCalificacion->execute();

               echo' <script> 
                    window.open("imprimirpdf.php?id="+'.$random.'+"&monto="+'.$cantidadCobrar.'+"&horas="+'.$horas.');
                    location.href="elegirtutor.php"; 
                    </script>';
            }
            
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
    <title>tutorias</title>
</head>
<body>
<nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homealumno.php">"Logo"</a>
        <a class="nav-item nav-link" href="actualizaralumno.php">Mi cuenta</a>
        <?php
            
            while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
                if($row['datoscompletos'] == 0){
                    echo '<a class="nav-item nav-link disabled" >Materias</a>';
                    echo '<a class="nav-item nav-link disabled" >Profesores</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }else{
                    echo '<a class="nav-item nav-link" href="materiasalumno.php">Materias</a>';
                    echo '        <a class="nav-link dropdown-toggle" href="elegirtutor.php" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Profesores
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                      <a class="dropdown-item" href="elegirtutor.php">Elegir Tutor</a>
                      <a class="dropdown-item" href="mistutores.php">Mis tutores</a>
                    </div>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr>


    <form action="adquirirtutoria.php" method="post">
        <div>
            <label>cuantas horas vas a pedir</label>
            <select name="cantidadHoras">
                <option value="1">1 Hora</option>
                <option value="2">2 Hora</option>
                <option value="3">3 Hora</option>
                <option value="4">4 Hora</option>
                <option value="5">5 Hora</option>
            </select>

            <input type="submit" value="pagar Tutoria" name="enviar">
            
        </div>
    
    </form>

    
</body>
</html>