<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();
    $porcentaje = 0.15;

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();

    if(isset($_POST['enviar'])){
        $token = $_POST['token'];
        $confirmacion = $_POST['confirmacion'];

        $verificar = $pdo->prepare('SELECT COUNT(bancopuntos_id) AS conteo, horasclase as horas, puntos as puntos FROM bancopuntos 
        WHERE tutor_id=:tutor_id AND bancopuntos_id=:token AND claveconfirmacion=:confirmacion AND cobropuntos=0');
        $verificar->bindParam(':tutor_id', $_SESSION['user_id']);
        $verificar->bindParam(':token', $token);
        $verificar->bindParam(':confirmacion', $confirmacion);
        $verificar->execute();

        $traerPuntosAnteriores = $pdo->query('SELECT puntos, horasclase FROM tutores WHERE tutor_id='.$_SESSION['user_id'].'');
        $puntosAnteriores = 0;
        $horasAnteriores = 0;
        foreach($traerPuntosAnteriores as $valor){
            $puntosAnteriores = $valor['puntos'];
            $horasAnteriores = $valor['horasclase'];
        }

        foreach($verificar as $valor){

            if($valor['conteo']==1){
                $fecha = zonaHoraria("Y-m-d H:i:s");
                $cobro=1;
                $cobrarPuntos = $pdo->prepare('UPDATE bancopuntos SET fechafin=:fecha, cobropuntos=:cobro WHERE bancopuntos_id=:token');
                $cobrarPuntos->bindParam(':fecha',$fecha);
                $cobrarPuntos->bindParam(':cobro',$cobro);
                $cobrarPuntos->bindParam(':token',$token);
                $cobrarPuntos->execute();


                $puntosActualizados = $puntosAnteriores + ($valor['puntos']- ($valor['puntos']*$porcentaje));
                $horasActualizadas = $horasAnteriores + $valor['horas'];

                $actualizarPerfil = $pdo->prepare('UPDATE tutores SET horasclase=:horas, puntos=:puntos WHERE tutor_id=:tutor_id');
                $actualizarPerfil->bindParam(':horas',$horasActualizadas);
                $actualizarPerfil->bindParam(':puntos',$puntosActualizados);
                $actualizarPerfil->bindParam(':tutor_id',$_SESSION['user_id']);
                if($actualizarPerfil->execute()){
                    $sesion = 0;
                    $actualizarCalificaciones = $pdo->prepare('UPDATE calificaciones SET sesion=:sesion WHERE claveconfirmacion=:claveconfirmacion AND sesion=1');
                    $actualizarCalificaciones->bindParam(':sesion',$sesion);
                    $actualizarCalificaciones->bindParam(':claveconfirmacion',$confirmacion);
                    $actualizarCalificaciones->execute();

                    echo'<script type="text/javascript">
                    alert("tu cobro se ha realizado exitosamente");
                    location.href = "hometutor.php";
                    </script>';
                }
            }else{

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
    <title>tutores</title>
</head>
<body>
<nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="hometutor.php">"Logo"</a>
        <a class="nav-item nav-link" href="actualizartutor.php">Mi cuenta</a>
        <?php
            while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
                if($row['datoscompletos'] == 0){
                    echo '<a class="nav-item nav-link disabled" >Materias</a>';
                    echo '<a class="nav-item nav-link disabled" >Pupilos</a>';
                    echo '<a class="nav-item nav-link disabled" >Cobrar puntos</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }else{
                    echo '<a class="nav-item nav-link" href="materiastutor.php">Materias</a>';
                    echo '<a class="nav-item nav-link" href="pupilos.php">Pupilos</a>';
                    echo '<a class="nav-item nav-link" href="cobropuntos.php">Cobrar puntos</a>';
                    echo '<a class="nav-item nav-link disabled" >'.$row['puntos'].'puntos</a>';
                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>
    </nav>
    <hr>

    <form action="cobropuntos.php" method="post">
        <div>
            <label>Selecciona el token:</label>
            <select name="token">
            <?php
                $traerTokens = $pdo->query('SELECT bancopuntos_id,puntos,fechainicio FROM bancopuntos WHERE cobropuntos=0 AND tutor_id='.$_SESSION['user_id'].'');
                foreach($traerTokens as $valor){
                    echo '<option value="'.$valor['bancopuntos_id'].'">'.$valor['bancopuntos_id'].' || por Q.'.$valor['puntos'].' || del '.$valor['fechainicio'].'</option>';
                } 
            ?>
            </select>
            
        </div>
        <div>
            <label>ingrese el codigo de confirmacion</label>
            <input type="text" placeholder="1328" name="confirmacion">
        </div>
        <input type="submit" value="enviar" name="enviar">
    </form>
    <script type="text/javascript" src="auth.js"></script>
</body>
</html>