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
				$lugarGuardado = 'imagentutor/';
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
				
				$traerFotoActual = $pdo->query('SELECT imagenperfil FROM tutores WHERE tutor_id='.$_SESSION['user_id'].'');
				$imagenAntigua = "";
				foreach($traerFotoActual as $valor){
					$imagenAntigua = $valor['imagenperfil'];
				}
                
                $actualizarFoto = $pdo->prepare('UPDATE tutores SET imagenperfil=:imagenperfil WHERE tutor_id=:tutor_id');
                $actualizarFoto->bindParam(':imagenperfil', $nombreArreglado);
                $actualizarFoto->bindParam(':tutor_id', $_SESSION['user_id']);
                if($actualizarFoto->execute()){
					unlink('./imagentutor/'.$imagenAntigua);
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
<html lang="zxx" class="no-js">
<head>
	<!-- Mobile Specific Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Favicon-->
	<link rel="shortcut icon" href="img/fav.png">
	<!-- Author Meta -->
	<meta name="author" content="colorlib">
	<!-- Meta Description -->
	<meta name="description" content="">
	<!-- Meta Keyword -->
	<meta name="keywords" content="">
	<!-- meta character set -->
	<meta charset="UTF-8">
	<!-- Site Title -->
	<title>Mi Cuenta Tutor | Tutoeri</title>

	<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
		<!--
		CSS
		============================================= -->
		<link rel="stylesheet" href="css/linearicons.css">
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<link rel="stylesheet" href="css/bootstrap.css">
		<link rel="stylesheet" href="css/magnific-popup.css">
		<link rel="stylesheet" href="css/jquery-ui.css">				
		<link rel="stylesheet" href="css/nice-select.css">							
		<link rel="stylesheet" href="css/animate.min.css">
		<link rel="stylesheet" href="css/owl.carousel.css">				
		<link rel="stylesheet" href="css/main.css">
	</head>
		<body>	
		<header id="header">
			<?php
				while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
					if($row['datoscompletos'] == 0){
						echo '
						<div class="header-top">
							<div class="container">
								<div class="row align-items-center">
									<div class="col-lg-6 col-sm-6 col-6 header-top-left">
										<ul>
											<li><a href="">['.$row['puntos'].'] Puntos</a></li>
										</ul>			
									</div>
									<div class="col-lg-6 col-sm-6 col-6 header-top-right">
										<ul>
											<li>
												<a href="pedircheque.php">Pedir Cheque</a>
											</li>
										</ul>
									</div>
								</div>			  					
							</div>
						</div>

					<div class="container main-menu">
						<div class="row align-items-center justify-content-between d-flex">
							<div id="logo">
								<a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
							</div>
							<nav id="nav-menu-container">
								<ul class="nav-menu">
								<li class="activo"><a href="homeTutor.html">Inicio</a></li>
								<li><a href="actualizarTutor.html">Mi Cuenta</a></li>		          					          		          
								<li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
								</ul>
							</nav><!-- #nav-menu-container -->					      		  
						</div>
					</div>
						
						';
					}else{
						echo '
						<div class="header-top">
						<div class="container">
						  <div class="row align-items-center">
							  <div class="col-lg-6 col-sm-6 col-6 header-top-left">
								  <ul>
									  <li><a href="#">['.$row['puntos'].'] Puntos</a></li>
								  </ul>			
							  </div>
							  <div class="col-lg-6 col-sm-6 col-6 header-top-right">
								<ul>
									<li>
										<a href="pedircheque.php">Pedir Cheque</a>
									</li>
								</ul>
							  </div>
						  </div>			  					
						</div>
					</div>
	
					<div class="container main-menu">
						<div class="row align-items-center justify-content-between d-flex">
						  <div id="logo">
							<a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
						  </div>
						  <nav id="nav-menu-container">
							<ul class="nav-menu">
							  <li><a href="hometutor.php">Inicio</a></li>
							  <li class="activo"><a href="actualizartutor.php">Mi Cuenta</a></li>
							  <li><a href="materiastutor.php">Materias</a></li>
							  <li><a href="pupilos.php">Pupilos</a></li>
							  <li><a href="cobropuntos.php">Cobrar Puntos</a></li>			          					          		          
							  <li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
							</ul>
						  </nav><!-- #nav-menu-container -->					      		  
						</div>
					</div>';
						$email = $row['email'];

					}
				}
			?>
				
			</header><!-- #header -->
			  
			<!-- start banner Area -->
			<section class="about-banner relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Mi Cuenta				
							</h1>	
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">
				<div class="container">
		            <div class="row d-flex justify-content-center">
		                <div class="menu-content pb-40 col-lg-8">
		                    <div class="title text-center">
		                        <h1 class="mb-10">Esta es tu cuenta</h1>
		                        <p>Puedes modificar la informaciónn cuando lo desees...</p>
		                    </div>
		                </div>
		            </div>						
					<div class="row">
						<div class="col-lg-12">
							<div class="single-destinations">
								<!--<div class="thumb">
									<img src="img/hotels/d1.jpg" alt="">
								</div>-->
								<div class="perfil text-center">
									<h4 class=" text-center ">
										<span class="text-center">Foto de perfil</span>                              		
									</h4>
									<p style="color: black;">
										Elige una imagen para foto de perfl.
									</p>
									<div>
										<div class="sidebar-widgets" style="padding-bottom: 2px;">
											<div class="widget-wrap">
												<div class="single-sidebar-widget user-info-widget">
													<?php
														while($row = $imagen->fetch(PDO::FETCH_ASSOC)){
															if(empty($row['imagenperfil'])){
																echo '<img src="img/profile.png">';
															}else{
																echo '<img class="imgPerfil"  src="./imagentutor/'.$row['imagenperfil'].'">';
															}
														}
													?>
													<br>
													<form action="actualizartutor.php" method="post" enctype="multipart/form-data">
														<div class="form-group text-center">
														  <label for="exampleFormControlFile1"></label>
														  <input type="file" name="imagen" class="" id="exampleFormControlFile1" style="margin-top: 35px; border: 2px dashed #3b9f97; border-radius: 3px;">
														</div>
														<div class="text-center">
															<input class="price-btn col-lg-6" type="submit" value="Guardar Imagen" name="subirImagen">
														</div>
													</form>
												</div>
											</div>
										</div>		
									</div>
									
								</div>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="single-destinations">
								<div class="perfil text-center">
									<h4 class="text-center">
										<span class="text-center">información</span>                              		
									</h4>
									<p style="color: black;">
										Puedes actualizar esta información en cualquier momento.
									</p>
									<div>
										<div class="sidebar-widgets" style="padding-bottom: 10px;">
											<div class="widget-wrap" style="padding: 0px 0px;">
												<div class="single-sidebar-widget user-info-widget">
												<?php
													$default = $pdo->prepare('SELECT * FROM tutores WHERE tutor_id=:tutor_id');
													$default->bindParam(':tutor_id', $_SESSION['user_id']);
													$default->execute();
													while($row = $default->fetch(PDO::FETCH_ASSOC)){
														$restarPorcentaje = $row['cobra']-($row['cobra']*0.15);

														echo '
														<form action="actualizartutor.php" method="post">
															<label for="cantidad">¿Cuánto desea cobrar por hora de tutoría?</label>
															<div class="mt-10">
																<input type="text" name="cobra" value="'.$restarPorcentaje.'" placeholder="Cantidad a cobrar" required="" class="single-input">
															</div>
															<small>*La cantidad que cobres se le sumará un porcentaje (Lo que cobra Tutoeri)</small>

															<div class="mt-10">
																<input type="text" name="nombres" value="'.$row['nombres'].'" placeholder="Nombres" class="single-input">
															</div>
															<div class="mt-10">
																<input type="text" name="apellidos" value="'.$row['apellidos'].'" placeholder="Apellidos" required="" class="single-input">
															</div>
															<div class="mt-10">
																<textarea name="descripcion" class="single-textarea" placeholder="Descripción (Opcional)"  required="">'.$row['descripcion'].'</textarea>
															</div>
															<br>
															<label for="">¿Cuál es su grado académico?</label>
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></div>
																<div class="form-select" id="default-select">
																	<select name="gradoAcademico" style="display: none;">';

																	switch($row['gradoAcademico']){
																		case "primaria":
																		echo '  <option value="primaria" selected>Primaria</option>
																				<option value="basicos">Básicos</option>
																				<option value="diversificado">Diversificado</option>
																				<option value="universitario">Universitario</option>';
																		break;
																		case "basicos":
																		echo '  <option value="primaria">Primaria</option>
																				<option value="basicos" selected>Básicos</option>
																				<option value="diversificado">Diversificado</option>
																				<option value="universitario">Universitario</option>';
																		break;
																		case "diversificado":	
																			echo '  <option value="primaria">Primaria</option>
																					<option value="basicos">Básicos</option>
																					<option value="diversificado" selected>Diversificado</option>
																					<option value="universitario">Universitario</option>';
																		break;
																		case "universitario":
																		echo '  <option value="primaria">Primaria</option>
																				<option value="basicos">Básicos</option>
																				<option value="diversificado">Diversificado</option>
																				<option value="universitario" selected>Universitario</option>';
																		break;
																		default:
																		echo '  <option value="primaria">Primaria</option>
																				<option value="basicos">Básicos</option>
																				<option value="diversificado">Diversificado</option>
																				<option value="universitario">Universitario</option>';
								
																	}
																		
														echo'		</select>
																</div>
															</div>
															<br>
															<label for="">¿Con qué categoría se familiariza más para dar clases?</label>
															<div class="input-group-icon mt-10">
																	<div class="icon"><i class="fa fa-pencil" aria-hidden="true"></i></div>
																	<div class="form-select" id="default-select1">
																		<select name="edadAlumnos" style="display: none;">';
																		switch($row['edadAlumnos']){
																			case "4-11":
																				echo '<option value="4-11" selected>de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
																			break;
																			case "12-17":
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17" selected>de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
																			break;
																			case "18-35":
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35" selected>de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
																			break;
																			case "36-50":
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50" selected>de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
																			break;
																			case "51-64":	
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64" selected>de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
																			break;
																			case "65--":
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--" selected>de 65 en adelante (tercera edad)</option>';
																			break;
																			default:
																				echo '<option value="4-11">de 4 a 11 (niños)</option>';
																				echo '<option value="12-17">de 12 a 17 (adolescentes)</option>';
																				echo '<option value="18-35">de 18 a 35 (jovenes)</option>';
																				echo '<option value="36-50">de 36 a 50 (adultos)</option>';
																				echo '<option value="51-64">de 51 a 64 (adultos mayores)</option>';
																				echo '<option value="65--">de 65 en adelante (tercera edad)</option>';
									
																		}
															echo'		</select>
																	</div>
																</div>
																<br>
																<label for="">Fecha de nacimiento</label>		
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-calendar" aria-hidden="true"></i></div>';
																if($row['datoscompletos'] == 0){
																	echo '<input type="date" name="fechaNacimiento" value="2000-01-01" required="" class="single-input" placeholder="Fecha de nacimiento">';
																}else{
																	echo '<input type="date" name="fechaNacimiento" value="'.$row['fechanacimiento'].'" required="" class="single-input" placeholder="Fecha de nacimiento">';
																}
														echo'</div>
															<br>
															<label for="">(Opcional) Debes pegar el link que redirija a la red social</label>
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-facebook" aria-hidden="true"></i></div>
																<input type="text" name="facebook" value="'.$row['facebook'].'" placeholder="Link" required="" class="single-input">
															</div>
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-twitter" aria-hidden="true"></i></div>
																<input type="text" name="twitter" value="'.$row['twitter'].'" placeholder="Link" required="" class="single-input">
															</div>
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-instagram" aria-hidden="true"></i></div>
																<input type="text" name="instagram" value="'.$row['instagram'].'" placeholder="Link" required="" class="single-input">
															</div>
															<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-skype" aria-hidden="true"></i></div>
																<input type="text" name="skype" value="'.$row['skype'].'" placeholder="Link" required="" class="single-input">
															</div>	
															<div class="text-center">
																<br>
															</div>
														
														
														';
													}
													echo '<section class="destinations-area section-gap">';

													$traerUnHorario = $pdo->prepare('SELECT horario FROM tutores WHERE tutor_id=:tutor_id');
													$traerUnHorario->bindParam(':tutor_id',$_SESSION['user_id']);
													$traerUnHorario->execute();
												
													$cadenaUnHorario = array();
													foreach($traerUnHorario as $valor){
														$resultado = $valor['horario'];
														$cadenaUnHorario = explode(',',$resultado);
													}

													echo '
													<div class="container table-responsive-xl">
															<table class="tg">
															<tr>
																<th class="tg-zc5o">HORA</th>
																<th class="tg-zc5o">LUNES</th>
																<th class="tg-zc5o">MARTES</th>
																<th class="tg-zc5o">MIÉRCOLES</th>
																<th class="tg-zc5o">JUEVES</th>
																<th class="tg-zc5o">VIERNES</th>
																<th class="tg-zc5o">SÁBADO</th>
																<th class="tg-zc5o">DOMINGO</th>
															</tr>';
													for($i=0; $i<=12; $i++){
														$hora = $i +6;
														echo '
														<tr>
														<td class="tg-zc5o">'.$hora.'</td>';

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
																echo '
																<td class="tg-zc5o" align="center">
																	<div style="text-align: center; vertical-align: middle; float: left; margin: 0 auto;">
																		<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'" checked="">
																	</div>
																</td>
																';
															}else{
																echo '<td class="tg-zc5o">
																	<div style="text-align: center; vertical-align: middle; float: left; margin: 0 auto;">
																		<input type="checkbox" name="horario[]" value="'.$dia.'|'.$hora.'">
																		
																	</div>
																</td>';
															}
														}
														echo '</tr>';
														
													}
													echo '</table>			  
														</div>
														<button name="crearTutor" class="price-btn col-lg-6">Guardar Información</button>
														';
														
														echo '</form>';
												?>
												</div>
											</div>
										</div>		
									</div>
									
								</div>

						</div>
								
																																				
					</div>
				</div>	
			</section>

			
		

		<!--AQUÍ VA EL MAPA-->
		<div class="container">
			<div id="map_container">

			<div id="map-canvas" style="height:500px;"></div>
			<br>
		                                            				
					</div>
				</div>
					
			</section>

		<!-- start footer Area -->		
		<footer class="footer-area section-gap">
			<div class="container">


				<div class="footer-bottom text-center align-items-center">
					<p class=" footer-text text-center m-0">
							&copy;	Copyright <script>document.write(new Date().getFullYear());</script> | Todos los derechos reservados.</p>
					
				</div>
			</div>
		</footer>
		<!-- End footer Area -->	
			

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h2 class="modal-title" id="exampleModalLabel">Registrarse</h2>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item " style="position: relative; width: 100%; min-height: 1px; flex: 0 0 50%; max-width: 50%;">
					<a class="nav-link active" style="font-family: 'K2D', sans-serif; font-size: 19px;" id="flight-tab" data-toggle="tab" href="#flight" role="tab" aria-controls="flight" aria-selected="true">Alumno</a>
					</li>
					<li class="nav-item " style="position: relative; width: 100%; min-height: 1px; flex: 0 0 50%; max-width: 50%;">
					<a class="nav-link" style="font-family: 'K2D', sans-serif; font-size: 19px;" id="hotel-tab" data-toggle="tab" href="#hotel" role="tab" aria-controls="hotel" aria-selected="false">Tutor</a>
					</li>
				</ul>
				<div class="tab-content" id="myTabContent">
					<br>
					<a href="" class="btn btn-block" style="background-color: #DD4B39; color: white;">Iniciar con Google</a>
					<a href="" class="btn btn-block" style="background-color: #3B5998; color: white;">Iniciar con Facebook</a>
					<a href="" class="btn btn-block" style="background-color: #55ACEE; color: white;">Iniciar con Twitter</a>
					<div class="text-center" style="margin-top: 10px;">
						<h6>- o -</h6>
					</div>
					<div class="tab-pane fade show active" id="flight" role="tabpanel" aria-labelledby="flight-tab">
						<form action="#">
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-envelope" style="font-size: 19px;"></span></div>
									<input type="email" name="email" placeholder="Correo electrónico" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Correo'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-lock" style="font-size: 19px;"></span></div>
									<input type="password" name="contrasena" placeholder="Contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Contraseña'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
								<div class="icon"><span class="lnr lnr-sync" style="font-size: 18px;"></span></div>
								<input type="password" name="repass" placeholder="Repite la contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Repetir contraseña'" required class="single-input">
							</div>
							<div class="text-center" style="margin-top: 10px;">
								<input type="submit" name="entrar" value="Entrar" class="primary-btn text-uppercase">
							</div>
						</form>
					</div>
					<div class="tab-pane fade" id="hotel" role="tabpanel" aria-labelledby="hotel-tab">
						<form action="#">
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-envelope" style="font-size: 19px;"></span></div>
									<input type="email" name="email" placeholder="Correo electrónico" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Correo'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-lock" style="font-size: 19px;"></span></div>
									<input type="password" name="contrasena" placeholder="Contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Contraseña'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
								<div class="icon"><span class="lnr lnr-sync" style="font-size: 18px;"></span></div>
								<input type="password" name="repass" placeholder="Repite la contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Repetir contraseña'" required class="single-input">
							</div>
							<div class="text-center" style="margin-top: 10px;">
								<input type="submit" name="entrar" value="Entrar" class="primary-btn text-uppercase">
							</div>
						</form>							  	
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
  </div>
  
  		<!-- preloader -->
		  <div id='preloader'><div class='preloader'></div></div>
		  <!-- /preloader -->

			<script src="js/vendor/jquery-2.2.4.min.js"></script>
			<script src="js/popper.min.js"></script>
			<script src="js/vendor/bootstrap.min.js"></script>			
			<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBqKKkmhYNNqy_HwDeADL5vtPvtIp20AmE&sensor=false"></script>		
 			<script src="js/jquery-ui.js"></script>					
  			<script src="js/easing.min.js"></script>			
			<script src="js/hoverIntent.js"></script>
			<script src="js/superfish.min.js"></script>	
			<script src="js/jquery.ajaxchimp.min.js"></script>
			<script src="js/jquery.magnific-popup.min.js"></script>						
			<script src="js/jquery.nice-select.min.js"></script>					
			<script src="js/owl.carousel.min.js"></script>							
			<script src="js/mail-script.js"></script>	
			<script src="js/main.js"></script>	
			<script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>
			<link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
		</body>
	
		<script>
		(function($) {
	"use strict"
	
	// Preloader
	$(window).on('load', function() {
		$("#preloader").delay(350).fadeOut();
	});

	// Mobile Toggle Btn
	$('.navbar-toggle').on('click',function(){
		$('#header').toggleClass('nav-collapse')
	});
	
})(jQuery);
		</script>
				<style>


					.ec-stars-wrapper {
				/* Espacio entre los inline-block (los hijos, los `a`) 
				   http://ksesocss.blogspot.com/2012/03/display-inline-block-y-sus-empeno-en.html */
				font-size: 0;
				/* Podríamos quitarlo, 
					pero de esta manera (siempre que no le demos padding), 
					sólo aplicará la regla .ec-stars-wrapper:hover a cuando
					también se esté haciendo hover a alguna estrella */
				display: inline-block;
			}
			.ec-stars-wrapper a {
				text-decoration: none;
				display: inline-block;
				font-size: 20px;
				font-size: 25px;
				color: #bababc;
			}
			
			.ec-stars-wrapper:hover a {
				color: #3D9F97;
			}
			/*
			 * El selector de hijo, es necesario para aumentar la especifidad
			 */
			.ec-stars-wrapper > a:hover ~ a {
				color: #bababc;
			}
			
			.activo{
				border: 1px outset #288894;
				border-radius: 3px;
				background: linear-gradient(#3D9F97, #00548F);
			}
			
			.activ{
				border: 1px solid #01548f;
				border-radius: 3px;
				background: transparent;
				/*color: white;*/
			}
			
			#map_container{
			  position: relative;
			}
			#map{
				height: 0;
				overflow: hidden;
				padding-bottom: 22.25%;
				padding-top: 30px;
				position: relative;
			}
					</style>
					<script>
					$( document ).ready( function() {
			
			  
				//Google Maps JS
				//Set Map
				function initialize() {
						var myLatlng = new google.maps.LatLng(53.3333,-3.08333);
						var imagePath = 'http://m.schuepfen.ch/icons/helveticons/black/60/Pin-location.png'
						var mapOptions = {
							zoom: 11,
							center: myLatlng,
							mapTypeId: google.maps.MapTypeId.ROADMAP
						}
			
					var map = new google.maps.Map(document.getElementById('map'), mapOptions);
					//Callout Content
					var contentString = 'Some address here..';
					//Set window width + content
					var infowindow = new google.maps.InfoWindow({
						content: contentString,
						maxWidth: 500
					});
			
					//Add Marker
					var marker = new google.maps.Marker({
						position: myLatlng,
						map: map,
						icon: imagePath,
						title: 'image title'
					});
			
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map,marker);
					});
			
					//Resize Function
					google.maps.event.addDomListener(window, "resize", function() {
						var center = map.getCenter();
						google.maps.event.trigger(map, "resize");
						map.setCenter(center);
					});
				}
			
				google.maps.event.addDomListener(window, 'load', initialize);
			
			});
					</script>
			
			<style type="text/css">
				.tg  {
				border-collapse: collapse;
				border-spacing: 0;
				border-radius: 3px;
				width: 100%;
				margin-bottom: 60px;
				}
				.tg td{
				font-size: 14px;
				padding: 20px 20px;
				border: 3px solid #eff5fd;
				border-radius: 3px;
				overflow: hidden;
				text-align: center;
				vertical-align: middle;  
				}
				.tg th{
				font-size: 14px;
				padding: 20px 20px;
				border: 3px solid #015e9a;
				border-radius: 3px;
				overflow: hidden;
				background: #015e9a;
				color: white;
				}
				.tg .tg-zc5o{
				/* 	background-color: #ffffff;
				border-color: #999999; */
				text-align: center;
				text-align: center;
				vertical-align: middle;  
				}
				</style>
	</html>

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