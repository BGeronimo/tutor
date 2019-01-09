<?php
    require_once 'conection.php';
    session_start();

	$traerAlumno = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');	
	
	$imagen = $pdo->prepare('SELECT * FROM alumnos WHERE alumno_id=:alumno_id');
    $imagen->bindParam(':alumno_id', $_SESSION['user_id']);
    $imagen->execute();


    if(isset($_POST['subirImagen'])){
        
        $tipoImagen = $_FILES['imagen']['type'];
        $nombreImagen = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());
        $sizeImagen = $_FILES['imagen']['size'];

        if($sizeImagen<3000000){
            if($tipoImagen == "image/jpeg" || $tipoImagen == "image/jpg" || $tipoImagen == "image/png"){
				$lugarGuardado = 'imagenalumno/';
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
				
				$traerFotoActual = $pdo->query('SELECT imagenperfil FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');
				$imagenAntigua = "";
				foreach($traerFotoActual as $valor){
					$imagenAntigua = $valor['imagenperfil'];
				}	
				
                $actualizarFoto = $pdo->prepare('UPDATE alumnos SET imagenperfil=:imagenperfil WHERE alumno_id=:alumno_id');
                $actualizarFoto->bindParam(':imagenperfil', $nombreArreglado);
                $actualizarFoto->bindParam(':alumno_id', $_SESSION['user_id']);
                if($actualizarFoto->execute()){
					unlink('./imagenalumno/'.$imagenAntigua);
                    header('Location: actualizaralumno.php');
                }
                
               
            }else{
                echo 'solo se permite subir archivos de tipo .jpeg, .jpg y .png';
            }
        }else{
            echo 'la imagen des demasiado grande';
        }
    }

    if(isset($_POST['crearAlumno'])){
        $traerAlumno2 = $pdo->prepare('SELECT *  FROM alumnos WHERE alumno_id=:alumno_id');
        $traerAlumno2->bindParam(':alumno_id', $_SESSION['user_id']);
        $traerAlumno2->execute();

        while($row = $traerAlumno2->fetch(PDO::FETCH_ASSOC)){
            if($row['datoscompletos'] == 0){
                if(!empty($_POST['nombres']) && !empty($_POST['apellidos']) && !empty($_POST['gradoAcademico']) && !empty($_POST['fechaNacimiento']) && !empty($_POST['establecimiento'])){

                    $actualizar = $pdo->prepare('UPDATE alumnos SET nombres=:nombres, apellidos=:apellidos, gradoacademico=:gradoAcademico, fechanacimiento=:fechanacimiento, establecimiento=:establecimiento, datoscompletos=:datoscompletos WHERE alumno_id=:alumno_id');
                    $actualizar->bindParam(':nombres', $_POST['nombres']);
                    $actualizar->bindParam(':apellidos', $_POST['apellidos']);
                    $actualizar->bindParam(':gradoAcademico', $_POST['gradoAcademico']);
                    $actualizar->bindParam(':fechanacimiento', $_POST['fechaNacimiento']);
                    $actualizar->bindParam(':establecimiento', $_POST['establecimiento']);
                    $actualizar->bindParam(':alumno_id', $_SESSION['user_id']);
                    $datosCompletos = 1;
                    $actualizar->bindParam(':datoscompletos', $datosCompletos);
                    if($actualizar->execute()){
                        header('Location: actualizaralumno.php');
                    }
                }else{
                    echo 'no se han igresado todos los datos necesarios';
                }
            }else{
                if(!empty($_POST['nombres']) || !empty($_POST['apellidos']) || !empty($_POST['gradoAcademico']) || !empty($_POST['fechaNacimiento']) || !empty($_POST['establecimiento'])){

                    $actualizar = $pdo->prepare('UPDATE alumnos SET nombres=:nombres, apellidos=:apellidos, gradoacademico=:gradoAcademico, fechanacimiento=:fechanacimiento, establecimiento=:establecimiento WHERE alumno_id=:alumno_id');
                    $actualizar->bindParam(':nombres', $_POST['nombres']);
                    $actualizar->bindParam(':apellidos', $_POST['apellidos']);
                    $actualizar->bindParam(':gradoAcademico', $_POST['gradoAcademico']);
                    $actualizar->bindParam(':fechanacimiento', $_POST['fechaNacimiento']);
                    $actualizar->bindParam(':establecimiento', $_POST['establecimiento']);
                    $actualizar->bindParam(':alumno_id', $_SESSION['user_id']);
                    if($actualizar->execute()){
                        header('Location: actualizaralumno.php');
                    }
                }else{
                    echo 'no se han hecho cambios';
                }
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
		<title>Mi Cuenta | Tutoeri</title>

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
		<!---->
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
										<li><a >['.$row['puntos'].'] Puntos</a></li>
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
							  <li><a href="homealumno.php">Inicio</a></li>
							  <li class="activo"><a href="actualizaralumno.php">Mi Cuenta</a></li>
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
						<div class="col-lg-6">
						
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
																echo '<img class="imgPerfil"  src="./imagenalumno/sinfoto.png">';
															}else{
																echo '<img class="imgPerfil"  src="./imagenalumno/'.$row['imagenperfil'].'">';
															}
															
														}
													?>
													<!---->
													<br>
													<form action="actualizaralumno.php" method="post" enctype="multipart/form-data">
														<div class="form-group text-center">
														  <label for="exampleFormControlFile1"></label>
														  <input type="file" name="imagen" id="exampleFormControlFile1" style="margin-top: 35px; border: 2px dashed #3b9f97; border-radius: 3px;">
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

						<div class="col-lg-6">
							<div class="single-destinations">
								<div class="perfil text-center">
									<h4 class="text-center">
										<span class="text-center">información</span>                              	
									</h4>
									<p style="color: black;">
										Puedes actualizar tu información en cualquier momento.
									</p>
									<div>
										<div class="sidebar-widgets" style="padding-bottom: 10px;">
											<div class="widget-wrap" style="padding: 0px 0px;">
												<div class="single-sidebar-widget user-info-widget">




													<?php
														$default = $pdo->prepare('SELECT * FROM alumnos WHERE alumno_id=:alumno_id');
														$default->bindParam(':alumno_id', $_SESSION['user_id']);
														$default->execute();

														while($row = $default->fetch(PDO::FETCH_ASSOC)){
															/////////
															echo '
															<form action="actualizaralumno.php" method="post">
																<div class="mt-10">
																	<input type="text" value="'.$row['nombres'].'" name="nombres" placeholder="Nombres"  required class="single-input">
																</div>
																<div class="mt-10">
																	<input type="text" value="'.$row['apellidos'].'" name="apellidos" placeholder="Apellidos"  required class="single-input">
																</div>
																<div class="mt-10">
																	<input type="text" value="'.$row['establecimiento'].'" name="establecimiento" placeholder="Establecimiento"  required class="single-input">
																</div>

																<div class="input-group-icon mt-10">
																<div class="icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></div>
																<div class="form-select" id="default-select">
																	<select name="gradoAcademico">';
																switch($row['gradoacademico']){
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
																	
																echo'	</select>
																	</div>
																</div>

																<div class="input-group-icon mt-10">
																	<div class="icon"><i class="fa fa-calendar" aria-hidden="true"></i></div>';
																	if($row['datoscompletos'] == 0){
																		echo '<input type="date" name="fechaNacimiento" value="2000-01-01" placeholder="Fecha de nacimiento" required class="single-input">';
																	}else{
																		echo '<input type="date" name="fechaNacimiento" value="'.$row['fechanacimiento'].'" placeholder="Fecha de nacimiento" required class="single-input">';
																	}
																	
																echo '</div>
																<div class="text-center">
																	<br>
																	<input class="price-btn col-lg-6" type="submit" value="Guardar Información" name="crearAlumno">
																</div>
															</form>';

														}
													
													?>

												<!---->
												</div>
											</div>
										</div>		
									</div>
									
								</div>
							</div>
						</div>
						
																																				
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