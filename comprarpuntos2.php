<?php
    require_once 'conection.php';
    session_start();

    $key ="3213118984651231819815661";
    $token = str_shuffle("abcdefghijklmno14725".uniqid());
    $time = time("U");
    $cantidad = $_POST['amount'];

    $hash = MD5($token."|".$cantidad."|".$time."|".$key);

    $_SESSION['orderid']= $token;
    $_SESSION['amount']= $cantidad;
    $_SESSION['time']=$time;

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
    <title>tutorias</title>
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



    <FORM name="CredomaticPost" method="post" action="" />
        <INPUT type="text" name="username" value="nombre usaurio" hidden="true"/>
        <INPUT type="text" name="type" value="auth" hidden="true"/>
        <INPUT type="text" name="key_id" value="key 49338953" hidden="true"/>
        <INPUT type="text" name="hash" value="<?php echo $hash;?>" />
        <INPUT type="text" name="time" value="<?php echo $time;?>"/>
        <INPUT type="text" name="amount" value="<?php echo $_POST['amount'];?>"/>
        <INPUT type="text" name="orderid" value="<?php echo $token;?>"/>
        <INPUT type="text" name="ccnumber" value="<?php echo $_POST['ccnumber'];?>"/>
        <INPUT type="text" name="ccexp" value="<?php echo $_POST['ccexp'];?>"/>
        <INPUT type="text" name="cvv" value="<?php echo $_POST['cvv'];?>"/>
        <INPUT type="text" name="redirect" value="respuesta.php" hidden="true"/>
        <INPUT type="button" value="Enviar Transacción" name="enviar" onClick="validar(this.form)"/>
    </FORM>
    <script type="text/javascript" src="auth.js"></script>
</body>
</html>

<script>
function validar(frm) {
  frm.enviar.disabled = true; 
}
</script>