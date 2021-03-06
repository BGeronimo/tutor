<?php
    require_once 'conection.php';
    session_start();

    $traerAdmin = $pdo->query('SELECT * FROM administradores WHERE administrador_id='.$_SESSION['user_id'].'');
    $correo = "";

    foreach($traerAdmin as $valor){
        $correo = $valor['email'];
    }

    if(isset($_POST['cheque'])){
        header('Location: pagartutor.php?id='.$_POST['tutor_id'].'&cantidad='.$_POST['cantidad'].'&direccion='.$_POST['direccion'].'&mensaje='.$_POST['mensaje_id'].'');
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
		<title>Inicio | Tutoeri</title>
		<!-- Autenticacion con firebase -->
		<script type="text/javascript" src="auth.js"></script>

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
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
				      <div id="logo">
				        <a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
				      </div>
				      <nav id="nav-menu-container">
				        <ul class="nav-menu">
				          <li class="activo"><a href="homeadministrador.php">Inicio</a></li>
				          <li><a href="materias.php">Materias</a></li>
				          <li><a href="pagartutor.php">Pagar a Tutor</a></li>	          					          		          
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
			<section class="destinations-area section-gap">
				<div class="container">
		            <div class="row d-flex justify-content-center">
		                <div class="menu-content pb-40 col-lg-8">
		                    <div class="title text-center">
		                        <h1 class="mb-10">¿Qué desea hacer?</h1>
		                    </div>
		                </div>
		            </div>						
					<div class="row text-center">
						<div class="col-sm-6">
						  <div class="card">
							<div class="card-body">
							  <h5 class="card-title">Administrar Materias</h5>
							  <p class="card-text">Actualizar materias, etc...</p>
							  <a href="materias.php" class="btn btn-primary">Ir <span class="lnr lnr-chevron-right" style="font-size: 10px;"></span></a>
							</div>
						  </div>
						</div>
						<div class="col-sm-6">
						  <div class="card">
							<div class="card-body">
							  <h5 class="card-title">Administrar Pagos a Tutores</h5>
							  <p class="card-text">Administrar los pagos o generar cheques para tutores.</p>
							  <a href="pagartutor.php" class="btn btn-primary">Ir <span class="lnr lnr-chevron-right" style="font-size: 10px;"></span></a>
							</div>
						  </div>
						</div>
					  </div>
					</div>
				</div>
				<br>
				<br>
				<br>
				<br>
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
                            $receptor = "";
                            $receptor = "Admin|".$_SESSION['user_id'];
                            $traerMensajes = $pdo->prepare('SELECT * FROM mensajes WHERE receptor=:receptor AND visto=0');
                            $traerMensajes->bindParam(':receptor',$receptor);
                            $traerMensajes->execute();
                            $cadena = array();
                            $emisor = array();
                            foreach($traerMensajes as $valor){
                                $cadena = explode("|",$valor['texto']);
                                $emisor = explode("|",$valor['emisor']);

                                $traerTutor = $pdo->prepare('SELECT * FROM tutores WHERE tutor_id=:tutor_id');
                                $traerTutor->bindParam(':tutor_id',$emisor[1]);
                                $traerTutor->execute();
                                $nombres = "";
                                $apellidos = "";
                                $img = "";
                                foreach($traerTutor as $valor2){
                                    $nombres = $valor2['nombres'];
                                    $apellidos = $valor2['apellidos'];
                                    $img = $valor2['imagenperfil'];
                                }
								echo '<div class="col-lg-4">';
                                echo '<form action="homeadministrador.php" method="post">';

                                echo '
                                <div class="single-destinations">
                                    <div class="details">
                                        <p style="color: white;">
                                        Un tutor te ha enviado el siguiente mensaje:
                                        </p>
                                        <div>
                                            <div class="sidebar-widgets">
                                                <div class="widget-wrap">
                                                    <div class="single-sidebar-widget user-info-widget">
                                                        <img src="imagentutor/'.$img.'" class="imgPerfil" alt="">
                                                        <a href="#"><h4 style="color: black;">'.$nombres.', '.$apellidos.'</h4></a>
                                                        <input type="text" value="'.$emisor[1].'" name="tutor_id" hidden="true">
                                                        <input type="text" value="'.$cadena[0].'" name="cantidad" hidden="true">
                                                        <input type="text" value="'.$cadena[1].'" name="direccion" hidden="true">
                                                        <input type="text" value="'.$valor['mensajes_id'].'" name="mensaje_id" hidden="true">	
                                                        <br>

														<div align="right">
															<h5>Hacer un cheque por: ['.$cadena[0].']</h5>
															<h5>A la dirección: ['.$cadena[1].'] </h5>
														</div>
                                                    </div>
                                                </div>
                                            </div>		
                                        </div>
                                        <div class="text-center">
                                            <button class="price-btn col-lg-5" name="cheque">Hacer cheque</button>
                                        </div>
                                    </div>
								</div> 
							</form>
						</div>';
                            }
                        ?>  
					<!---->																															
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