<?php

# Cargamos la librería dompdf.
require_once("../../estilo/application/dompdf/dompdf_config.inc.php");

# Contenido HTML del documento que queremos generar en PDF.
$html='
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ejemplo de Documento en PDF.</title>
</head>
<body>

<h2>Ingredientes para la realización de Postres.</h2>
<p>Ingredientes:</p>
<dl>
<dt>Chocolate</dt>
<dd>Cacao</dd>
<dd>Azucar</dd>
<dd>Leche</dd>
<dt>Caramelo</dt>
<dd>Azucar</dd>
<dd>Colorantes</dd>
</dl>

</body>
</html>';


$html= str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'), array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&Ntilde;'), $html);
/*/
echo $html;
exit;//*/
  $dompdf = new DOMPDF();
  $dompdf->load_html($html);
  $dompdf->set_paper('letter', 'portrait');
  $dompdf->render();

  $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));

  exit(0);
