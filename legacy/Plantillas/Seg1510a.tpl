<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Ejemplo sencillo de AJAX</title>

<!--VENTANA MODAL-->
<script type="text/javascript" src="CSS/contacto-modal/jquery-1.8.3.min.js"></script>

<link href="CSS/contacto-modal/fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="CSS/contacto-modal/fancybox/jquery.fancybox-1.3.4.js"></script>

<script type="text/javascript" src="CSS/contacto-modal/js-contacto-modal-ynnova.js"></script>
<style type="text/css">
#FormularioContacto{
	font-family:Arial, Helvetica, sans-serif;
}
</style>

<!--MALOGRA LA VENTANA MODAL-->
<!--<script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script>-->

<link rel="stylesheet" href="CSS/jquery-ui-1.11.0/jquery-ui.min.css">
<script type="text/javascript" src="CSS/jquery-ui-1.11.0/jquery-ui.min.js"></script>
<script type="text/javascript" src="CSS/jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function() {
   $("#datepicker").datepicker();
   $("#datepicker2").datepicker();
});

jQuery(function($){
      $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '&#x3c;Ant',
            nextText: 'Sig&#x3e;',
            currentText: 'Hoy',
            monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
            'Jul','Ago','Sep','Oct','Nov','Dic'],
            dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
            dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
            weekHeader: 'Sm',
            dateFormat: 'yy-mm-dd',
			//dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
      $.datepicker.setDefaults($.datepicker.regional['es']);
}); 

</script>

    <script type="text/javascript">
	//valida telefono sea solo numeros
	    jQuery(document).ready(function() {
        jQuery('.input_tel').keypress(function(tecla) {
           if(tecla.charCode < 48 || tecla.charCode > 57) return false;
              });
        });
		//jQuery('.input_apellido').keypress(function(tecla) {
        //   if((tecla.charCode < 97 || tecla.charCode > 122) && (tecla.charCode < 65 || tecla.charCode > 90) && (tecla.charCode != 45)) return false;
        //});
		
	
        jQuery("a").mouseenter(function (e) {             
            var posMouse = e.pageX - this.offsetLeft; 
            var textoTooltip = jQuery(this).attr("title"); 
			
            if (textoTooltip.length > 0) {
                jQuery(this).append('<div class="tooltip">' + textoTooltip + '</div>');
                jQuery("a > div.tooltip").css("left", "" + posMouse - 103 + "px");
                jQuery("a > div.tooltip").fadeIn(300);
            }
        });

        jQuery("a").mouseleave(function () {             
            jQuery("a > div.tooltip").fadeOut(300).delay(300).queue(function () {
                jQuery(this).remove();
                jQuery(this).dequeue();
            });
        });
    </script>


<script type="text/javascript">
$(document).ready(function(){
          $("#formMahasiswa").validate();
       });


function realizaProceso(valorCaja1, valorCaja2){
        var parametros = {
                "valorCaja1" : valorCaja1,
                "valorCaja2" : valorCaja2
        };
        $.ajax({
                data:  parametros,
                url:   'ejemplo_ajax_proceso.php',
                type:  'post',
                beforeSend: function () {
                        $("#resultado").html("Procesando, espere por favor...");
                },
                success:  function (response) {
                        
						$("#resultado").html(response);
						
                }
        });
						
}

function MyFuncion(Valor){
        var parametros = {
                "pcEmail" : Valor
        };
        $.ajax({
                data:  parametros,
                url:   'ejemplo_ajax_proceso.php',
                type:  'post',
                beforeSend: function () {
                        $("#resultado").html("Procesando, espere por favor...");
                },
                success:  function (response) {
				if (response!='OK'){
				alert(response);
				}
                }
        });
}

function MyFuncion1(Valor){
  var m = Valor;
  var expreg = /^\d+\.\d\d$/;
  if(expreg.test(m))
	alert("OK"); 
  else 
    alert("La matrícula NO es correcta"); 
} 

</script>

</head>

<body>
<form id="formMahasiswa" method="post"> 

<fieldset>
<label for="tel">Teléfono:</label>
<input type="text" id="tel" class="input_tel" />
</fieldset>

datepikeytwser
<!--<input type="text" name="datepicker"  id="datepicker" readonly="readonly" size="12" />-->
<input type="text" name="datepicker"  id="datepicker" size="12" />

datepikeytddd
<!--<input type="text" name="datepicker"  id="datepicker" readonly="readonly" size="12" />-->
<input type="text" name="datepicker2"  id="datepicker2" size="12" />



<center>
    <h2>Tooltips con JQuery</h2>
    <a href="#" title="Este enlace actualmente no lleva a ningÃºn lado pero sirve para mostrarte el uso del tooltip">Tooltip en un enlace</a>
    <div class="tooltip">Texto del tooltip</div>
    </center>

email zzzz
<label for="nombre">Nombre</label>
<input id="nombre" name="nombre" type=text placeholder="Escribe tu nombre completo" required autofocus>
</li>
<li>
<label for="email">Email</label>
<input id="email" name="email" type="email" placeholder="ejemplo@dominio.com" required>
</li>
<li>
<label for="telefono">Teléfono</label>
<input id="telefono" name="telefono" type="tel" placeholder="Ej. +4466212112154" required>
</li>
<li>
<label for="pais">País</label>
<input id="pais" name="pais" type="text" required>
</li>
<td><input type="submit" value="Submit" class="tooltip_elemento center"/></td>
xxxxx
<input type="text" name="pcEmail1" id="pcEmail1" value="1" onblur="MyFuncion1(this.value);"/>



Introduce valor 1

<input type="text" name="caja_texto" id="valor1" value="0"/> 


Introduce valor 2

<input type="text" name="caja_texto" id="valor2" value="0"/>

Realiza suma

<input type="button" href="javascript:;" onclick="realizaProceso($('#valor1').val(), $('#valor2').val());return false;" value="Calcula"/>

<br/>

Resultado: <span id="resultado">0</span>
<!--VENTANA MODAL aformulario.html-->

<h1>Formulario de contacto modal Ynnova</h1>
<a href="#">Inicio</a> - <a href="#">Productos</a> - <a href="#">Ubicaci&oacute;n</a> - 
<a href="Seg9009.php" class="Fancy">
<!--<a href="aformulario-contacto.html" class="Fancy">-->
<strong>Contacto</strong></a>

<!--****-->


</form>
</body>

</html>



