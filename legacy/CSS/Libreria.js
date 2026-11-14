var nav4 = window.Event ? true : false;

function acceptNum(evt) {
   // NOTE: Backspace = 8, Enter = 13, '0' = 48, '9' = 57
   var key = nav4 ? evt.which : evt.keyCode;
   return (key <= 13 || (key >= 48 && key <= 57));
}

// Pasa a mayusculas
function cUpper(cObj) {
   cObj.value=cObj.value.toUpperCase();
}

function toUpper(field) {
   field.value = field.value.toUpperCase()
}

function fNumbers(e) { 
   var key = window.Event ? e.which : e.keyCode;
   return ((key >= 48 && key <= 57) || (key == 8)) 
}

function fFloat(e) { 
   var key = window.Event ? e.which : e.keyCode;
   return ((key >= 48 && key <= 57) || (key == 8) || (key == 46)) 
}

function fDate(e) { 
   var key = window.Event ? e.which : e.keyCode;
   return ((key >= 48 && key <= 57) || (key == 8) || (key == 45)) 
}

function fHour(e) { 
   var key = window.Event ? e.which : e.keyCode;
   return ((key >= 48 && key <= 57) || (key == 8) || (key == 58)) 
}
	    jQuery(document).ready(function() {
        jQuery('.fmNumero').keypress(function(tecla) {
           if(tecla.charCode < 48 || tecla.charCode > 57) return false;
              });
        });
