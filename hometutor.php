<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();

    $verificarComentario = $pdo->prepare('SELECT alumnos.nombres,alumnos.apellidos, alumnos.imagenperfil,calificaciones.comentario,calificaciones.calificaciones_id FROM alumnos,calificaciones 
    WHERE calificaciones.revision=0  AND calificaciones.comentario != "" AND calificaciones.tutor_id = :tutor_id AND calificaciones.alumno_id=alumnos.alumno_id');
    $verificarComentario->bindParam(':tutor_id', $_SESSION['user_id']);
    $verificarComentario->execute();



    if(isset($_POST['tutor'])){
        $eleccion = 1;
        $completo = $pdo->prepare('UPDATE indecisos SET eleccion=:eleccion WHERE indeciso_id=:indeciso_id');
        $completo->bindParam(':eleccion',$eleccion);
        $completo->bindParam(':indeciso_id', $_SESSION['idBorrar']);
        $completo->execute();

        $agregarNuevoTutor = $pdo->prepare('INSERT INTO tutores (email,firebase_id) values (:email,:firebase_id)');
        $agregarNuevoTutor->bindParam(':email',$_SESSION['email']);
        $agregarNuevoTutor->bindParam(':firebase_id',$_SESSION['firebase_id']);
        $agregarNuevoTutor->execute();

        $traerNuevoTutor = $pdo->prepare('SELECT tutor_id as id FROM tutores WHERE email=:email');
        $traerNuevoTutor->bindParam(':email',$_SESSION['email']);
        $traerNuevoTutor->execute();

        foreach($traerNuevoTutor as $valor){
            $_SESSION['user_id'] = $valor['id'];
            header('Location: actualizartutor.php');
        }
    }

    if(isset($_POST['alumno'])){
        $eleccion = 1;
        $completo = $pdo->prepare('UPDATE indecisos SET eleccion=:eleccion WHERE indeciso_id=:indeciso_id');
        $completo->bindParam(':eleccion',$eleccion);
        $completo->bindParam(':indeciso_id', $_SESSION['idBorrar']);
        $completo->execute();

        $agregarNuevoAlumno = $pdo->prepare('INSERT INTO alumnos (email,firebase_id) values (:email,:firebase_id)');
        $agregarNuevoAlumno->bindParam(':email',$_SESSION['email']);
        $agregarNuevoAlumno->bindParam(':firebase_id',$_SESSION['firebase_id']);
        $agregarNuevoAlumno->execute();

        $traerNuevoAlumno = $pdo->prepare('SELECT alumno_id as id FROM alumnos WHERE email=:email');
        $traerNuevoAlumno->bindParam(':email',$_SESSION['email']);
        $traerNuevoAlumno->execute();

        foreach($traerNuevoAlumno as $valor){
            $_SESSION['user_id'] = $valor['id'];
            header('Location: actualizaralumno.php');
        }
    }

    if(isset($_POST['haceptar'])){
        $fecha = zonaHoraria("Y-m-d H:i:s");
        $token = $_POST['datos'];
        $actualizarBancoPuntos = $pdo->prepare('UPDATE bancopuntos SET cobropuntos=1, nota="clase cancelada",fechafin=:fechafin WHERE bancopuntos_id = :bancopuntos_id');
        $actualizarBancoPuntos->bindParam(':bancopuntos_id',$token);
        $actualizarBancoPuntos->bindParam(':fechafin',$fecha);
        $actualizarBancoPuntos->execute();

        $traerAlumno = $pdo->prepare('SELECT alumno_id,puntos FROM bancopuntos WHERE bancopuntos_id =:bancopuntos_id');
        $traerAlumno->bindParam(':bancopuntos_id',$token);
        $traerAlumno->execute();
        $alumnoid = 0;
        $puntos=0;
        foreach($traerAlumno as $valor){
            $alumnoid = $valor['alumno_id'];
            $puntos =$valor['puntos'];
        } 
        
        $traerAlumnoActualizar = $pdo->prepare('SELECT puntos FROM alumnos WHERE alumno_id=:alumno_id');
        $traerAlumnoActualizar->bindParam(':alumno_id',$alumnoid);
        $traerAlumnoActualizar->execute();
        $puntosAlumno = 0;
        foreach($traerAlumnoActualizar as $valor){
            $puntosAlumno = $valor['puntos'];
        }

        $puntosActualizados = $puntosAlumno+$puntos;

        $actualizarPuntos = $pdo->prepare('UPDATE alumnos SET puntos=:puntos WHERE alumno_id=:alumno_id');
        $actualizarPuntos->bindParam(':puntos',$puntosActualizados);
        $actualizarPuntos->bindParam(':alumno_id',$alumnoid);
        if($actualizarPuntos->execute()){
             //emisor
            $emisor = "Tutor|".$_SESSION['user_id'];

            //receptor
            $receptor = "Alumno|".$alumnoid; 

            //texto
            $texto = "2|Acepto la declinacion, tus puntos han sido devueltos|".$_SESSION['user_id'];

            $mandarMensaje=$pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
            $mandarMensaje->bindParam(':texto',$texto);
            $mandarMensaje->bindParam(':emisor',$emisor);
            $mandarMensaje->bindParam(':receptor',$receptor);
            $mandarMensaje->execute();

            $visto = $pdo->prepare('UPDATE mensajes SET visto=1 WHERE texto=:texto');
            $visto->bindParam(':texto',$_POST['texto']);
            $visto->execute();

            $actualizarSesion = $pdo->prepare('UPDATE calificaciones SET sesion=3 WHERE tutor_id=:tutor_id AND alumno_id=:alumno_id AND sesion=1');
            $actualizarSesion->bindParam(':tutor_id',$_SESSION['user_id']);
            $actualizarSesion->bindParam(':alumno_id',$alumnoid);
            $actualizarSesion->execute();
        }



    }
    
    
    if(isset($_POST['rechazar'])){
        $token = $_POST['datos'];
        $traerAlumno = $pdo->prepare('SELECT alumno_id FROM bancopuntos WHERE bancopuntos_id =:bancopuntos_id');
        $traerAlumno->bindParam(':bancopuntos_id',$token);
        $traerAlumno->execute();
        $alumnoid = 0;
        foreach($traerAlumno as $valor){
            $alumnoid = $valor['alumno_id'];
        }  


        //emisor
        $emisor = "Tutor|".$_SESSION['user_id'];

        //receptor
        $receptor = "Alumno|".$alumnoid; 

        //texto
        $texto = "2|No hacepto la declinacion|".$_SESSION['user_id'];

        $mandarMensaje=$pdo->prepare('INSERT INTO mensajes (texto,emisor,receptor) VALUES (:texto,:emisor,:receptor)');
        $mandarMensaje->bindParam(':texto',$texto);
        $mandarMensaje->bindParam(':emisor',$emisor);
        $mandarMensaje->bindParam(':receptor',$receptor);
        $mandarMensaje->execute();

        $visto = $pdo->prepare('UPDATE mensajes SET visto=1 WHERE texto=:texto');
        $visto->bindParam(':texto',$_POST['texto']);
        $visto->execute();
    }

    if(isset($_POST['SI'])){
        $actualizarVisualizacion = $pdo->query('UPDATE calificaciones SET visible=1, revision=1 WHERE calificaciones_id='.$_POST['idCalificacion'].'');
        header('Location: hometutor.php');
    }

    if(isset($_POST['NO'])){    
        $actualizarVisualizacion = $pdo->query('UPDATE calificaciones SET visible=0, revision=1 WHERE calificaciones_id='.$_POST['idCalificacion'].'');
        header('Location: hometutor.php');
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
		<title>Inicio Tutor | Tutoeri</title>

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
								<li class="activo"><a href="hometutor.php">Inicio</a></li>
								<li><a href="actualizartutor.php">Mi Cuenta</a></li>		          					          		          
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
							  <li class="activo"><a href="hometutor.php">Inicio</a></li>
							  <li><a href="actualizartutor.php">Mi Cuenta</a></li>
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
								Hola				
							</h1>	
							<p class="text-white link-nav"><a href="index.html">Bienvenido, <?php echo $email; ?></a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

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
			?>

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">
				<div class="container">
		            <div class="row d-flex justify-content-center">
		                <div class="menu-content pb-40 col-lg-8">
		                    <div class="title text-center">
		                        <h1 class="mb-10">Mensajes / Notificaciones</h1>
		                        <p>Cuando tengas algún mensaje o notificación aparecerán debajo...</p>
		                    </div>
		                </div>
		            </div>						
					<div class="row">
						<?php
						$receptor = "Tutor|".$_SESSION['user_id'];

						$traerMensajes = $pdo->prepare('SELECT texto FROM mensajes WHERE receptor=:receptor AND visto=0');
						$traerMensajes->bindParam(':receptor',$receptor);
						$traerMensajes->execute();

						foreach($traerMensajes as $valor){
							$separar = explode('|',$valor['texto']);
							
							if($separar[0] == 1){
								$llamarImagen = $pdo->prepare('SELECT imagenperfil FROM alumnos WHERE email=:email');
								$llamarImagen->bindParam(':email',$separar[2]);
								$llamarImagen->execute();
								$imagen = "";
								foreach($llamarImagen as $valordos){
									$imagen = $valordos['imagenperfil'];
								}
								echo '
								<div class="col-lg-4">
									<form action="hometutor.php" method="post">
										<div class="single-destinations">
											<div class="alerta">
												<h4 class="d-flex justify-content-between text-center">
													<span>Cancelación de Tutoría</span>  	
												</h4>
												<p style="color: white;">
													El alumno '.$separar['2'].':  
												</p>
												<div>
													<div class="sidebar-widgets">
														<div class="widget-wrap">
															<div class="single-sidebar-widget user-info-widget">
																<img class="imgPerfil" src="./imagenalumno/'.$imagen.'" alt="">
																<a href="#"><h4 style="color: black;">'.$separar['1'].'</h4></a>
																<br>
																<p>
																Deseo cancelar la tutoría que tenía un valor de [Q.'.$separar[3].'], el token es ['.$separar[4].']
																</p>
																<input type="text" name="datos" value="'.$separar[4].'" hidden="true">
                    											<input type="text" name="texto" value="'.$valor['texto'].'" hidden="true">
															</div>
														</div>
													</div>		
												</div>
												<div class="text-center">
													<input type="submit" class="price-btn col-lg-5" value="Aceptar" name="haceptar">
													<input type="submit" class="price-btn col-lg-5" value="Rechazar" name="rechazar">
												</div>
											</div>
										</div>
									</form>
								</div>
								
								';

							}
						}


						foreach($verificarComentario as $valor){
							echo '
								<div class="col-lg-4">
									<form action="hometutor.php" method="post">
										<div class="single-destinations">
											<!--<div class="thumb">
												<img src="img/hotels/d3.jpg" alt="">
											</div>-->
											<div class="tutoria">
												<h4 class="d-flex justify-content-between">
													<span>Calificación de Alumno</span>   	
												</h4>
												<div>
													<div class="sidebar-widgets">
														<div class="widget-wrap">
															<div class="single-sidebar-widget user-info-widget">
																<img class="imgPerfil" src="./imagenalumno/'.$valor['imagenperfil'].'" alt="">
																<a href="#"><h4 style="color: black;">['.$valor['nombres'].','.$valor['apellidos'].']</h4></a>
																<p>[Fecha de la tutoría]</p>
																<form action="">
																	
																	<div class="form-group">
																		<label for="exampleFormControlTextarea1">Comentarios:</label>
																		<textarea disabled class="form-control" id="exampleFormControlTextarea1" rows="3">'.$valor['comentario'].'</textarea>
																		<input type="text" name="idCalificacion" value="'.$valor['calificaciones_id'].'" hidden="true">
																	</div>
																</form>
															</div>
														</div>
													</div>
													<div class="text-center">
														<p style="color: white; font-size: 12px;">
															¿Desea que este comentario aparezca en su perfil?
														</p>
													</div>		
													
												</div>
												<div class="text-center">
														<input class="price-btn col-lg-5" type="submit" value="SI" name="SI">
													<input class="price-btn col-lg-5" type="submit" value="NO" name="NO">
												</div>
											</div>
										</div>
									</form>
								</div>	
							
							';
						}
						?>
						

						
						
																																				
					</div>
				</div>	
			</section>
			<!-- End destinations Area -->
			

			<!-- Start home-about Area -->
			<!--<section class="home-about-area">
				<div class="container-fluid">
					<div class="row align-items-center justify-content-end">
						<div class="col-lg-6 col-md-12 home-about-left">
							<h1>
								Did not find your Package? <br>
								Feel free to ask us. <br>
								We‘ll make it for you
							</h1>
							<p>
								inappropriate behavior is often laughed off as “boys will be boys,” women face higher conduct standards especially in the workplace. That’s why it’s crucial that, as women, our behavior on the job is beyond reproach. inappropriate behavior is often laughed.
							</p>
							<a href="#" class="primary-btn text-uppercase">request custom price</a>
						</div>
						<div class="col-lg-6 col-md-12 home-about-right no-padding">
							<img class="img-fluid" src="img/hotels/about-img.jpg" alt="">
						</div>
					</div>
				</div>	
			</section>-->
			<!-- End home-about Area -->

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
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>		
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
		</style>
	</html>