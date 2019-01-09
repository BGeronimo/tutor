<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();
    $porcentaje = 0.15;
	$traerAlumno = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');


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
        $traerDatos = $pdo->prepare('SELECT horasclase, puntos, materia_id FROM bancopuntos 
        WHERE bancopuntos.tutor_id=:tutor_id AND bancopuntos.alumno_id=:alumno_id AND bancopuntos.cobropuntos=0');
        $traerDatos->bindParam(':tutor_id',$tutorid);
        $traerDatos->bindParam(':alumno_id',$userid);
        $traerDatos->execute();
        $horas = 0;
		$puntos = 0; 
		$materiaId = 0;
        foreach($traerDatos as $valor){
                $puntos = $valor['puntos'];
				$horas = $valor['horasclase'];
				$materiaId = $valor['materia_id'];
		}
		
		//////asigna las horas de clase segun la materia
		$traerHorasMateria = $pdo->prepare('SELECT materias.horas as materiasHoras, materiatutor.horasclase as materiatutorHora 
		FROM materias, materiatutor WHERE materias.materia_id=materiatutor.materia_id AND materias.materia_id=:materia_id AND materiatutor.tutor_id=:tutor_id');
		$traerHorasMateria->bindParam(':materia_id', $materiaId);
		$traerHorasMateria->bindParam(':tutor_id', $_POST['idtutor']);
		$traerHorasMateria->execute();

		$materias = 0;
		$materiaTutor = 0;
		foreach($traerHorasMateria as $valor){
			$materias = $valor['materiasHoras'];
			$materiaTutor = $valor['materiatutorHora'];
		}
		$materiasSuma = $materias+$horas;
		$materiaTutorSuma = $materiaTutor+$horas;
		var_dump($materiasSuma);
		var_dump($materiaTutorSuma);

		$actualizarHorasMateria = $pdo->prepare('UPDATE materias SET horas=:horas WHERE materia_id=:materia_id');
		$actualizarHorasMateria->bindParam(':horas', $materiasSuma);
		$actualizarHorasMateria->bindParam(':materia_id', $materiaId);
		$actualizarHorasMateria->execute();

		$actualizarHorasMateriaTutor = $pdo->prepare('UPDATE materiatutor SET horasclase=:horasclase WHERE materia_id=:materia_id AND tutor_id =:tutor_id');
		$actualizarHorasMateriaTutor->bindParam(':horasclase', $materiaTutorSuma);
		$actualizarHorasMateriaTutor->bindParam(':materia_id', $materiaId);
		$actualizarHorasMateriaTutor->bindParam(':tutor_id', $_POST['idtutor']);
		$actualizarHorasMateriaTutor->execute();


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
	
	
	if(isset($_POST['enviar'])){
		$traerDatosTutor = $pdo->query('SELECT cobra FROM tutores WHERE tutor_id='.$_SESSION['tutorId'].'');
    	$traerPuntos = $pdo->query('SELECT puntos FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');


        $horas = (int)$_POST['cantidadHoras'];
        $cobra = 0;
        foreach($traerDatosTutor as $valor){
            $cobra = $valor['cobra'];
        }
        $tiene = 0;
        foreach($traerPuntos as $valor){
            $tiene = $valor['puntos'];
        }
        $cantidadCobrar = $horas*$cobra;

        if($cantidadCobrar>$tiene){
            echo'<script type="text/javascript">
                 alert("no tienes el dinero suficiente");
                 location.href="comprarpuntos.php";
                 </script>';
        }else{
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $random = rand(1000, 9999);
            $token = str_shuffle("abcdefghijklmno14725".uniqid());
            $puntosNuevos = $tiene-$cantidadCobrar;
            $actualizarPuntos = $pdo->query('UPDATE alumnos SET puntos='.$puntosNuevos.' WHERE alumno_id='.$_SESSION['user_id'].'');
            $ingresarBancoPuntos = $pdo->prepare('INSERT INTO bancopuntos (bancopuntos_id,alumno_id,tutor_id,fechainicio,claveconfirmacion,puntos,horasclase,materia_id) 
            VALUES (:bancopuntos_id,:alumno_id,:tutor_id,:fechainicio,:claveconfirmacion,:puntos,:horasclase,:materia_id)');
            $ingresarBancoPuntos->bindParam(':bancopuntos_id',$token);
            $ingresarBancoPuntos->bindParam(':alumno_id',$_SESSION['user_id']);
            $ingresarBancoPuntos->bindParam(':tutor_id',$_SESSION['tutorId']);
            $ingresarBancoPuntos->bindParam(':fechainicio',$fecha);
            $ingresarBancoPuntos->bindParam(':claveconfirmacion',$random);
            $ingresarBancoPuntos->bindParam(':puntos',$cantidadCobrar);
			$ingresarBancoPuntos->bindParam(':horasclase',$horas);
			$ingresarBancoPuntos->bindParam(':materia_id', $_POST['materia']);
            if($ingresarBancoPuntos->execute()){
                $sesion = 1;
                $insertCalificacion = $pdo->prepare('INSERT INTO calificaciones (tutor_id,alumno_id,sesion,claveconfirmacion) VALUES (:tutor_id,:alumno_id,:sesion,:claveconfirmacion)');
                $insertCalificacion->bindParam(':tutor_id', $_SESSION['tutorId']);
                $insertCalificacion->bindParam(':alumno_id',$_SESSION['user_id']);
                $insertCalificacion->bindParam(':sesion',$sesion);
                $insertCalificacion->bindParam(':claveconfirmacion',$random);
                $insertCalificacion->execute();

               echo' <script> 
                    window.open("imprimirpdf.php?id="+'.$random.'+"&monto="+'.$cantidadCobrar.'+"&horas="+'.$horas.');
                    location.href="elegirtutor.php"; 
                    </script>';
            } 
        }
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
		<title>Perfil Tutor | Tutoeri</title>

		
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

			<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBqKKkmhYNNqy_HwDeADL5vtPvtIp20AmE&sensor=false"></script>
		</head>
		<body>	
			<header id="header">
				<?php
					$correo = "";
					while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
						if($row['datoscompletos'] == 0){
							echo '
							<div class="header-top">
								<div class="container">
								<div class="row align-items-center">
									<div class="col-lg-6 col-sm-6 col-6 header-top-left">
										<ul>
											<li><a >['.$row[puntos].'] Puntos</a></li>
											<li><a href="#">Comprar Puntos</a></li>
										</ul>			
									</div>
								</div>			  					
								</div>
							</div>
							';
						}else{
							//si tiene los datos completos
							echo '
							<div class="header-top">
							<div class="container">
							<div class="row align-items-center">
								<div class="col-lg-6 col-sm-6 col-6 header-top-left">
									<ul>
										<li><a href="">['.$row['puntos'].'] Puntos</a></li>
										<li><a href="comprarpuntos.php">Comprar Puntos</a></li>
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
								<li class="activo"><a href="homealumno.php">Inicio</a></li>
								<li><a href="actualizaralumno.php">Mi Cuenta</a></li>
								<li><a href="materiasalumno.php">Materias</a></li>
								<!-- 
								<li><a href="hotels.html">Pupilos</a></li>
								<li><a href="insurance.html">Cobrar Puntos</a></li>
								-->
								<li class="menu-has-children"><a href="">Profesores</a>
									<ul>
									<li><a href="elegirtutor.php">Elegir Tutor</a></li>
									<li><a href="mistutores.php">Mis Tutores</a></li>
									<li><a href="historialtutores.php">Historial de tutores</a></li>
									</ul>
								</li>				          					          		          
								<li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
								</ul>
							</nav><!-- #nav-menu-container -->					      		  
							</div>
						</div>
							';

						}
						$correo = $row['email'];
					}
				?>

			</header><!-- #header -->
			  
			<!-- start banner Area -->
			<section class="about-banner relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12" style="margin-top: 0px;">
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">

				


			<?php
			$opcionBoton = $pdo->prepare('SELECT COUNT( alumno_id) AS numero, alumno_id as alumno, sesion as activo 
			FROM calificaciones  WHERE tutor_id = '.$_GET['id'].' AND alumno_id=:alumno_id AND sesion = 1');
			$opcionBoton->bindParam(':alumno_id',$_SESSION['user_id']);
			$opcionBoton->execute();

			$traerCalificacion = $pdo->query('SELECT TRUNCATE( AVG(calificacion),0) as promediotruncate, COUNT(calificacion) as numero, TRUNCATE(AVG(calificacion),2) as promedio FROM calificaciones WHERE tutor_id = '.$_GET['id'].' AND calificacion!=0');

			$vermasTutor = $pdo->query('SELECT * FROM tutores WHERE tutor_id = '.$_GET['id'].'');

			$traerMateriaTutor = $pdo->query('SELECT materias.nombre as nombre, materiatutor.horasclase as horas, materiatutor.activo as activo FROM materias, materiatutor WHERE materiatutor.materia_id=materias.materia_id AND materiatutor.tutor_id='.$_GET['id'].'');

			while($row = $vermasTutor->fetch(PDO::FETCH_ASSOC)){
				echo '<form action="perfiltutor.php" method="post">';
				echo '
					<div class="container">
						<div class="row">
							<div class="col-lg-6 col-md-6">
								<div class="widget-wrap">
									<div class="single-sidebar-widget user-info-widget">
										<img src="./imagentutor/'.$row['imagenperfil'].'" class="imgPerfil" alt="">
										<a href=""><h4 style="margin-bottom: 28px;">'.$row['nombres'].'</h4></a>
										
										<ul class="social-links">
											<li><a href=""><i class="fa fa-facebook"></i></a></li>
											<li><a href=""><i class="fa fa-twitter"></i></a></li>
											<li><a href=""><i class="fa fa-google"></i></a></li>
											<li><a href=""><i class="fa fa-skype"></i></a></li>
										</ul>
										<p>
											'.$row['descripcion'].'
										</p>
									</div>					
								</div>
							</div>

							<div class="col-lg-6 col-md-6 mt-sm-30 element-wrap">
								<div class="widget-wrap">
									<div class="single-sidebar-widget post-category-widget">
										<h4 class="category-title">Materias que enseña</h4>
										<ul class="cat-list">';

										foreach($traerMateriaTutor as $valor){
											if($valor['activo'] == 1){
												echo '
											<li>
												<a href="" class="d-flex justify-content-between">
													<p>'.$valor['nombre'].'</p>
													<p>['.$valor['horas'].' Horas]</p>
												</a>
											</li>
											';

											}else{
												echo '
											<li>
												<a href="" class="d-flex justify-content-between">
													<p>'.$valor['nombre'].' (por ahora no esta disponible esta materia)</p>
													<p>['.$valor['horas'].' Horas]</p>
												</a>
											</li>
											';
											}


				

										}
																
				echo'					</ul>
									</div>
								</div>
							</div>

						</div>
					</div>
				';


				echo '
					<div class="container">
						<div class="section-top-border">
							<h3 class="mb-30">información...</h3>
							<div class="row">
								<div class="col-lg-12">
										<div class="jumbotron text-center">
												<h1 class="">'.$row['cobra'].'Q por hora</h1>
												<br>
												<h3 style="color: #01548f;">[Horas de clase: '.$row['horasclase'].']</h3>
												<br>';

												foreach($traerCalificacion as $valor){
													if($valor['numero']==0){
														echo '<p>Aun no lo han calificado</p>';
														echo '
															<div class="star" style="font-size: 35px;">
																<span class="fa fa-star"></span>
																<span class="fa fa-star"></span>
																<span class="fa fa-star"></span>
																<span class="fa fa-star"></span>
																<span class="fa fa-star"></span>				
															</div>
														';
													}else{
														echo '<p> calificacion: '.$valor['promedio'].'</p>';
														$numero = $valor['promediotruncate'];
														switch ($numero) {
															case 1:
																echo ' 
																<div class="star" style="font-size: 35px;">
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>				
																</div>
																';
																break;
															case 2:
																echo '
																<div class="star" style="font-size: 35px;">
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>				
																</div>
																';
																break;
															case 3:
																echo '
																<div class="star" style="font-size: 35px;">
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star"></span>
																	<span class="fa fa-star"></span>				
																</div>
																';
																break;
															case 4:
																echo '
																<div class="star" style="font-size: 35px;">
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star"></span>				
																</div>
																';
																break;
															case 5:
																echo '
																<div class="star" style="font-size: 35px;">
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>
																	<span class="fa fa-star checked"></span>				
																</div>
																';
																break;
														}
														
													}
												}

												while($row2 = $opcionBoton->fetch(PDO::FETCH_ASSOC)){

													if($row2['numero']==1){

														echo '<input type="text" name="idtutor" value="'.$row['tutor_id'].'" hidden="true">';
														
														echo '
												<hr class="my-4">
												<div class="alert alert-info" role="alert">
													<h4 class="alert-heading">¡Uuups!</h4>
													<p>Parece que ya tienes una sesión reservada con este tutor.</p>
													<hr>
													<input class="btn btn-primary btn-lg" type="submit" name="enviarpago" value="hacer pago directo">
													<input class="btn btn-primaryDos btn-lg" type="submit" name="declinar" value="Cancelar tutoría">
												</div>

											</div>
														';
								
													}else{
														//echo '<a href="adquirirtutoria.php?id='.$row['tutor_id'].'">Adquirir tutoria</a>';
														echo '
												<hr class="my-4">
												<p>Si está interesado puede solicitar una tutoría...</p>
												<a class="btn btn-primary btn-lg" data-toggle="modal" data-target="#exampleModal" style="color: white !important; font-size: 16px; font-family: "K2D", sans-serif; text-transform: uppercase; font-weight: 600;">Adquirir tutoría</a>

											</div>';
													}
													
												}
				echo'
								</div>
							</div>
						</div>
					</div>					
				';
				
				$_SESSION['tutorId'] = $row['tutor_id'];
				echo '</form>';


				$traerUnHorario = $pdo->prepare('SELECT horario FROM tutores WHERE tutor_id=:tutor_id');
				$traerUnHorario->bindParam(':tutor_id',$_GET['id']);
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
								<div class="disabled-checkbox" style="text-align: center; vertical-align: middle; float: left; margin: 0 auto;">
									<input type="checkbox" id="disabled-checkbox-active" name="horario[]" value="'.$dia.'|'.$hora.'" checked="" disabled="">
									<label for="disabled-checkbox-active"></label>
								</div>
							</td>
							';
						}else{
							echo '<td class="tg-zc5o"></td>';
						}
					}
					echo '</tr>';
					 
				}
				echo '</table>			  
					</div>';

			}
			?>
			
				<!--AQUÍ VA EL MAPA-->
				<div class="container">
					<div id="map-canvas" style="height:500px;"></div>
				</div>
				<br>
				<br>
				<br>	
				<div class="container">
					<div class="comments-area">
						    <?php
								if(isset($_GET['id'])){
									$traerComentarios = $pdo->query('SELECT  alumnos.nombres, alumnos.imagenperfil,calificaciones.comentario FROM alumnos,calificaciones 
									WHERE calificaciones.tutor_id = '.$_GET['id'].' AND calificaciones.alumno_id=alumnos.alumno_id AND calificaciones.visible=1');

									
									foreach($traerComentarios as $valor){
										
										echo '
										<div class="comment-list">
											<div class="single-comment justify-content-between d-flex">
												<div class="user justify-content-between d-flex">
													<div class="thumb">
														<img style="width:150px;" src="./imagenalumno/'.$valor['imagenperfil'].'" alt="" class="imgPerfilDos">
													</div>
													<div class="desc">
														<h5><a href="">'.$valor['nombres'].'</a></h5>
														<p class="date">[EN PROCESO]</p>
														<p class="comment">
														'.$valor['comentario'].'
														</p>
													</div>
												</div>
											</div>
										</div>
										';
									}

								}
							
							?>

					</div>
				</div>
					
			</section>
			<!-- End destinations Area -->
		

		<!-- start footer Area -->		
		<footer class="footer-area section-gap">
			<div class="container">

				<div class="row">
					<div class="col-lg-4  col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Acerca de Tutoeri</h6>
							<p>
								The world has become so fast paced that people don’t want to stand by reading a page of information, they would much rather look at a presentation and understand the message. It has come to a point 
							</p>
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Links</h6>
							<div class="row">
								<div class="col">
									<ul>
										<li><a href="#">Home</a></li>
										<li><a href="#">Feature</a></li>
										<li><a href="#">Services</a></li>
										<li><a href="#">Portfolio</a></li>
									</ul>
								</div>
								<div class="col">
									<ul>
										<li><a href="#">Team</a></li>
										<li><a href="#">Pricing</a></li>
										<li><a href="#">Blog</a></li>
										<li><a href="#">Contact</a></li>
									</ul>
								</div>										
							</div>							
						</div>
					</div>							
					<div class="col-lg-4  col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Redes Sociales</h6>
							<p>
								Encuéntranos en nuestras redes sociales para más información.									
							</p>								
							<div class=" footer-social">
									<a href="#"><i class="fa fa-facebook"></i></a>
									<a href="#"><i class="fa fa-twitter"></i></a>
									<a href="#"><i class="fa fa-dribbble"></i></a>
									<a href="#"><i class="fa fa-behance"></i></a>
								</div>
						</div>
					</div>
										
				</div>

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
			  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>
			<div class="modal-body">
				<form action="perfiltutor.php" method="post" class="">
					<div class="form-group text-center">
						<label for="exampleFormControlSelect1" style="font-size: 17px;">¿Cuánto tiempo desea pedir la tutoría?</label>
						<select name="cantidadHoras" class="form-control" id="exampleFormControlSelect1">
						  <option value="1">1 Hora</option>
						  <option value="2">2 Horas</option>
						  <option value="3">3 Horas</option>
						  <option value="4">4 Horas</option>
						  <option value="5">5 Horas</option>
						</select>
					  </div>
					  <div class="form-group text-center">
						<label for="exampleFormControlSelect1" style="font-size: 17px;">¿Qué materia desea?</label>
						
						<select name="materia" class="form-control" id="exampleFormControlSelect1">
							<?php
								$traerMateriaTutor2 = $pdo->query('SELECT materias.nombre as nombre, materias.materia_id as id FROM materias, materiatutor WHERE materiatutor.materia_id=materias.materia_id AND materiatutor.activo=1 AND materiatutor.tutor_id='.$_GET['id'].'');
								foreach($traerMateriaTutor2 as $valor){
									echo '<option value="'.$valor['id'].'">'.$valor['nombre'].'</option>';
								}
							?>
						</select>
					  </div>
					<button type="submit" class="btn btn-block btn-primary my-1" name="enviar">Pagar Tutoría</button>
				  </form>
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
  
	//Google Maps JS
	//Set Map

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

	
}
	</style>
	</html>