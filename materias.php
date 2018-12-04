<?php   
    require_once 'conection.php';
    session_start();

    $traerMaterias = $pdo->query('SELECT * FROM materias');

    if(isset($_POST['actualizar'])){
        $_SESSION['materiaId'] = $_POST['id'];
        header('location: actualizarmateria.php?id='.$_POST['id']);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>tutorias</title>
</head>
<body>
    
    <?php
    foreach($traerMaterias as $valor){

        echo '<form action="materias.php" method="post">';
        echo '<div>';
        echo '<img src="./imagenmateria/'.$valor['imagenmateria'].'" style="height: 200px;">';
        echo '<h3>'. $valor['nombre'] .'</h3>';
        echo '<p>'. $valor['descripcion'] .'</p>';
        echo '<input type="text" name="id" value="'.$valor['materia_id'].'" hidden="true">';
        echo '<button name="actualizar">actualizar</button>';
        echo '<hr>';
        echo '</div>';
        echo '</form>';
    }
    ?>
    

</body>
</html>