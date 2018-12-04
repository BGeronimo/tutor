var userUID;
var user, token;

var ref = firebase.database().ref().child("usuario");
var usuario = {};

var email = document.getElementById("email");

function signFB(){
    var provider = new firebase.auth.FacebookAuthProvider();
    firebase.auth().signInWithPopup(provider).then(function(result) {
        // This gives you a Facebook Access Token. You can use it to access the Facebook API.
        var token = result.credential.accessToken;
        // The signed-in user info.
        var user = result.user;

        usuario = {
            nombre   : result.user.displayName,
            email    : result.user.email,
            uid      : result.user.uid,
            photoURL : result.user.photoURL        
        }

        agregarUsuario(usuario, usuario.uid);

        
        
    }).catch(function(error) {
        // Handle Errors here.
        var errorCode = error.code;
        var errorMessage = error.message;
        // The email of the user's account used.
        var email = error.email;
        // The firebase.auth.AuthCredential type that was used.
        var credential = error.credential;
        
        
      });

}


function signGg(){
    var provider = new firebase.auth.GoogleAuthProvider();
    provider.addScope('https://www.googleapis.com/auth/contacts.readonly');
    //defino en que leguaje le va a salir el Popup de autenticacion
    firebase.auth().languageCode = 'es';
    firebase.auth().signInWithPopup(provider).then(function(result) {
        
    usuario = {
        nombre   : result.user.displayName,
        email    : result.user.email,
        uid      : result.user.uid,
        photoURL : result.user.photoURL        
      }
       
      agregarUsuario(usuario, usuario.uid);
      
    }).catch(function(err){
    console.log(err);
    }) 
}



function logOut(){
    firebase.auth().signOut().then(function() {
        // Sign-out successful.
        console.log('te has salido xdxd');
        window.location.href = "index.php"
        }).catch(function(error) {
        // An error happened.
        console.log(error);
    });

    
}


function agregarUsuario(usuario, uid){

    if(ref.child(uid)){
        ref.child(uid).update(usuario);
        
        var base = "procesar.php?indeciso=1";
        var enviarEmail = "&email=" + usuario.email;
        var enviarFirebase = "&firebase_id="+usuario.uid;
        var rutaCompleta = base.concat(enviarEmail,enviarFirebase);
        location.href=rutaCompleta;
         
    }
    
} 


firebase.auth().onAuthStateChanged(function(user){
    if (user) {
      console.log("Tienes una sesion activa");
      getDataDB(user.uid);
      //mostrarLogout()      
    }else{
      console.log("No se detecta ninguna sesion");
      //mostrarLogin()
    }
});


