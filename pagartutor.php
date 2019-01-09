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
                $puntosValidos = $valor['puntos'];
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
		<title>Pagar a Tutor | Tutoeri</title>

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
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
				      <div id="logo">
				        <a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
				      </div>
				      <nav id="nav-menu-container">
				        <ul class="nav-menu">
				          <li><a href="homeadministrador.php">Inicio</a></li>
				          <li><a href="materias.php">Materias</a></li>
				          <li class="activo"><a href="pagartutor.php">Pagar a Tutor</a></li>	          					          		          
				          <li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
				        </ul>
				      </nav><!-- #nav-menu-container -->					      		  
					</div>
				</div>
			</header><!-- #header -->
			  
			<!-- start banner Area -->
			<section class="about-banner relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12" style="margin-top: 0px; padding: 36px 0px">
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap" style="width: auto;">
				<div class="container">
		            <div class="row d-flex justify-content-center">
		                <div class="menu-content pb-40 col-lg-8">
		                    <div class="title text-center">
								<h1 class="mb-10">Pagar a Tutor</h1>
								<p>Busca a un tutor mediante su correo:</p>
							</div>
		                </div>
		            </div>						
					<div class="row d-flex justify-content-center">
							<section class="post-content-area">
									<div class="container">
										<div class="row">
											
											<div class="col-lg-12 sidebar-widgets">
												<div class="widget-wrap">
													<div class="single-sidebar-widget search-widget">
														<form class="search-form" action="pagartutor.php" method="post">
															<input placeholder="Buscar Tutor por correo" name="email" type="text" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Buscar Tutor por correo'">
															<button type="submit" name="buscar"><i class="fa fa-search"></i></button>
														</form>
													</div>
													
													<?php
														if(isset($_POST['buscar'])){
															$buscarTutores = $pdo->prepare('SELECT * FROM tutores WHERE email=:email');
															$buscarTutores->bindParam(':email',$_POST['email']);
															$buscarTutores->execute();

															foreach($buscarTutores as $valor){
																$valorAPagar = $valor['puntos'];
																if($valor['puntos']<200){
																	echo '<div class="alert alert-primary" role="alert">';
																	echo 'este tutor no tiene los puntos suficientes para cobrar su pago';
																	echo '</div>';
																}
																
																echo '
																<div class="single-sidebar-widget user-info-widget">
																	<img class="imgPerfil" src="./imagentutor/'.$valor['imagenperfil'].'" alt="">
																	<a href=""><h4>['.$valor['email'].']</h4></a>
																	<p>
																		['.$valor['nombres'].', '.$valor['apellidos'].']
																	</p>
																	<p>
																		['.$valor['descripcion'].']
																	</p>
																</div>
																<div class="single-sidebar-widget popular-post-widget" style="padding-bottom: 0px;">
																	<h4 class="popular-title">Equivalencia</h4>
																	<div class="popular-post-list">
																		<div class="jumbotron jumbotron-fluid">
																			<div class="container text-center">
																				<h1 class="display-4">['.$valor['puntos'].'] Puntos <span class="badge badge-secondary">[Q. '.$valor['puntos'].']</span></h1><br>
																				<p class="lead">Puntos equivalentes en quetzales.</p>
																			</div>
																		</div>													
																	</div>
																	
																	

																</div>
																<div class="single-sidebar-widget post-category-widget">
																	<h4 class="category-title">Horas de clase ['.$valor['horasclase'].']</h4>
																</div>
																<div class="single-sidebar-widget newsletter-widget">
																	<h4 class="newsletter-title">Formulario de pago</h4>
																	<form action="pagartutor.php" method="post">
																	<br>
																		<div class="mt-10">
																			<input type="text" value="'.$valor['tutor_id'].'" name="tutor_id" hidden="true">
																			<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)" placeholder="Número de cheque que se entrega" required="" class="single-input">
																		</div>
																		<div class="mt-10">
																			<input type="text" value="'.$valor['tutor_id'].'" name="tutor_id" hidden="true">
																			<input type="text" name="cantidad" onkeypress="return valida2(event)" placeholder="Cantidad a pagar" class="single-input">
																		</div>
																		<div class="text-center">
																			<br>';
																			if($valor['puntos']<200){
																				echo '<input class="btn btn-primary col-lg-6" type="submit" value="Pagar" disabled>';
																			}else{
																				echo '<input class="btn btn-primary col-lg-6" type="submit" value="Pagar" name="pagar">';
																			}
																echo'	</div>
																	</form>						
																</div>
																	
																';
															}
														}

													?>
														
													
													
														
																				
												</div>
											</div>
										</div>
									</div>	
								</section>
								
								<section class="post-content-area" style="width: 100%;">
										<div class="container">
											<div class="row">
												<?php
												if(isset($_GET['id'])){
													echo '<hr>';
													$_SESSION['idTutor'] = $_GET['id'];
													$_SESSION['cantidad'] = $_GET['cantidad'];
													$_SESSION['direccion'] = $_GET['direccion'];
													$_SESSION['mensaje'] = $_GET['mensaje'];
													
													
													echo '
													<div class="col-lg-12 sidebar-widgets">
														<div class="widget-wrap">
																
															<div class="single-sidebar-widget newsletter-widget">
																<h4 class="newsletter-title">Formulario de pago</h4>
																<br>
																<form action="#">
																	<input type="text" name="mensaje" value="'.$_SESSION['mensaje'].'" hidden="true">
																	<div class="mt-10">
																		<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)" placeholder="Número de cheque que se entrega" required="" class="single-input">
																	</div>
																	<div class="mt-10">
																		<input type="text" name="cantidad" value="Q.'.$_SESSION['cantidad'].'" onkeypress="return valida2(event)" disabled placeholder="Cantidad a pagar" required="" class="single-input">
																		
																	</div>
																	<div class="mt-10">
																	<input type="text" name="direccion" value="Direccion:  '.$_SESSION['direccion'].'" disable disabled placeholder="Cantidad a pagar" required="" class="single-input">
																		
																	</div>

																	
																	<div class="text-center">
																		<br>
																		<button class="btn btn-primary col-lg-6">Pagar</button>
																	</div>
																</form>							
															</div>							
														</div>
													</div>

													';
												}
											?>

												
											</div>
										</div>	
									</section>
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

  
  		<!-- preloader -->
		  <div id='preloader'><div class='preloader'></div></div>
			<!-- /preloader -->

			<!-- Modal -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Actualizar [Materia]</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
					<div class="col-lg-6">
						<div class="single-destinations">
							<!--<div class="thumb">
								<img src="img/hotels/d1.jpg" alt="">
							</div>-->
							<div class="perfil text-center">
								<h4 class=" text-center ">
									<span class="text-center">Foto de la materia</span>       
								</h4>
								<p style="color: black;">
									Elige una imagen para la materia.
								</p>
								<div>
									<div class="sidebar-widgets" style="padding-bottom: 2px;">
										<div class="widget-wrap">
											<div class="single-sidebar-widget user-info-widget">
												<img class="imgPerfil" src="img/profile.png" alt="">
												<br>
												<form>
													<div class="form-group text-center">
														<label for="exampleFormControlFile1"></label>
														<input type="file" class="" id="exampleFormControlFile1" style="margin-top: 35px; border: 2px dashed #3b9f97; border-radius: 3px;">
													</div>
													<div class="text-center">
														<button type="button" class="btn btn-primary">Guardar Imagen</button>
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
							<!--<div class="thumb">
								<img src="img/hotels/d2.jpg" alt="">
							</div>-->
							<div class="perfil text-center">
								<h4 class="text-center">
									<span class="text-center">información</span>      
								</h4>
								<p style="color: black;">
									Actualiza la información de la materia.
								</p>
								<div>
									<div class="sidebar-widgets" style="padding-bottom: 10px;">
										<div class="widget-wrap" style="padding: 0px 0px;">
											<div class="single-sidebar-widget user-info-widget">
												<form action="#">
													<div class="mt-10">
														<input type="text" name="nombreMateria" placeholder="Nombre de la materia" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Nombre de la materia'" required="" class="single-input">
													</div>
													<div class="mt-10">
														<input type="text" name="descripcion" placeholder="Descripción" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Descripción'" required="" class="single-input">
													</div>
													<div class="mt-10">
															<input type="text" name="descripcion" placeholder="Descripción" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Descripción'" required="" class="single-input">
														</div>
													<div class="text-center">
														<br>
														<button type="button" class="btn btn-primary">Actualizar Información</button>
													</div>
												</form>
											</div>
										</div>
									</div>		
								</div>
								
							</div>
						</div>
					</div>
					
																																			
				</div>
      </div>
    </div>
  </div>
</div>

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