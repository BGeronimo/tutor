<?php
    require_once 'conection.php';
    session_start();

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();

    $traerTutor2 = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor2->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor2->execute();
    

    if(isset($_POST['pedir'])){
        if($_POST['cantidad']<=$_POST['puntos']){
            if(isset($_POST['mandar'])){
                $cantidad = $_POST['cantidad']-30;
                $texto = $cantidad."|".$_POST['direccion'];
                $emisor = "Tutor|".$_SESSION['user_id'];
                $receptor = "Admin|1";
                $mandarMensaaje = $pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
                $mandarMensaaje->bindParam(':texto',$texto);
                $mandarMensaaje->bindParam(':emisor',$emisor);
                $mandarMensaaje->bindParam(':receptor',$receptor);
                $mandarMensaaje->execute();
            }else{
                $cantidad = $_POST['cantidad'];
                $texto = $cantidad."|PAGO DIRECTO";
                $emisor = "Tutor|".$_SESSION['user_id'];
                $receptor = "Admin|1";
                $mandarMensaaje = $pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
                $mandarMensaaje->bindParam(':texto',$texto);
                $mandarMensaaje->bindParam(':emisor',$emisor);
                $mandarMensaaje->bindParam(':receptor',$receptor);
                $mandarMensaaje->execute();
            }
        }else{
            echo'<script type="text/javascript">
            alert("Pide una cantidad valida, no puedes pedir mas del dinero que tinenes");
            </script>';
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
        $puntos = 0;
        foreach($traerTutor2 as $valor){
            $puntos = $valor['puntos'];
        }

        echo '<form action="pedircheque.php" method="post">';
        echo '<div>';
        echo '<h3>tienes '.$puntos.' puntos</h3>';
        echo '<label>por cuanto quieres que se haga el cheque</label>';
        echo '<input type="number" name="cantidad" onkeypress="return valida(event)">';
        echo '</div>';
        echo '<div>';
        echo '<input type="text" name="puntos" value="'.$puntos.'" hidden="true">';
        echo '<label>desea recibir el cheque en su casa (se le descontaran Q.30)</label>';
        echo '<input type="checkbox" name="mandar" onclick="mostrar()">';
        echo '<input type="text" name="direccion" id="direccion" hidden="true">';
        echo '</div>';
        if($puntos>=500){
            echo '<input type="submit" value="pedir cheque" name="pedir" >';
        }else{
            echo '<input type="submit" value="pedir cheque" name="pedir" disabled>';
        }
        echo '</form>';
            
    ?>    
    <script type="text/javascript" src="auth.js"></script>
</body>
</html>


<script>
function valida(e){
    tecla = (document.all) ? e.keyCode : e.which;

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    // Patron de entrada, en este caso solo acepta numeros
    patron =/[0-9]/;
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}


function mostrar(){
    var mostrar = document.getElementById('direccion')
    if(mostrar.checked){
        mostrar.hidden = true
    }else{
        mostrar.hidden = false
    }
    
}
</script>