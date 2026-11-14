<meta HTTP-EQUIV="Content-Type" content="text/html; charset=utf-8" />
<html>
<head>
   <title>TEXTILERIA</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
</head>
<body>
<form action="Mnu1000.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Cambio de Clave</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   <table>
      <tr>
      <td>Código:</td>
      <td>{$scNombre}</td>
      </tr><tr>
      <td>Clave Antigua:</td>
      <td><input type="password" maxlength="30" name="pcClave" autofocus/></td>
      </tr><tr>
      <td>Clave Nueva:</td>
      <td><input type="password" maxlength="30" name="pcClave1"/></td>
      </tr><tr>
      <td>Clave (Reingrese):</td>
      <td><input type="password" maxlength="30" name="pcClave2"/></td>
      </tr>
   </table>
   <table>
      <tr>
      <td></td>
      <td><input type="submit" name="Boton1" value="Grabar"/></td>
      <td><input type="button" value="Salir" onclick="self.location='index.php'" /></td>
      </tr>
   </table>
   </div>
   </div>
   <div id="content">
   </div>
   <div id="footer">
   </div>
   </div>
</form>
</body>
</html>