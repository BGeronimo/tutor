<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();
    $porcentaje = 0.15;
    echo $_SESSION['user_id'];
    $traerAlumno = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
    $traerAlumno->bindParam(':alumno_id', $_SESSION['user_id']);
    $traerAlumno->execute();


    if(isset($_POST['elegir'])){
        $sesion = 1;
        $crearVinculo = $pdo->query('INSERT INTO calificaciones (tutor_id, alumno_id,sesion) VALUES ('.$_POST['tutor_id'].','.$_SESSION['user_id'].','.$sesion.')');

        header('Location: elegirtutor.php');
    }

    //consultas para traer todos los marker del tutor
    if(isset($_GET['id'])){
        $traerMarcadores = $pdo->query('SELECT longitud, latitud,rango,lugartutorias_id FROM lugartutorias WHERE tutor_id='.$_GET['id'].'');
        
        $arrayLat = array();
        $arrayLong = array();
        $arrayRan = array();
        $arrayId = array();
        foreach($traerMarcadores as $valor){
            array_push($arrayLat,$valor['latitud']);
            array_push($arrayLong,$valor['longitud']);
            array_push($arrayRan,$valor['rango']);
            array_push($arrayId,$valor['lugartutorias_id']);
        }
        $countLat = count($arrayLat);
        $countLong = count($arrayLong);
    
    }
    

    if(isset($_POST['enviarpago'])){
        $tutorid = $_POST['idtutor'];
        $userid = $_SESSION['user_id'];
        $fecha = zonaHoraria("Y-m-d H:i:s");
        $traerDatos = $pdo->prepare('SELECT horasclase, puntos FROM bancopuntos 
        WHERE bancopuntos.tutor_id=:tutor_id AND bancopuntos.alumno_id=:alumno_id AND bancopuntos.cobropuntos=0');
        $traerDatos->bindParam(':tutor_id',$tutorid);
        $traerDatos->bindParam(':alumno_id',$userid);
        $traerDatos->execute();
        $horas = 0;
        $puntos = 0; 
        foreach($traerDatos as $valor){
                $puntos = $valor['puntos'];
                $horas = $valor['horasclase'];
        }

        $traerDatosTutor = $pdo->query('SELECT horasclase,puntos FROM tutores WHERE tutor_id='.$tutorid.'');
        $horasAntiguas = 0;
        $puntosAntiguos = 0;
        foreach($traerDatosTutor as $valor){
            $horasAntiguas = $valor['horasclase'];
            $puntosAntiguos = $valor['puntos'];
        }
        $horasActualizadas = $horas+$horasAntiguas;
        $puntosActualizados = $puntosAntiguos+($puntos-($puntos*$porcentaje));

        $sesion = 0;
        $actualizarSesion = $pdo->prepare('UPDATE calificaciones SET sesion=:sesion WHERE tutor_id=:tutor_id AND alumno_id=:alumno_id AND sesion=1');
        $actualizarSesion->bindParam(':sesion',$sesion);
        $actualizarSesion->bindParam(':tutor_id',$tutorid);
        $actualizarSesion->bindParam(':alumno_id',$userid);
        $actualizarSesion->execute();

        $cobropuntos = 1;
        $hacerPago = $pdo->prepare('UPDATE bancopuntos SET fechafin=:fechafin,cobropuntos=:cobropuntos 
        WHERE bancopuntos.tutor_id=:tutor_id AND bancopuntos.alumno_id=:alumno_id AND bancopuntos.cobropuntos=0');
        $hacerPago->bindParam(':fechafin',$fecha);
        $hacerPago->bindParam(':cobropuntos',$cobropuntos);
        $hacerPago->bindParam(':tutor_id',$tutorid);
        $hacerPago->bindParam(':alumno_id',$userid);
        if($hacerPago->execute()){
            $actualizarTutor = $pdo->prepare('UPDATE tutores SET horasclase=:horasclase,puntos=:puntos WHERE tutor_id=:tutor_id');
            $actualizarTutor->bindParam(':horasclase',$horasActualizadas);
            $actualizarTutor->bindParam(':puntos',$puntosActualizados);
            $actualizarTutor->bindParam(':tutor_id',$tutorid);
            if($actualizarTutor->execute()){
                header('Location: homealumno.php');   
            }
        }
    }

    if(isset($_POST['declinar'])){
        //emisor
        $emisor = "Alumno|".$_SESSION['user_id'];


        //receptor
        $receptor = "Tutor|".$_POST['idtutor'];

        //texto

        $datosDeclinar = $pdo->prepare('SELECT alumnos.nombres as nombres, alumnos.apellidos as apellidos, alumnos.email as email,bancopuntos.puntos as puntos, bancopuntos.bancopuntos_id as id 
        FROM bancopuntos,alumnos WHERE bancopuntos.tutor_id=:tutor_id AND bancopuntos.alumno_id=:alumno_id AND bancopuntos.cobropuntos=0 AND bancopuntos.alumno_id=alumnos.alumno_id');
        $datosDeclinar->bindParam(':tutor_id',$_POST['idtutor']);
        $datosDeclinar->bindParam(':alumno_id',$_SESSION['user_id']);
        $datosDeclinar->execute();

        $nombres = "";
        $apellidos = "";
        $email = "";
        $puntos = 0;
        $id = "";
        foreach($datosDeclinar as $valor){
            $nombres = $valor['nombres'];
            $apellidos = $valor['apellidos'];
            $email = $valor['email'];
            $puntos = $valor['puntos'];
            $id = $valor['id']; 
        }

        $mensajeCompleto = "1|".$nombres.", ".$apellidos."|".$email."|".$puntos."|".$id;

        $mandarMensaje = $pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
        $mandarMensaje->bindParam(':texto',$mensajeCompleto);
        $mandarMensaje->bindParam(':emisor',$emisor);
        $mandarMensaje->bindParam(':receptor',$receptor);
        if($mandarMensaje->execute()){
            echo'<script type="text/javascript">
            alert("tu declinacion ha sido procesada, ahora espera la respuesta del tutor.");
            location.href = "elegirtutor.php";
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
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBqKKkmhYNNqy_HwDeADL5vtPvtIp20AmE&sensor=false"></script>
    <title>Document</title>
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
    <?php
    if(isset($_GET['id'])){
        $opcionBoton = $pdo->prepare('SELECT COUNT( alumno_id) AS numero, alumno_id as alumno, sesion as activo 
        FROM calificaciones  WHERE tutor_id = '.$_GET['id'].' AND alumno_id=:alumno_id AND sesion = 1');
        $opcionBoton->bindParam(':alumno_id',$_SESSION['user_id']);
        $opcionBoton->execute();

        $traerCalificacion = $pdo->query('SELECT COUNT(calificacion) as numero, AVG(calificacion) as promedio FROM calificaciones WHERE tutor_id = '.$_GET['id'].' AND calificacion!=0');

        $vermasTutor = $pdo->query('SELECT * FROM tutores WHERE tutor_id = '.$_GET['id'].'');

        $horario = $pdo->query('SELECT horario AS h FROM tutores where tutor_id = '.$_GET['id'].'');

        $traerMateriaTutor = $pdo->query('SELECT materias.nombre as nombre FROM materias, materiatutor WHERE materiatutor.materia_id=materias.materia_id AND materiatutor.tutor_id='.$_GET['id'].'');

        while($row = $vermasTutor->fetch(PDO::FETCH_ASSOC)){
            echo '<form action="perfiltutor.php" method="post">';
            echo '<div class="jumbotron">';
            echo '<h4>materias que enseña</h4>';
            foreach($traerMateriaTutor as $valor){
                echo $valor['nombre'].', ';
            }
            echo '<br>';
            echo '<img style="height: 200px;"  src="./imagentutor/'.$row['imagenperfil'].'">';
            echo '<h1>'.$row['nombres'].'</h1>';
            echo '<p>'.$row['descripcion'].'</p>';
            echo '<p>'.$row['gradoAcademico'].'</p>';
            echo '<p>'.$row['edadAlumnos'].'</p>';
            echo '<p>redes sociales</p>';
            echo '<p>'.$row['facebook'].'</p>';
            echo '<p>'.$row['twitter'].'</p>';
            echo '<p>'.$row['instagram'].'</p>';
            echo '<p>'.$row['skype'].'</p>';
            echo '<h1>Q.'.$row['cobra'].' por hora</h1>';
            echo '<input type="text" name="tutor_id" value="'.$row['tutor_id'].'" hidden="true">';
            echo '<h4>Horas de clase:</h4>';
            echo '<p>'.$row['horasclase'].' horas</p>';
            echo '<br>';
            while($row2 = $opcionBoton->fetch(PDO::FETCH_ASSOC)){

                foreach($traerCalificacion as $valor){
                    if($valor['numero']==0){
                        echo '<p>no tiene calificaiones</p>';
                    }else{
                        echo '<p> calificacion: '.$valor['promedio'].'</p>';
                    }
                    
                }
                
                if($row2['numero']==1){
                    echo '<div class="alert alert-primary" role="alert">';
                    echo 'ya tienes una sesion reservada con este tutor';
                    echo '</div>';
                    echo '<div>';
                    echo '<input type="text" name="idtutor" value="'.$row['tutor_id'].'" hidden="true">';
                    echo '<input type="submit" name="enviarpago" value="hacer pago directo">';
                    echo '<input type="submit" name="declinar" value="cancelar tutoria">';
                    echo '<p>el cancelar la tutoria requiere el acuerdo de ambas partes, al tutor se le notificara de tu decision</p>';
                    echo '</div>';

                }else{
                    echo '<a href="adquirirtutoria.php?id='.$row['tutor_id'].'">Adquirir tutoria</a>';
                }
                
            }
            $_SESSION['tutorId'] = $row['tutor_id'];

            echo '</div>';
            echo '</form>';

        }

        $traerUnHorario = $pdo->prepare('SELECT horario FROM tutores WHERE tutor_id=:tutor_id');
        $traerUnHorario->bindParam(':tutor_id',$_GET['id']);
        $traerUnHorario->execute();
    
        $cadenaUnHorario = array();
        foreach($traerUnHorario as $valor){
            $resultado = $valor['horario'];
            $cadenaUnHorario = explode(',',$resultado);
        }

            
        for($i=0; $i<=12; $i++){
            $hora = $i +6;
            echo '<div>';
            echo '<label>'.$hora.'hrs</label>';

            for($o=0; $o<=6; $o++){
                switch($o){
                    case 0:
                        $dia = "L";
                    break;
                    case 1:
                        $dia = "M";
                    break;
                    case 2:
                        $dia = "MI";
                    break;
                    case 3:
                        $dia = "J";
                    break;
                    case 4:
                        $dia = "V";
                    break;
                    case 5:
                        $dia = "S";
                    break;
                    case 6:
                        $dia = "D";
                    break;
                }

                $diaHora = $dia.'|'.$hora;
                if(in_array($diaHora,$cadenaUnHorario)){
                    echo '<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'" checked disabled>';
                }else{
                    echo '<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'" disabled>';
                }
            }
            echo '</div>'; 
        }
    } 

    ?>

    <div id="map-canvas" style="height:500px;"></div>
    <script>
        var map;
        var markers = new Array();
        var bounds = new google.maps.LatLngBounds();

        function initialize() {
            //inizializzo la mappa
            var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(15.6356088,-89.8988087),
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE
                },
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            


            <?php
            for ($k = 0; $k < $countLat; ++$k){ 
                $kk = $k + 1; ?> 
                traerMarker(map, bounds,'<?php echo $arrayLat[$k]; ?>', '<?php echo $arrayLong[$k]; ?>','<?php echo $arrayRan[$k]; ?>','<?php echo $arrayId[$k]; ?>'); <?php
            } ?>                        


        }
        google.maps.event.addDomListener(window, 'load', initialize);


        //colocar los marker que el usuario habia guardado
        function traerMarker(map, bounds, lat, lon, ran,id){
        var position = new google.maps.LatLng(
            lat, lon
        );
        var marker = new google.maps.Marker({
            position: position,
            map: map
        });
        markers.push(marker);
        bounds.extend(position);
        map.setCenter(position);
        map.fitBounds(bounds);
        
        var circle = new google.maps.Circle({ 
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            map: map,
            center: position,
            radius: ran * 1000
        });

    }
    </script>

    <hr>
    <h1>comentarios: </h1>
    <?php
        if(isset($_GET['id'])){
            $traerComentarios = $pdo->query('SELECT alumnos.nombres, alumnos.imagenperfil,calificaciones.comentario FROM alumnos,calificaciones 
            WHERE calificaciones.tutor_id = '.$_GET['id'].' AND calificaciones.alumno_id=alumnos.alumno_id AND calificaciones.visible=1');

            foreach($traerComentarios as $valor){
                echo '<div>';
                echo '<img style="height: 50px;" src="./imagenalumno/'.$valor['imagenperfil'].'">';
                echo '<h3>'.$valor['nombres'].'</h3>';
                echo '<p>'.$valor['comentario'].'</p>';
                echo '<hr>';
                echo '</div>';
            }

        }
    
    ?>

<script type="text/javascript" src="auth.js"></script>

            
            
            
            
        

</body>
</html>