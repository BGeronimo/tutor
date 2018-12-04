<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();

    $traerTutor2 = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor2->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor2->execute();

    $verificarComentario = $pdo->prepare('SELECT alumnos.nombres,alumnos.apellidos, alumnos.imagenperfil,calificaciones.comentario,calificaciones.calificaciones_id FROM alumnos,calificaciones 
    WHERE calificaciones.revision=0  AND calificaciones.comentario != "" AND calificaciones.tutor_id = :tutor_id AND calificaciones.alumno_id=alumnos.alumno_id');
    $verificarComentario->bindParam(':tutor_id', $_SESSION['user_id']);
    $verificarComentario->execute();



    if(isset($_POST['tutor'])){
        $eleccion = 1;
        $completo = $pdo->prepare('UPDATE indecisos SET eleccion=:eleccion WHERE indeciso_id=:indeciso_id');
        $completo->bindParam(':eleccion',$eleccion);
        $completo->bindParam(':indeciso_id', $_SESSION['idBorrar']);
        $completo->execute();

        $agregarNuevoTutor = $pdo->prepare('INSERT INTO tutores (email,firebase_id) values (:email,:firebase_id)');
        $agregarNuevoTutor->bindParam(':email',$_SESSION['email']);
        $agregarNuevoTutor->bindParam(':firebase_id',$_SESSION['firebase_id']);
        $agregarNuevoTutor->execute();

        $traerNuevoTutor = $pdo->prepare('SELECT tutor_id as id FROM tutores WHERE email=:email');
        $traerNuevoTutor->bindParam(':email',$_SESSION['email']);
        $traerNuevoTutor->execute();

        foreach($traerNuevoTutor as $valor){
            $_SESSION['user_id'] = $valor['id'];
            header('Location: actualizartutor.php');
        }
    }

    if(isset($_POST['alumno'])){
        $eleccion = 1;
        $completo = $pdo->prepare('UPDATE indecisos SET eleccion=:eleccion WHERE indeciso_id=:indeciso_id');
        $completo->bindParam(':eleccion',$eleccion);
        $completo->bindParam(':indeciso_id', $_SESSION['idBorrar']);
        $completo->execute();

        $agregarNuevoAlumno = $pdo->prepare('INSERT INTO alumnos (email,firebase_id) values (:email,:firebase_id)');
        $agregarNuevoAlumno->bindParam(':email',$_SESSION['email']);
        $agregarNuevoAlumno->bindParam(':firebase_id',$_SESSION['firebase_id']);
        $agregarNuevoAlumno->execute();

        $traerNuevoAlumno = $pdo->prepare('SELECT tutor_id as id FROM alumnos WHERE email=:email');
        $traerNuevoAlumno->bindParam(':email',$_SESSION['email']);
        $traerNuevoAlumno->execute();

        foreach($traerNuevoTutor as $valor){
            $_SESSION['user_id'] = $valor['id'];
            header('Location: actualizaralumno.php');
        }
    }

    if(isset($_POST['haceptar'])){
        $fecha = zonaHoraria("Y-m-d H:i:s");
        $token = $_POST['datos'];
        $actualizarBancoPuntos = $pdo->prepare('UPDATE bancopuntos SET cobropuntos=1, nota="clase cancelada",fechafin=:fechafin WHERE bancopuntos_id = :bancopuntos_id');
        $actualizarBancoPuntos->bindParam(':bancopuntos_id',$token);
        $actualizarBancoPuntos->bindParam(':fechafin',$fecha);
        $actualizarBancoPuntos->execute();

        $traerAlumno = $pdo->prepare('SELECT alumno_id,puntos FROM bancopuntos WHERE bancopuntos_id =:bancopuntos_id');
        $traerAlumno->bindParam(':bancopuntos_id',$token);
        $traerAlumno->execute();
        $alumnoid = 0;
        $puntos=0;
        foreach($traerAlumno as $valor){
            $alumnoid = $valor['alumno_id'];
            $puntos =$valor['puntos'];
        } 
        
        $traerAlumnoActualizar = $pdo->prepare('SELECT puntos FROM alumnos WHERE alumno_id=:alumno_id');
        $traerAlumnoActualizar->bindParam(':alumno_id',$alumnoid);
        $traerAlumnoActualizar->execute();
        $puntosAlumno = 0;
        foreach($traerAlumnoActualizar as $valor){
            $puntosAlumno = $valor['puntos'];
        }

        $puntosActualizados = $puntosAlumno+$puntos;

        $actualizarPuntos = $pdo->prepare('UPDATE alumnos SET puntos=:puntos WHERE alumno_id=:alumno_id');
        $actualizarPuntos->bindParam(':puntos',$puntosActualizados);
        $actualizarPuntos->bindParam(':alumno_id',$alumnoid);
        if($actualizarPuntos->execute()){
             //emisor
            $emisor = "Tutor|".$_SESSION['user_id'];

            //receptor
            $receptor = "Alumno|".$alumnoid; 

            //texto
            $texto = "2|Acepto la declinacion, tus puntos han sido devueltos|".$_SESSION['user_id'];

            $mandarMensaje=$pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
            $mandarMensaje->bindParam(':texto',$texto);
            $mandarMensaje->bindParam(':emisor',$emisor);
            $mandarMensaje->bindParam(':receptor',$receptor);
            $mandarMensaje->execute();

            $visto = $pdo->prepare('UPDATE mensajes SET visto=1 WHERE texto=:texto');
            $visto->bindParam(':texto',$_POST['texto']);
            $visto->execute();

            $actualizarSesion = $pdo->prepare('UPDATE calificaciones SET sesion=0 WHERE tutor_id=:tutor_id AND alumno_id=:alumno_id AND sesion=1');
            $actualizarSesion->bindParam(':tutor_id',$_SESSION['user_id']);
            $actualizarSesion->bindParam(':alumno_id',$alumnoid);
            $actualizarSesion->execute();
        }



    }
    
    
    if(isset($_POST['rechazar'])){
        $token = $_POST['datos'];
        $traerAlumno = $pdo->prepare('SELECT alumno_id FROM bancopuntos WHERE bancopuntos_id =:bancopuntos_id');
        $traerAlumno->bindParam(':bancopuntos_id',$token);
        $traerAlumno->execute();
        $alumnoid = 0;
        foreach($traerAlumno as $valor){
            $alumnoid = $valor['alumno_id'];
        }  


        //emisor
        $emisor = "Tutor|".$_SESSION['user_id'];

        //receptor
        $receptor = "Alumno|".$alumnoid; 

        //texto
        $texto = "2|No hacepto la declinacion|".$_SESSION['user_id'];

        $mandarMensaje=$pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
        $mandarMensaje->bindParam(':texto',$texto);
        $mandarMensaje->bindParam(':emisor',$emisor);
        $mandarMensaje->bindParam(':receptor',$receptor);
        $mandarMensaje->execute();

        $visto = $pdo->prepare('UPDATE mensajes SET visto=1 WHERE texto=:texto');
        $visto->bindParam(':texto',$_POST['texto']);
        $visto->execute();
    }

    if(isset($_POST['SI'])){
        $actualizarVisualizacion = $pdo->query('UPDATE calificaciones SET visible=1, revision=1 WHERE calificaciones_id='.$_POST['idCalificacion'].'');
        header('Location: hometutor.php');
    }

    if(isset($_POST['NO'])){    
        $actualizarVisualizacion = $pdo->query('UPDATE calificaciones SET visible=0, revision=1 WHERE calificaciones_id='.$_POST['idCalificacion'].'');
        header('Location: hometutor.php');
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
    <title>Document</title>
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
                    echo '<a class="nav-item nav-link" href="pedircheque.php">pedir cheque</a>';

                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>
    </nav>
    <hr> 

        <?php
        while($row = $traerTutor2->fetch(PDO::FETCH_ASSOC)){
            echo '<h1> hola '.$row['email'].'</h1>';
            break;
        }
        
        ?>
    


    <?php
        if(isset($_GET['indeciso'])){
            $recordarId = $pdo->prepare('SELECT * FROM indecisos WHERE email=:email');
            $recordarId->bindParam(':email',$_GET['email']);
            $recordarId->execute();
            foreach($recordarId as $valor){
                $_SESSION['idBorrar'] = $valor['indeciso_id'];
                $_SESSION['email'] = $_GET['email'];
                $_SESSION['firebase_id'] = $_GET['firebase_id'];

            }
            echo '    
            <form action="hometutor.php" method="post">
                <div>
                    <input type="submit" value="Quiero ser tutor" name="tutor">
                    <input type="submit" value="Quiero ser tutor" name="alumno">
                </div>
            </form>';

        }
        $receptor = "Tutor|".$_SESSION['user_id'];

        $traerMensajes = $pdo->prepare('SELECT texto FROM mensajes WHERE receptor=:receptor AND visto=0');
        $traerMensajes->bindParam(':receptor',$receptor);
        $traerMensajes->execute();

        foreach($traerMensajes as $valor){
            $separar = explode('|',$valor['texto']);

            if($separar[0] == 1){
                echo '<form action="hometutor.php" method="post">';
                    echo '<div>';
                    echo '<h3>El alumno: '.$separar[1].' y con correo: '.$separar['2'].'</h3>';
                    echo '<h4>desea cancelar la tutoria</h4>';
                    echo '<p>que tenia un valor de Q.'.$separar[3].' y un token de: '.$separar[4].'</p>';
                    echo '<input type="text" name="datos" value="'.$separar[4].'" hidden="true">';
                    echo '<input type="text" name="texto" value="'.$valor['texto'].'" hidden="true">';
                    echo '<input type="submit" value="haceptar declinacion" name="haceptar">';
                    echo '<input type="submit" value="rechazar" name="rechazar">';
                    echo '<hr>';
                    echo '</div>';
                echo '</form>';

            }
        }


        foreach($verificarComentario as $valor){
            echo '<form action="hometutor.php" method="post">';
                echo '<div>';
                echo '<img style="height: 200px;" src="./imagenalumno/'.$valor['imagenperfil'].'">';
                echo '<h3>'.$valor['nombres'].','.$valor['apellidos'].'</h3>';
                echo '<textarea name="" cols="40" rows="8" disabled>'.$valor['comentario'].'</textarea>';
                echo '<p>quieres que este comentario este visible?</p>';
                echo '<input type="text" name="idCalificacion" value="'.$valor['calificaciones_id'].'" hidden="true">';
                echo '<input type="submit" value="SI" name="SI">';
                echo '<input type="submit" value="NO" name="NO">';
                echo '</div>';
            echo '</form>';
        }
    ?>

    <script type="text/javascript" src="auth.js"></script>
</body>
</html>