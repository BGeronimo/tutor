<?php
    require_once 'conection.php';
    session_start();

    $traerAlumno = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');

    
    if(isset($_POST['enviar'])){
        if(isset($_POST['correo'])){
            $traerReceptor = $pdo->prepare('SELECT COUNT(alumnos.alumno_id) as numero, alumnos.alumno_id as id, alumnos.email as email 
            FROM alumnos WHERE email=:email');
            $traerReceptor->bindParam(':email',$_POST['correo']);
            $traerReceptor->execute();
            $count = 0;
            $id = 0;
            $email = "";
            foreach($traerReceptor as $valor){
                if($valor['numero']==1){
                    $count = $valor['numero'];
                    $id = $valor['id'];
                    $email = $valor['email'];
                }
            }

            if($count>0){
                //receptor
                $receptor = "Alumno|".$id;

                $traerEmisor = $pdo->prepare('SELECT email FROM alumnos WHERE alumno_id=:alumno_id');
                $traerEmisor->bindParam(':alumno_id',$_SESSION['user_id']);
                $traerEmisor->execute();
                $emailEmisor= "";
                foreach($traerEmisor as $valor){
                    $emailEmisor =$valor['email'];
                }
                //emisor
                $emisor = "Alumno|".$_SESSION['user_id'];



                if(!empty($_POST['texto'])){
                    $texto = $emailEmisor."|".$_POST['texto'];
                }else{
                    $texto = $emailEmisor." te ha recomendado ha este tutor";
                }

                $mensajeCompleto = "1|".$texto."|".$_POST['datosTutor'];
                $insertMensaje = $pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
                $insertMensaje->bindParam(':texto',$mensajeCompleto);
                $insertMensaje->bindParam(':emisor',$emisor);
                $insertMensaje->bindParam(':receptor',$receptor);
                $insertMensaje->execute();

            }else{
                echo 'no se ha encontrado el usuario...';
            }

            

        }else{
            echo 'ingresa un correo del usuario destinatario';
        }
        
        
    }

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
                    echo '<a class="nav-item nav-link" href="comprarpuntos.php">comprar Puntos</a>';
                    echo '<a class="nav-item nav-link" >'.$row['puntos'].'puntos</a>';
                }
            }
        ?>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>

    </nav>
    <hr>


    <?php
        if(isset($_POST['datos'])){
            echo '<form action="recomendartutor.php" method="post">';
            echo '<div>';
            echo '<input type="text" name="correo" placeholder="correo">';
            echo '<input type="text" name="texto" placeholder="mensaje (opcional)">';
            echo '<input type="text" name="datosTutor" value="'.$_POST['datos'].'" hidden="true">';
            echo '<input type="submit" value="enviar" name="enviar">';
            echo '<hr>';
            echo '</div>';
            echo '</form>';
            
        }
    ?>

    
        
            
            

            
            
            


            
        
    
    
</body>
</html>