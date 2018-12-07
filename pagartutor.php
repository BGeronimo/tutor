<?php
    require_once 'conection.php';
    require_once 'zonahoraria.php';
    session_start();

    if(isset($_POST['pagar'])){
        if(!empty($_POST['nocheque'])){
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $id = $_POST['tutor_id'];
            $cantidad = (int)$_POST['cantidad'];
            $nocheque = $_POST['nocheque'];

            $comprobarPuntos = $pdo->query('SELECT puntos FROM tutores WHERE tutor_id='.$_POST['tutor_id'].'');
            $puntosValidos = 0;
            foreach($comprobarPuntos as $valor){
                $puntosValidos = $valor['puntos']-($valor['puntos']*0.15);
            }


            if($puntosValidos>=$cantidad){
                $puntos = $puntosValidos-$cantidad;

                $ingresarRegistro = $pdo->prepare('INSERT INTO pagostutor (tutor_id,cantidad,fecha,nocheque,administrador_id)
                VALUES (:tutor_id,:cantidad,:fecha,:nocheque,:administrador_id)');
                $ingresarRegistro->bindParam(':tutor_id', $id);
                $ingresarRegistro->bindParam(':cantidad',$cantidad);
                $ingresarRegistro->bindParam(':fecha',$fecha);
                $ingresarRegistro->bindParam(':nocheque',$nocheque);
                $ingresarRegistro->bindParam(':administrador_id',$_SESSION['user_id']);
                $ingresarRegistro->execute();
    
                $actualizarTutor = $pdo->prepare('UPDATE tutores SET puntos=:puntos WHERE tutor_id=:tutor_id');
                $actualizarTutor->bindParam(':puntos',$puntos);
                $actualizarTutor->bindParam(':tutor_id',$id);
                if($actualizarTutor->execute()){
                    echo'<script>
                    alert("se ha hecho todo correctamente");
                    </script>';
                }
            }else{
                echo'<script>
                    alert("ingresa una cantidad valida");
                    </script>';
            }
        }
    }

    if(isset($_POST['hacerCheque'])){
        if(!empty($_POST['nocheque'])){
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $nocheque = $_POST['nocheque'];
            $trarePuntos = $pdo->prepare('SELECT puntos FROM tutores WHERE tutor_id=:tutor_id');
            $trarePuntos->bindParam(':tutor_id',$_SESSION['idTutor']);
            $trarePuntos->execute();
            $puntosAntiguos = 0;
            foreach($trarePuntos as $valor){
                $puntosAntiguos = $valor['puntos'];
            }

            $puntos = $puntosAntiguos-$_SESSION['cantidad'];

            $ingresarRegistro = $pdo->prepare('INSERT INTO pagostutor (tutor_id,cantidad,fecha,nocheque,administrador_id)
            VALUES (:tutor_id,:cantidad,:fecha,:nocheque,:administrador_id)');
            $ingresarRegistro->bindParam(':tutor_id', $_SESSION['idTutor']);
            $ingresarRegistro->bindParam(':cantidad',$_SESSION['cantidad']);
            $ingresarRegistro->bindParam(':fecha',$fecha);
            $ingresarRegistro->bindParam(':nocheque',$nocheque);
            $ingresarRegistro->bindParam(':administrador_id',$_SESSION['user_id']);
            $ingresarRegistro->execute();

            $actualizarTutor = $pdo->prepare('UPDATE tutores SET puntos=:puntos WHERE tutor_id=:tutor_id');
            $actualizarTutor->bindParam(':puntos',$puntos);
            $actualizarTutor->bindParam(':tutor_id',$_SESSION['idTutor']);
            if($actualizarTutor->execute()){
                echo'<script>
                alert("se ha hecho todo correctamente");
                </script>';
                $mensajeVisto = $pdo->query('UPDATE mensajes SET visto=1 WHERE mensajes_id='.$_SESSION['mensaje'].'');
            }
        }else{
            echo'<script>
            alert("ingresa el numero de cheque");
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
    <title>Tutorias</title>
</head>
<body>

    <nav class="nav nav-pills nav-fill">
        <a class="nav-item nav-link" href="homeadministrador.php">"Logo"</a>
        <a class="nav-item nav-link" href="materias.php">Materias</a>
        <a class="nav-item nav-link" href="pagartutor.php">Pagar a Tutor</a>
        <a class="nav-item nav-link" href="logout.php" onClick="logOut();">logout</a>
    </nav>
    <hr>
    <form action="pagartutor.php" method="post">
        <div>
            <label>correo del profesor</label>
            <input type="email" name="email">
            <input type="submit" value="buscar" name="buscar">
        </div>
    </form>
    <?php
    if(isset($_POST['buscar'])){
        echo $_POST['email'];
        $buscarTutores = $pdo->prepare('SELECT * FROM tutores WHERE email=:email');
        $buscarTutores->bindParam(':email',$_POST['email']);
        $buscarTutores->execute();

        foreach($buscarTutores as $valor){
            $valorAPagar = $valor['puntos']-($valor['puntos']*0.15);
            if($valor['puntos']<200){
                echo '<div class="alert alert-primary" role="alert">';
                echo 'este tutor no tiene los puntos suficientes para cobrar su pago';
                echo '</div>';
            }
            echo '<form action="pagartutor.php" method="post">';
            echo '<div>';
            echo '<h3>'.$valor['puntos'].' puntos ------ equivale a Q.'.$valorAPagar.'</h3>';
            echo '<h4>'.$valor['nombres'].', '.$valor['apellidos'].'</h4>';
            echo '<p>'.$valor['horasclase'].' horas de clase</p>';
            echo '</div>';
            echo '<div>';
            echo '<label>Numero de cheque que se le entrega</label>';
            echo '<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)">';
            echo '</div>';
            echo '<input type="text" value="'.$valor['tutor_id'].'" name="tutor_id" hidden="true">';
            echo '<input type="text"  name="cantidad" onkeypress="return valida2(event)" placeholder="cantidad a pagar">';
            if($valor['puntos']<200){
                echo '<input type="submit" value="cobrar su paga" disabled>';
            }else{
                echo '<input type="submit" value="cobrar su paga" name="pagar" disabled="disabled">';
            }

            echo '</form>';
            echo '';
            
        }
    }

    if(isset($_GET['id'])){
        echo '<hr>';
        $_SESSION['idTutor'] = $_GET['id'];
        $_SESSION['cantidad'] = $_GET['cantidad'];
        $_SESSION['direccion'] = $_GET['direccion'];
        $_SESSION['mensaje'] = $_GET['mensaje'];
        
        echo '<form action="pagartutor.php" method="post">';
        echo '<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)">';
        echo '<input type="text"  name="cantidad" value="'.$_SESSION['cantidad'].'" onkeypress="return valida2(event)" disabled>';
        echo '<input type="text" name="direccion" value="'.$_SESSION['direccion'].'" disabled>';
        echo '<input type="text" name="mensaje" value="'.$_SESSION['mensaje'].'" hidden="true">';
        echo '<input type="submit" value="hacer registro de cheque" name="hacerCheque">';
        echo '</form>';
    }

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

function valida2(e){
    tecla = (document.all) ? e.keyCode : e.which;

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    // Patron de entrada, en este caso solo acepta numeros
    patron =/[0-9-.]/;
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}

function validar(frm) {
  frm.pagar.disabled = false;
  for (i=0; i<3; i++)
    if (frm['txt'+i].value =='') return
  frm.pagar.disabled = true;
}
</script>
