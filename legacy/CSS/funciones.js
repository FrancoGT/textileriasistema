addEvent(window, 'load', inicializarEventos, false);

function inicializarEventos() {
   var select1 = document.getElementById('pcCodPry');
   addEvent(select1, 'change', mostrarMaterias, false);
}

var conexion1;

function mostrarMaterias(e) {
   var codigo = document.getElementById('pcCodPry').value;
   if (codigo != 0) {
      conexion1 = crearXMLHttpRequest();
      conexion1.onreadystatechange = procesarEventos;
      conexion1.open('GET', 'Clases/pagina1.php?Id=' + codigo, true);
      conexion1.send(null);
   } else {
      var select2 = document.getElementById('pcCodPrc');
      select2.options.length = 0;
   }
}

function procesarEventos() {
   if (conexion1.readyState == 4) {
      var d = document.getElementById('espera');
      d.innerHTML  =  '';
      var select2 = document.getElementById('pcCodPrc');
      select2.options.length = 0;
      var datos = eval("(" + conexion1.responseText + ")");
      for (var i = 0; i < datos.length; i++) {
          var option = document.createElement("option");
          option.text = datos[i].Texto;
          option.value = datos[i].Valor;
          select2.appendChild(option);
      }
   } else {
      var d = document.getElementById('espera');
      d.innerHTML = '<img src="CSS/Cargando.gif" height="15" width="25">';
	  //height="25" width="25"
   }
}


//***************************************
//Funciones comunes a todos los problemas
//***************************************
function addEvent(elemento, nomevento, funcion, captura) {
   if (elemento.attachEvent) {
      elemento.attachEvent('on'+nomevento, funcion);
      return true;
   } else if (elemento.addEventListener) {
      elemento.addEventListener(nomevento, funcion, captura);
      return true;
   } else
      return false;
}

function crearXMLHttpRequest() {
   var xmlHttp = null;
   if (window.ActiveXObject) 
      xmlHttp  =  new ActiveXObject("Microsoft.XMLHTTP");
   else if (window.XMLHttpRequest) 
      xmlHttp  =  new XMLHttpRequest();
   return xmlHttp;
}
