<?php

# Cargamos la librería dompdf.
require_once("../../estilo/application/dompdf/dompdf_config.inc.php");

function pdfrender($html,$nombre,$download=false,$render=true){
  $html= str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'), array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&Ntilde;'), $html);
  if(!$render){
    echo $html;
    exit;
  }
  $dompdf = new DOMPDF();
  $dompdf->load_html($html);
  $dompdf->set_paper('a4', 'portrait');
  $dompdf->render();

  $dompdf->stream("$nombre.pdf", array("Attachment" => $download));

  exit(0);
}

function pdftemplate($template,$values){
  if(file_exists($template)){
    $gestor = fopen($template, "r");
    $html = fread($gestor, filesize($template));
    fclose($gestor);
  } else {
    $html = $template;
  }

  $a_html = explode("{{",$html);
  $html = "";
    foreach ($a_html as &$value) {
      $pos = strpos($value, "}}");
      if ($pos !== false){
        $a_a_html = explode("}}",$value);
        $html .= $values[$a_a_html[0]];
        $html .= $a_a_html[1];
      }
      else {
        $html .= $value;
      }
    }

  return $html;
}
