<?php
    require_once 'conection.php';
    session_start();

    $traerUnHorario = $pdo->prepare('SELECT horario FROM tutores WHERE tutor_id=:tutor_id');
    $traerUnHorario->bindParam(':tutor_id',$_SESSION['user_id']);
    $traerUnHorario->execute();

    $cadenaUnHorario = array();
    foreach($traerUnHorario as $valor){
        $resultado = $valor['horario'];
        $cadenaUnHorario = explode(',',$resultado);
    }

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();
    
    $imagen = $pdo->prepare('SELECT * FROM tutores WHERE tutor_id=:tutor_id');
    $imagen->bindParam(':tutor_id', $_SESSION['user_id']);
    $imagen->execute();

    if(isset($_POST['subirImagen'])){
        
        $tipoImagen = $_FILES['imagen']['type'];
        $nombreImagen = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());
        $sizeImagen = $_FILES['imagen']['size'];

        if($sizeImagen<3000000){
            if($tipoImagen == "image/jpeg" || $tipoImagen == "image/jpg" || $tipoImagen == "image/png"){
                $lugarGuardado = $_SERVER['DOCUMENT_ROOT'].'/tutores/php/imagentutor/';
                switch($tipoImagen){
                    case "image/jpeg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpeg');
                        $nombreArreglado = $nombreImagen.'.jpeg';
                    break;
                    case "image/jpg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpg');
                        $nombreArreglado = $nombreImagen.'.jpg';
                    break;
                    case "image/png":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.png');
                        $nombreArreglado = $nombreImagen.'.png';
                    break;
                }
                
                $actualizarFoto = $pdo->prepare('UPDATE tutores SET imagenperfil=:imagenperfil WHERE tutor_id=:tutor_id');
                $actualizarFoto->bindParam(':imagenperfil', $nombreArreglado);
                $actualizarFoto->bindParam(':tutor_id', $_SESSION['user_id']);
                if($actualizarFoto->execute()){
                    header('Location: actualizartutor.php');
                }
                
               
            }else{
                echo 'solo se permite subir archivos de tipo .jpeg, .jpg y .png';
            }
        }else{
            echo 'la imagen des demasiado grande';
        }
    }

    if(isset($_POST['crearTutor'])){
        $traerTutor2 = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
        $traerTutor2->bindParam(':tutor_id', $_SESSION['user_id']);
        $traerTutor2->execute();

        while($row = $traerTutor2->fetch(PDO::FETCH_ASSOC)){
            if($row['datoscompletos'] == 0){
                if(!empty($_POST['nombres']) && !empty($_POST['apellidos']) && !empty($_POST['gradoAcademico']) && !empty($_POST['fechaNacimiento']) && !empty($_POST['edadAlumnos'])){

                    $porcentaje = $_POST['cobra']+($_POST['cobra']*0.15);

                    $actualizar = $pdo->prepare('UPDATE tutores SET nombres=:nombres, apellidos=:apellidos, gradoAcademico=:gradoAcademico, fechanacimiento=:fechanacimiento, edadAlumnos=:edadAlumnos, descripcion=:descripcion, facebook=:facebook, twitter=:twitter, instagram=:instagram, skype=:skype ,datoscompletos=:datoscompletos, cobra=:cobra WHERE tutor_id=:tutor_id');
                    $actualizar->bindParam(':nombres', $_POST['nombres']);
                    $actualizar->bindParam(':apellidos', $_POST['apellidos']);
                    $actualizar->bindParam(':gradoAcademico', $_POST['gradoAcademico']);
                    $actualizar->bindParam(':fechanacimiento', $_POST['fechaNacimiento']);
                    $actualizar->bindParam(':edadAlumnos', $_POST['edadAlumnos']);
                    $actualizar->bindParam(':descripcion', $_POST['descripcion']);
                    $actualizar->bindParam(':facebook', $_POST['facebook']);
                    $actualizar->bindParam(':twitter', $_POST['twitter']);
                    $actualizar->bindParam(':instagram', $_POST['instagram']);
                    $actualizar->bindParam(':skype', $_POST['skype']);
                    $actualizar->bindParam(':cobra',$porcentaje);
                    $actualizar->bindParam(':tutor_id', $_SESSION['user_id']);
                    $datosCompletos = 1;
                    $actualizar->bindParam(':datoscompletos', $datosCompletos);
                    if($actualizar->execute()){
                        header('Location: actualizartutor.php');
                    }
                }else{
                    echo 'no se han igresado todos los datos necesarios';
                }
            }else{
                if(!empty($_POST['nombres']) || !empty($_POST['apellidos']) || !empty($_POST['gradoAcademico']) || !empty($_POST['fechaNacimiento']) || !empty($_POST['establecimiento'])){

                    $actualizar = $pdo->prepare('UPDATE tutores SET nombres=:nombres, apellidos=:apellidos, gradoAcademico=:gradoAcademico, fechanacimiento=:fechanacimiento, edadAlumnos=:edadAlumnos, descripcion=:descripcion, facebook=:facebook, twitter=:twitter, instagram=:instagram, skype=:skype WHERE tutor_id=:tutor_id');
                    $actualizar->bindParam(':nombres', $_POST['nombres']);
                    $actualizar->bindParam(':apellidos', $_POST['apellidos']);
                    $actualizar->bindParam(':gradoAcademico', $_POST['gradoAcademico']);
                    $actualizar->bindParam(':fechanacimiento', $_POST['fechaNacimiento']);
                    $actualizar->bindParam(':edadAlumnos', $_POST['edadAlumnos']);
                    $actualizar->bindParam(':descripcion', $_POST['descripcion']);
                    $actualizar->bindParam(':facebook', $_POST['facebook']);
                    $actualizar->bindParam(':twitter', $_POST['twitter']);
                    $actualizar->bindParam(':instagram', $_POST['instagram']);
                    $actualizar->bindParam(':skype', $_POST['skype']);
                    $actualizar->bindParam(':tutor_id', $_SESSION['user_id']);
                    if($actualizar->execute()){
                        header('Location: actualizartutor.php');
                    }
                }else{
                    echo 'no se han hecho cambios';
                }
            }
        }

    }


    if(isset($_POST['enviarhorario'])){
        $contador = count($_POST['horario']);
        if($contador>0){
            $base = "";
            $vuelta = 0;
            foreach($_POST['horario'] as $valor){
                $vuelta +=1; 
                if($contador>1){
                    if($contador==$vuelta){
                        $base .= $valor;
                    }else{
                        $base .= $valor.",";
                    }
                }else{
                    $base = $valor;
                }
            }
        }

        $ingresarHorario = $pdo->prepare('UPDATE tutores SET horario=:horario WHERE tutor_id = :tutor_id');
        $ingresarHorario->bindParam(':horario', $base);
        $ingresarHorario->bindParam(':tutor_id', $_SESSION['user_id']);
        if($ingresarHorario->execute()){
            header('Location: actualizartutor.php');
        }

    }


    if(isset($_POST['datoguardar'])){
        if(!empty($_POST['datoguardar'])){
            $todasCordenadas = $_POST['datoguardar'];
            $coordenadas = explode(":",$todasCordenadas);
            $cantidadMarker = count($coordenadas)-1;
            for($i=0;$i<$cantidadMarker;$i++){
                $latLogRan = $coordenadas[$i];
                $separarCoordenadasRango = explode("/",$latLogRan);
                $latLog = $separarCoordenadasRango[0];
                $rango = $separarCoordenadasRango[1];
                $resultado = explode(",",$latLog);
                $latitud = $resultado[0];
                $longitud = $resultado[1];
                $ingresarCoordenadas = $pdo->prepare('INSERT INTO lugartutorias (tutor_id,longitud,latitud,rango) values (:tutor_id,:longitud,:latitud,:rango)');
                $ingresarCoordenadas->bindParam(':longitud', $longitud);
                $ingresarCoordenadas->bindParam(':latitud', $latitud);
                $ingresarCoordenadas->bindParam(':rango',$rango);
                $ingresarCoordenadas->bindParam(':tutor_id', $_SESSION['user_id']);
                $ingresarCoordenadas->execute();
            }
        }
    }


    //consultas para traer todos los marker del tutor
    $traerMarcadores = $pdo->prepare('SELECT longitud, latitud,rango,lugartutorias_id FROM lugartutorias WHERE tutor_id=:tutor_id');
    $traerMarcadores->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerMarcadores->execute();

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


    //borrar marker de db
    if(isset($_POST['dato'])){
        $eliminarMarker = $pdo->prepare('DELETE FROM lugartutorias WHERE lugartutorias_id=:lugartutorias_id');
        $eliminarMarker->bindParam(':lugartutorias_id', $_POST['dato']);
        $eliminarMarker->execute();
    }

    if(isset($_POST['datoUpdate'])){
        $separar = explode(":",$_POST['datoUpdate']);

        $actualizarMarker = $pdo->prepare('UPDATE lugartutorias SET rango=:rango WHERE lugartutorias_id=:lugartutorias_id');
        $actualizarMarker->bindParam(':rango',$separar[0]);
        $actualizarMarker->bindParam(':lugartutorias_id',$separar[1]);
        $actualizarMarker->execute();
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
    <script type="text/javascript" src="jquery-1.10.2.min.js"></script>
    <title>Mis datos</title>
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


    <?php
        while($row = $imagen->fetch(PDO::FETCH_ASSOC)){
            if(empty($row['imagenperfil'])){
                echo '<img style="height: 200px;"  src="./imagentutor/sinfoto.png">';
            }else{
                echo '<img style="height: 200px;"  src="./imagentutor/'.$row['imagenperfil'].'">';
            }
        }
    ?>
    
<div class="container-fluid">
    <div class="row">
        <div class="col-sm">
            <form action="actualizartutor.php" method="post" enctype="multipart/form-data">
                <label >Escoge tu imagen de perfil</label>
                <div>
                    <input type="file" name="imagen">
                    <input type="submit" value="subir" name="subirImagen">
                </div>
                <br>
            </form>
        
        <?php
            $default = $pdo->prepare('SELECT * FROM tutores WHERE tutor_id=:tutor_id');
            $default->bindParam(':tutor_id', $_SESSION['user_id']);
            $default->execute();
            while($row = $default->fetch(PDO::FETCH_ASSOC)){
                $restarPorcentaje = $row['cobra']-($row['cobra']*0.15);
                echo '<form action="actualizartutor.php" method="post" class="col-lg-5">';
                echo '<div>';
                echo '<label>Cuanto vas a cobrar por hora de tutoria?</label>';
                echo '<input type="text" name="cobra" value="'.$restarPorcentaje.'">';
                echo '<p>Nota: a la cantidad que cobres se le sumara un porcentaje que sera lo que cobre la pagina</p>';
                echo '</div>';
                echo '<div>';
                echo '<label>Nombres</label>';
                echo '<input type="text" name="nombres" value="'.$row['nombres'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Apellidos</label>';
                echo '<input type="text" name="apellidos" value="'.$row['apellidos'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Descripcion(opcional, esto lo podra ver cualquier pupilo)</label>';
                echo '<input type="text" name="descripcion" value="'.$row['descripcion'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Grado academico</label>';
                echo '<select name="gradoAcademico">';
                if($row['datoscompletos'] == 1){
                    echo '<option selected="true" disabled="disabled">'.$row['gradoAcademico'].'</option>';
                }
                echo '<option value="primaria">Primaria</option>';
                echo '<option value="basicos">Basicos</option>';
                echo '<option value="diversificado">Diversificado</option>';
                echo '<option value="maestria">Maestria</option>';
                echo '<option value="doctorado">Doctorado</option>';
                echo '</select>';
                echo '</div>';
                echo '<div>';
                echo '<label>Con que categoria se familiariza mas para dar clases?</label>';
                echo '<select name="edadAlumnos">';
                if($row['datoscompletos'] == 1){
                    echo '<option selected="true" disabled="disabled">'.$row['edadAlumnos'].'</option>';
                }
                echo '<option value="4-11">de 4 a 11 (niños)</option>';
                echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
                echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
                echo '<option value="36-50">de 36 a 50 (adultos)</option>';
                echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
                echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
                echo '</select>';
                echo '</div>';
                echo '<div>';
                echo '<label>fecha de nacimiento</label>';
                if($row['datoscompletos'] == 0){
                    echo '<input type="date" name="fechaNacimiento" value="2000-01-01">';
                }else{
                    echo '<input type="date" name="fechaNacimiento" value="'.$row['fechanacimiento'].'">';
                }
                echo '</div>';
                echo '<div>';
                echo '<label>Facebook(opcional)</label>';
                echo '<input type="text" name="facebook" value="'.$row['facebook'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Twitter(Opcional)</label>';
                echo '<input type="text" name="twitter" value="'.$row['twitter'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Instagram(Opcional)</label>';
                echo '<input type="text" name="instagram" value="'.$row['instagram'].'">';
                echo '</div>';
                echo '<div>';
                echo '<label>Skype(Opciona)</label>';
                echo '<input type="text" name="skype" value="'.$row['skype'].'">';
                echo '</div>';
                echo '<div>';
                echo '<input type="submit" value="enviar" name="crearTutor">';
                echo '</div>';
                echo '</form>';
            }
        ?>
        </div>
        <div class="col-sm">
        <form action="actualizartutor.php" method="post">
        <h1>describe tu horario por dia</h1>
        <div>
        <?php
            $traerHorario = $pdo->prepare('SELECT horario FROM tutores WHERE tutor_id=:tutor_id');
            $traerHorario->bindParam(':tutor_id', $_SESSION['user_id']);
            $traerHorario->execute();

            $count = 0;
            $cadena = array();
            foreach($traerHorario as $valor){
                $resultado = $valor['horario'];
                $cadena = explode(',',$resultado);

                $count = count($cadena);
            }

            $contar = 0;
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
                        echo '<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'" checked>';
                    }else{
                        echo '<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'">';
                    }
                }
                echo '</div>'; 
            }
        ?>
            

        <input type="submit" value="Publicar horario" name="enviarhorario">
    </form>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm">
        <hr>
        <h2>lugares donde puedes dar clases</h2>
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
                    
                    //al hacer clic en alguna parte del mapa
                    map.addListener('click', function(e){
                        
                        //crear marker
                        var marker = new google.maps.Marker({
                        position: e.latLng,
                        map: map
                        });
                        
                        var mLatLang = marker.getPosition().toUrlValue();
                        bounds.extend(e.latLng);
                        map.setCenter(e.latLng);
                        map.fitBounds(bounds);
                        map.panTo(e.latLng);
                        
                                            

                        //hacer el div 
                        var contentString = '<div id="content">'+
                            '<h4>A cuantos kilometros a la redonda?</h4>'+
                            '<div id="bodyContent">'+
                            '<input type="number" value="1" id="kilometros" name="kilometros" onkeydown="javascript: return event.keyCode == 69 ? false : true">'+
                            '<input type="button" name="activar" id="activar" value="aceptar">'+
                            '<input type="text" id="crear" hidden="true">'+
                            '<input type="button" name="remove" id="remove" value="quitar">'+
                            '</div>'+
                            '</div>';
                        //crear nuevo popup y abrirlo
                        var popup = new google.maps.InfoWindow({
                            content: contentString
                        });

                        popup.open(map, marker);
                        
                        google.maps.event.addListener(marker,'click',function(){
                            popup.open(map, marker);
                        })
                        
                        

                        google.maps.event.addListener(popup, 'domready', function(){  //hacer que leea dentro del dom del popup
                            
                            //borrar marcador precargado
                            var botonRemove = document.getElementById('remove')
                            botonRemove.onclick = function(){
                                marker.setMap(null)
                            }  

                            //crear circulo
                            var boton = document.getElementById('activar')
                            boton.onclick = function(){

                                var kilometros = document.getElementById('kilometros').value;
                                var aceptarInput = document.getElementById('crear');
                                aceptarInput.value = aceptarInput.value+mLatLang+"/"+kilometros+":";
                                
                                 
                                var circle = new google.maps.Circle({ 
                                strokeColor: '#FF0000',
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                fillColor: '#FF0000',
                                fillOpacity: 0.35,
                                map: map,
                                center: e.latLng,
                                radius: parseInt(kilometros) * 1000
                                });
                                
                                

                                var dato = $('#crear').val();
                                $.ajax({
                                data: {"datoguardar" : dato},
                                url: "actualizartutor.php",
                                type: "post",
                                async: true,
                                success:  function (response) {
                                    location.href='actualizartutor.php';
                                }
                                });

                                

                                console.log(mLatLang);

                                popup.close()

                                
    
                            }

                            
                            

                        });
                        
                    });
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

                    var contentString = '<div id="content">'+
                        '<h4>A cuantos kilometros a la redonda?</h4>'+
                        '<div id="bodyContent">'+
                        '<input type="number" id="kilometros" name="kilometros" onkeydown="javascript: return event.keyCode == 69 ? false : true">'+
                        '<input type="button" name="activar" id="actualizar" value="actualizar">'+
                        '<input type="text" id="update" name="actualizar" hidden="true">'+
                        '<input type="button" name="remove" id="remove" value="borrar marker">'+
                        '<input type="text" id="borrar" name="borrar" hidden="true">'+
                        '</div>'+
                        '</div>';


                    
                    //crear nuevo popup y abrirlo
                    var popup = new google.maps.InfoWindow({
                        content: contentString
                    });

                    google.maps.event.addListener(marker,'click',function(){
                        popup.open(map, marker);

                        google.maps.event.addListener(popup, 'domready', function(){
                            //cargar rango guardado
                            var cargado = document.getElementById('kilometros')
                            cargado.value = ran;


                            //actualizar
                            var actualizar = document.getElementById('actualizar')
                            actualizar.onclick = function(){
                                
                                var kilometros = document.getElementById('kilometros').value;
                                var actualizarInput = document.getElementById('update')
                                actualizarInput.value = kilometros + ":" + id

                                var dato = $('#update').val();
                                    $.ajax({
                                    data: {"datoUpdate" : dato},
                                    url: "actualizartutor.php",
                                    type: "post",
                                    async: true,
                                    success:  function (response) {
                                    }
                                });


                                circle.setRadius(parseInt(kilometros)*1000)
  
                            }
                            
                            //borrar
                            var botonRemove = document.getElementById('remove')
                            botonRemove.onclick = function(){
                                var borrarInput = document.getElementById('borrar')
                                borrarInput.value = id
                                
                                var dato = $('#borrar').val();
                                $.ajax({
                                data: {"dato" : dato},
                                url: "actualizartutor.php",
                                type: "post",
                                async: true,
                                success:  function (response) {
                                }
                                });
                                
                                marker.setMap(null)

                                circle.setRadius(0)

                            } 
                        
                        })

                        
                    })
                }

                
            </script>
        </div>
    </div>
</div>

<script type="text/javascript" src="auth.js"></script>

</body>
</html>
   