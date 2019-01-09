
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