<?php
session_start();

require 'db_controller.php';

require 'xls_controller.php';

require 'pdf_controller.php';
date_default_timezone_set("America/Mexico_City");

class request{

	public $app;
        public $_app;
	public $param;
        public $anchor;
        public $root;

	public function __construct(){

		$this -> seg();

	}

	public function seg(){

            $var = explode('/',$_GET['url']);

            $this->app = array_shift($var);
            $this->_app=  $this->app;
            $this->param = $var;
            $this->anchor="http://".$_SERVER['HTTP_HOST'].str_replace("index.php","",$_SERVER['SCRIPT_NAME']);
            $this->root=$_SERVER['DOCUMENT_ROOT'].str_replace("index.php","",$_SERVER['SCRIPT_NAME']);
            if($this->_app==""){
                $this->_app="Home";
            }
            $data = json_decode(file_get_contents("php://input"));
            $param = array();
            foreach( $this->param as $clave => $valor){
              $param[$clave] = $valor;
            }
            foreach( $data as $clave => $valor){
              $param[$clave] = $valor;
            }
            $get_param = $_GET;
            unset($get_param['url']);
            foreach( $get_param as $clave => $valor){
              $param[$clave] = $valor;
            }
            foreach( $_FILES as $clave => $valor){
              $param['FILE_'][$clave] = $valor;
            }
            foreach( $_POST as $clave => $valor){
              $param[$clave] = $valor;
            }
            $this->param=$param;

        }


}

function dias_que_corresponde($años_){
  switch($años_){
    case '1': case '2': case '3':
      $dias = 10;
      break;
    case '4':
      $dias = 12;
      break;
    case '5': case '6': case '7': case '8': case '9':
      $dias = 14;
      break;
    case '10': case '11': case '12': case '13': case '14':
      $dias = 16;
      break;
    case '15': case '16': case '17': case '18': case '19':
      $dias = 18;
      break;
    case '20':
      $dias = 20;
      break;
    default:
      $dias = 0;
      break;
  }


  switch($años_){
    case '5':
      $extras = 3;
      break;
    case '10':
      $extras = 4;
      break;
    case '15':
      $extras = 5;
      break;
    default:
      $extras = 0;
      break;
  }
  return $extras + $dias;
}

function get_ip(){
      $ip;
      if (isset($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      }
      else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      else if(isset($_SERVER['HTTP_X_FORWARDED'])){
      $ip = $_SERVER['HTTP_X_FORWARDED'];
      }
      else if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
          $ip = $_SERVER['HTTP_FORWARDED_FOR'];
      }
      else if(isset($_SERVER['HTTP_FORWARDED'])){
          $ip = $_SERVER['HTTP_FORWARDED'];
      }
      else if(isset($_SERVER['REMOTE_ADDR'])){
          $ip = $_SERVER['REMOTE_ADDR'];
      }
      else $ip ='0.0.0.0';

      return $ip;
}

function generar_token(){
  return date("ymdHis").str_pad(rand(0,999), 3, "0", STR_PAD_LEFT);
}

function ObtenerNavegador($user_agent) {
  $navegadores = array(
    'Opera' => 'Opera',
    'Mozilla Firefox'=> '(Firebird)|(Firefox)',
    'Galeon' => 'Galeon',
    'Chrome'=>'Gecko',
    'MyIE'=>'MyIE',
    'Lynx' => 'Lynx',
    'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
    'Konqueror'=>'Konqueror',
    'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
    'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
    'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
    'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
      );
      foreach($navegadores as $navegador=>$pattern){
             if (strtoupper($pattern)==strtoupper($user_agent))
             return $navegador;
          }
      return 'Desconocido';
}

function set_header(){
  if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Content-Type: application/json; charset=UTF-8');
      //If required
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
  }
}

function valid_token($token){
  if($token!=""){
    $valid;
    dbquery_call("call sso_isValidTokenAccess( '$token', @valid);"," select @valid;",$valid);
    return !!$valid['@valid'];
  }
  return false;
}

function fun_api($api){

        if (function_exists($api->app)){
               return call_user_func($api->app,$api->param);
        } else {
          return array("access"=>false, "execute" => "", "msg" => "Access not found");
        }
}

// auth function

function get_profile($param){
  $app = $param['apps'];
  $token = $param['accessToken'];
  if (valid_token($token)){
    $q_profile="select  distinct(profile) as profi,permissions,nombre from `sso`.`view_profile_token` WHERE  app = '$app' and accessToken = '$token';";
    $result = dbquery($q_profile);
    if($result->num_rows>0){
      $row = mysqli_fetch_assoc($result);
      $row['fecha']=date("d-M-Y");
      return array('access'=>true,"ls"=>$row);
    }
    return array('access'=> false, 'execute'=>'toSSO',"extras"=>$result);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function attack_set($param){
  $ip = get_ip();
  $atk="Call sso.sso_attack_add('USUARIO NO AUTENTIFICADO','$ip','".ObtenerNavegador($_SERVER['HTTP_USER_AGENT'])."','SE INTENTO ACCEDER SIN INICIAR SECCION','')";
  dbquery($atk);
  return array("access"=>false,"execute"=>"toSSO","msg"=>"not_logged");
}

function guard_session($param){
  $apps = $param['apps'];
  $url = $param['url'];
  $token = $param['accessToken'];
  if($token!=""){
      $qq="call sso.sso_isValidPermissionsByToken ('".$token."', '".$apps."', '".$url."',@p,@r,@m)";
      $rowqq=array();
      $error;
      dbquery_call($qq,"select @r,@m,@p",$rowqq,$error);
      if(isset($param['debug'])&&$param['debug']) print_r($rowqq);
        if($rowqq['@r']==2){ // sin permisos
          $_SESSION['error_profile']=$rowqq['@m'];
          return array("access"=>false,"execute"=>"toSSO","msg"=>"not_permission");
        }
        if($rowqq['@r']==100){ //time_out
              $_SESSION['error_profile']=$rowqq['@m'];
              return array("access"=>false,"execute"=>"logoff","msg"=>"time_out");
        }
        if($rowqq['@r']==0){ //LOGOFF
              return array("access"=>false,"execute"=>"logoff","msg"=>"LOGOFF");
        }
        return array("access"=>true);
      }
      else{
        return attack_set($param);
      }



}

// Empleados

function get_empleados($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $result = dbquery("select * from `sso`.`view_empleados` order by nombre;");
    $empleados;
    while($row = mysqli_fetch_assoc($result)){
      $empleados[] = $row;
    }
    return array('access'=> true, "ls"=>$empleados);
  }
    return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");

}

function get_empleado($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $result = dbquery("select * from `sso`.`view_get_empleado` where id='$id'");
    $empleados;
    while($row = mysqli_fetch_assoc($result)){
      foreach($row as $key => $value){
        if(json_encode(array($value))==''){
          $row[$key] = utf8_encode($value);
        }
      }
      $empleados = $row;
    }
    $extras;
    if($empleados['estado_nacimiento']!=""){
      $id =$empleados['estado_nacimiento'];
      $qget = "select * from `RH`.`view_municipios` where estado_id='$id';";
      $result = dbquery($qget);
      $array=array();
      if ($result->num_rows>0) {
        while($row = mysqli_fetch_assoc($result)){
          if(json_encode(array($row['descripcion']))==''){
            $row['descripcion'] = utf8_encode($row['descripcion']);
          }
          $array[]=$row;
        }
      }
      $extras['ciudad_nacimiento']=$array;
    }
    if($empleados['estado']!=""){
      $id =$empleados['estado'];
      $qget = "select * from `RH`.`view_municipios` where estado_id='$id';";
      $result = dbquery($qget);
      $array=array();
      if ($result->num_rows>0) {
        while($row = mysqli_fetch_assoc($result)){
          if(json_encode(array($row['descripcion']))==''){
            $row['descripcion'] = utf8_encode($row['descripcion']);
          }
          $array[]=$row;
        }
      }
      $extras['municipio']=$array;
    }
    return array('access'=> true, "ls"=>$empleados, "extras" => $extras);
  }
    return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");

}

function set_empleado($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $cia =utf8_decode(strtoupper( $param['cia']));
    $nivel =utf8_decode(strtoupper( $param['nivel']));
    $numero_emp =utf8_decode(strtoupper( $param['numero_emp']));
    $status_rh =utf8_decode(strtoupper( $param['status_rh']));
    $pagadora =utf8_decode(strtoupper( $param['pagadora']));
    $razon_social =utf8_decode(strtoupper( $param['razon_social']));
    $nombre =utf8_decode(strtoupper( $param['nombre']));
    $segundo_nombre =utf8_decode(strtoupper( $param['segundo_nombre']));
    $paterno =utf8_decode(strtoupper( $param['paterno']));
    $materno =utf8_decode(strtoupper( $param['materno']));
    $fingreso =utf8_decode(strtoupper( $param['fingreso']));
    $puesto =utf8_decode(strtoupper( $param['puesto']));
    $division =utf8_decode(strtoupper( $param['division']));
    $departamento =utf8_decode(strtoupper( $param['departamento']));
    $lugar_prestacion =utf8_decode(strtoupper( $param['lugar_prestacion']));
    $horario =utf8_decode(strtoupper( $param['horario']));
    $genero =utf8_decode(strtoupper( $param['genero']));
    $fnacimiento =utf8_decode(strtoupper( $param['fnacimiento']));
    $nacionalidad =utf8_decode(strtoupper( $param['nacionalidad']));
    $estado_nacimiento =utf8_decode(strtoupper( $param['estado_nacimiento']));
    $ciudad_nacimiento =utf8_decode(strtoupper( $param['ciudad_nacimiento']));
    $numero_ss =utf8_decode(strtoupper( $param['numero_ss']));
    $numero_infonavit =utf8_decode(strtoupper( $param['numero_infonavit']));
    $rfc =utf8_decode(strtoupper( $param['rfc']));
    $curp =utf8_decode(strtoupper( $param['curp']));
    $tsangre =utf8_decode(strtoupper( $param['tsangre']));
    $nivel_estudios =utf8_decode(strtoupper( $param['nivel_estudios']));
    $carrera =utf8_decode(strtoupper( $param['carrera']));
    $titulo =utf8_decode(strtoupper( $param['titulo']));
    $direccion =utf8_decode(strtoupper( $param['direccion']));
    $cruces =utf8_decode(strtoupper( $param['cruces']));
    $colonia =utf8_decode(strtoupper( $param['colonia']));
    $estado =utf8_decode(strtoupper( $param['estado']));
    $municipio =utf8_decode(strtoupper( $param['municipio']));
    $cp =utf8_decode(strtoupper( $param['cp']));
    $personal_email =utf8_decode(strtoupper( $param['personal_email']));
    $tcasa =utf8_decode(strtoupper( $param['tcasa']));
    $cell =utf8_decode(strtoupper( $param['cell']));
    $estado_civil =utf8_decode(strtoupper( $param['estado_civil']));
    $emergencias_nombre =utf8_decode(strtoupper( $param['emergencias_nombre']));
    $emergencias_parentesco =utf8_decode(strtoupper( $param['emergencias_parentesco']));
    $emergencias_cel =utf8_decode(strtoupper( $param['emergencias_cel']));
    $emergencias_oficina =utf8_decode(strtoupper( $param['emergencias_oficina']));
    $emergencias_casa =utf8_decode(strtoupper( $param['emergencias_casa']));
    $banco =utf8_decode(strtoupper( $param['banco']));
    $clabe =utf8_decode(strtoupper( $param['clabe']));
    $dia_pago =utf8_decode(strtoupper( $param['dia_pago']));
    $casa_propia =utf8_decode(strtoupper( $param['casa_propia']));
    $medio_transporte =utf8_decode(strtoupper( $param['medio_transporte']));
    $id = (isset($param['id']))?$param['id']:0;
    $qEmp = "call `sso`.`sso_setEmpleado`('$cia', 	'$nivel', 	'$numero_emp', 	'$status_rh', 	'$pagadora', 	'$razon_social', 	'$nombre', 	'$segundo_nombre', 	'$paterno', 	'$materno', 	'$fingreso', 	'$puesto', 	'$division', 	'$departamento', 	'$lugar_prestacion', 	'$horario', 	'$genero', 	'$fnacimiento', 	'$nacionalidad', 	'$estado_nacimiento', 	'$ciudad_nacimiento', 	'$numero_ss', 	'$numero_infonavit', 	'$rfc', 	'$curp', 	'$tsangre', 	'$nivel_estudios', 	'$carrera', 	'$titulo', 	'$direccion', 	'$cruces', 	'$colonia', 	'$estado', 	'$municipio', 	'$cp', 	'$personal_email', 	'$tcasa', 	'$cell', 	'$estado_civil', 	'$emergencias_nombre', 	'$emergencias_parentesco', 	'$emergencias_cel', 	'$emergencias_oficina', 	'$emergencias_casa', 	'$banco', 	'$clabe', 	'$dia_pago', 	'$casa_propia', 	'$medio_transporte', 	'$id', @result)";
    $flags = "select @result";
    $array = array();
    $error = "";
    dbquery_call($qEmp,$flags,$array,$error);
    if($error==""){
      return array('access'=> true, "ls"=>$array);
    } else {
      return array('access'=> false, 'execute'=>'msg',"msg"=>"Error: no se alcanzo la base de datos");
    }
  }
    return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_empresas($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_empresas`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_puestos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_puestos`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_division($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_division`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_lugar($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_lugar`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_horarios($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_horarios`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_estados($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_estados`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_sangre($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_sangre`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_estudios($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_estudios`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_civil($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_civil`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
       if(json_encode(array($row['descripcion']))==''){
         $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_bancos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_bancos`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_municipios($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $qget = "select * from `RH`.`view_municipios` where estado_id='$id';";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_datos_medicos_empleados($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $qDM = "select * FROM RH.view_datos_medicos where id_emp = '$id' ;";
    $result = dbquery($qDM);
    $array=array();
    while($row = mysqli_fetch_assoc($result)){
      $array[] = $row;
    }
    $datos_medicos=$array;
    //print_r($array);
    $qDM = "select * FROM RH.view_parentesco where id_empleado = '$id' ;";
    $result = dbquery($qDM);
    $array=array();
    while($row = mysqli_fetch_assoc($result)){
      $array[] = $row;
    }
    $parentescos=$array;
    //print_r($array);

    return array('access'=> true, 'ls'=>array("dm"=>$datos_medicos, "fm"=>$parentescos));
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_datos_medicos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = isset($param['id'])?$param['id']:'0';
    $dm_descripcion=utf8_decode(strtoupper($param['dm_descripcion']));
    $tipo=$param['dm_tipo'];
    $id_emp=$param['id_emp'];
    $id_par=$param['id_par'];
    $qSDM = "CALL `RH`.`set_datos_medicos`('$dm_descripcion', '$tipo','$id','$id_par','$id_emp',@result)";
    $flags = "select @result";
    $array;
    dbquery_call($qSDM,$flags,$array);
    return array('access'=> true, 'ls'=>$array);
  }
    return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function del_datos_medicos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['id'];
    $q_Del = "call `RH`.`del_datos_medicos`($id);";
    dbquery($q_Del);
    return array('access'=> true);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");

}

function get_familia($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $qDM = "select * FROM RH.view_parentesco where id_empleado = '$id' ;";
    $result = dbquery($qDM);
    $array=array();
    while($row = mysqli_fetch_assoc($result)){
      $array[] = $row;
    }

    $qget = "select * FROM RH.view_sangre;";
    $result = dbquery($qget);
    $array_s=array();
    while($row = mysqli_fetch_assoc($result)){
      $array_s[] = $row;
    }

    $qget = "select * FROM RH.view_catalogo_parentescos;";
    $result = dbquery($qget);
    $array_p=array();
    while($row = mysqli_fetch_assoc($result)){
      $array_p[] = $row;
    }

    return array('access'=> true, "ls" => $array, "pareintes" => $array_p, "sangre" => $array_s );
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_familia($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = isset($param['id'])?$param['id']:'0';
    $fnacimiento = $param['fnacimiento'];
    $id_empleado = $param['id_empleado'];
    $materno = utf8_decode(strtoupper($param['materno']));
    $nombre = utf8_decode(strtoupper($param['nombre']));
    $paterno = utf8_decode(strtoupper($param['paterno']));
    $tipo = $param['tipo_id'];
    $tsangre = $param['tsangre_id'];
    $q_FAM = "CALL `RH`.`set_familia`('$id','$token','$id_empleado','$tipo','$paterno','$materno','$nombre','$fnacimiento','$tsangre')";
    $error='';
    dbquery($q_FAM, $error);
    if($error== ''){
      return array('access'=> true);
    }
    return array('access'=> false, 'execute'=>'toSSO',"msg"=>$error);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_movimientos($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $qDM = "select * FROM RH.view_movimiento_salarial where id_emp = '$id' ;";
    $result = dbquery($qDM);
    $array=array();
    while($row = mysqli_fetch_assoc($result)){
      $row['incremento']=number_format($row['incremento'],2);
      $array[] = $row;
    }

    $qget = "select * FROM RH.view_tipo_movimiento;";
    $result = dbquery($qget);
    $array_p=array();
    while($row = mysqli_fetch_assoc($result)){
      $array_p[] = $row;
    }

    return array('access'=> true, "ls" => $array, "tipos" => $array_p);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_movimiento($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $anio = $param['anio'];
    $id_emp = $param['id_emp'];
    $incremento = $param['incremento'];
    $mes = $param['mes'];
    $tipo = $param['tipo_id'];
    $array = array();
    $q_MS = "call `RH`.`set_movimiento_salarial`( '$id_emp', '$tipo', '$mes', '$anio', '$incremento' , @msn);";
    dbquery_call($q_MS,'select @msn', $array);
    return array('access'=> true, "ls" => $array);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_expediente($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    $qExp = "select * FROM `sso`.`view_data_empleado` where id = '$id' ;";
    $result = dbquery($qExp);
    $array=array();
    $array = mysqli_fetch_assoc($result);
    if($array['documentos_personales']==''){
      $array['documentos_personales']="00000000000000000000";
    }

    if($array['documentos_internos']==''){
      $array['documentos_internos']="00000000000000000000";
    }


    foreach($array as $key => $value){
      if(json_encode(array($value))==''){
        $array[$key] = utf8_encode($value);
      }
    }


    $temp = $array['documentos_personales'];
    $array['documentos_personales'] = array();
    for($i=0;$i<strlen($temp);$i++){
      $array['documentos_personales'][$i]=!!$temp[$i];
    }
    $temp = $array['documentos_internos'];
    $array['documentos_internos'] = array();
    for($i=0;$i<strlen($temp);$i++){
      $array['documentos_internos'][$i]=!!$temp[$i];
    }
    $avatar=array();
    dbquery_call("call `RH`.`get_avatar_empleados`('$id',@path,@nombre);","select @path,@nombre;",$avatar);
    $avatar['src'] = $avatar['@path'];
    unset($avatar['@path']);
    $avatar['alt'] = $avatar['@nombre'];
    unset($avatar['@nombre']);
    return array('access'=> true, "ls" => $array,"avatar"=>$avatar);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_expediente($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['index'];
    foreach($param['documentos_internos'] as $key => $value){
      if($value==""){
        $param['documentos_internos'][$key] = "0";
      }
    }
    foreach($param['documentos_personales'] as $key => $value){
      if($value==""){
        $param['documentos_personales'][$key] = "0";
      }
    }
    $param['documentos_internos'] = implode('', $param['documentos_internos']);
    $param['documentos_personales'] = implode('', $param['documentos_personales']);
    $documentos_internos = $param['documentos_internos'];
    $documentos_personales = $param['documentos_personales'];

    $qExp = "call `RH`.`set_expediente`('$id', '$documentos_internos', '$documentos_personales', @msn)";
    $array=array();

    dbquery_call($qExp,'select @msn',$array);

    return array('access'=> true, "ls" => $array);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_picture_expediente($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $error = array(0 => 'UPLOAD_ERR_OK', 1 => 'UPLOAD_ERR_INI_SIZE', 2 => 'UPLOAD_ERR_FORM_SIZE', 6 => 'UPLOAD_ERR_NO_TMP_DIR', 7 => 'UPLOAD_ERR_CANT_WRITE', 8 => 'UPLOAD_ERR_EXTENSION', 3 => 'UPLOAD_ERR_PARTIAL');
    $id = $param['id'];
    if($param['FILE_']['image']['error'] == 0){
      $folder = "./";
      $file_name = $param['FILE_']['image']['name'];
      $path = $folder.$file_name;
      $array=array();
      $flag = 0;
      dbquery_call("call `RH`.`get_avatar_empleados`('$id',@path,@nombre);","select @path,@nombre;",$array);
      if(copy($param['FILE_']['image']['tmp_name'], $path)){
        if(!!$array['@path']){
          unlink($array['@path']);
          $flag = 1;
        }
        dbquery("CALL  `RH`.`set_avatar_empleados`('$flag', '$id','$path', '$file_name');");
          return array('access'=> true, 'ls'=>$path);
      }
      return array('access'=> false, 'execute'=>'reload',"msg"=>"don't upload file");
    }
    return array('access'=> false, 'execute'=>'reload',"msg"=>$error[$param['FILE_']['image']['error']]);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function generate_pdf_expediente($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $tipo = $param['tipo'];
    $id = $param['argument'];
    $q_emp = "select * from `sso`.`view_data_empleado` where id = '$id';";
    $result = dbquery($q_emp);
    $array = mysqli_fetch_assoc($result);
    $date=date_create($array['fingreso']);
    $array['fingreso'] = date_format($date,"d/m/Y");
    $date=date_create($array['fnacimiento']);
    $array['fnacimiento'] = date_format($date,"d/m/Y");
    $check;
    for($a=0;$a<strlen($array['documentos_personales']);$a++){
      if($array['documentos_personales'][$a]=='1'){
        $check[]='x';
      } else {
        $check[]='';
      }
    }
    for($a=0;$a<strlen($array['documentos_internos']);$a++){
      if($array['documentos_internos'][$a]=='1'){
        $check[]='x';
      } else {
        $check[]='';
      }
    }
    # Contenido HTML del documento que queremos generar en PDF.
    $html = ($tipo==1) ?pdftemplate("./dom-template/expediente-template.html",$array):pdftemplate("./dom-template/checklist-template.html",$check);
    pdfrender($html,'cosos',false,true);
    exit;
  }

}
// Catalogo


function get_catalogos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qget = "select * from `RH`.`view_catalogos`;";
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_catalogo($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['id'];
    $qget = "call `RH`.`get_catalogo_view`('$id',@view);";
    $flag = "select @view;";
    $array = array();
    dbquery_call($qget,$flag,$array);
    $qget = "select * from ".$array['@view'];
    $result = dbquery($qget);
    $array=array();
    if ($result->num_rows>0) {
      while($row = mysqli_fetch_assoc($result)){
        if(json_encode(array($row['descripcion']))==''){
          $row['descripcion'] = utf8_encode($row['descripcion']);
        }
        $array[]=$row;
      }

      return array("access"=>true,"ls"=>$array);
    }

    return array("access"=>true,"msg"=>"Sin Elementos");
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_catalogo($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['id_catalogo'];
    $descripcion = utf8_decode(strtoupper($param['descripcion']));
    $id_ = $param['id'];
    $qget = "call `RH`.`get_catalogo_view`('$id',@view);";
    $flag = "select @view;";
    $array = array();
    dbquery_call($qget,$flag,$array);
    $exp = explode('.',$array['@view']);
    $qget = "call ".implode(".set_",$exp)."('$id_','$descripcion');";
    dbquery($qget);
    $array=array();
    return array("access"=>true);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

// Home

function get_festejos($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qCumple = "select * from sso.view_fnacimiento where month(fnacimiento)=month(now()) order by dia asc;";
    $result = dbquery($qCumple);
    $array = array();
    while($row = mysqli_fetch_assoc($result)){
      $array[]=$row;
    }
    $ls['cumpleaños']=$array;
    $qCumple = "select * from sso.view_fingreso where month(fingreso)=month(now()) order by dia asc;";
    $result = dbquery($qCumple);
    $array = array();
    while($row = mysqli_fetch_assoc($result)){
      $array[]=$row;
    }
    $ls['aniversario']=$array;
    $qCumple = "select *from RH.view_cumpleanios_familiar where month(fnacimiento)=month(now()) order by day(fnacimiento) asc;";
    $result = dbquery($qCumple);
    $array = array();
    while($row = mysqli_fetch_assoc($result)){
      $array[]=$row;
    }
    $ls['parientes']=$array;
    return array('access'=> true, 'ls'=>$ls);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

//Reportes

function get_reportes($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $qReportes = "select * from RH.view_reportes_excel";
    $result = dbquery($qReportes);
    $ls = array();
    while($row = mysqli_fetch_assoc($result)){
      $ls[] = $row;
    }
    return array('access'=> true, 'ls'=>$ls);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function get_reporte($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id = $param['id'];
    $qReporte = "call `RH`.`get_view_reportes`('$id',@view);";
    $array = array();
    dbquery_call($qReporte,"select @view",$array);
    $qReporte = "select * from ".$array['@view'];
    $result = dbquery($qReporte);
    $assoc = array();
    while($row = mysqli_fetch_assoc($result)){
      foreach($row as $key => $value){
        $value = htmlentities($value);
        if(json_encode(array($value))==''){
          $row[$key] = utf8_decode($value);
        }
      }
      $assoc[] = $row;
    }
    //print_r($assoc);
    $export_file = excel_inflar($assoc);
    return array('access'=> true, "execute"=>'download','argument'=> $export_file, "verb"=>'dwl_excel');
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function dwl_excel($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $export_file = $param['argument'];
    excel_download($export_file);
    return array('access'=> true);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

// Solicitud de Vacaciones

function get_info_vacaciones($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $q_emp = "select id from sso.view_token_access where accessToken = '$token';";
    $result = dbquery($q_emp);
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    $q_emp = "select *  from `sso`.`view_data_empleado` where id = '$id';";
    $result = dbquery($q_emp);
    $array = mysqli_fetch_assoc($result);
    foreach($array as $key => $value){
      if(json_encode(array($value))==''){
        $array[$key] = utf8_encode($value);
      }
    }
    ksort($array);
    $a_fecha = explode('-', $array['fingreso']);
    $años = date('Y');
    $fecha = strtotime($a_fecha[2]."-".$a_fecha[1]."-".$años);
    $now = strtotime(date("d-m-Y",time()));

    $dteStart = new DateTime($array['fingreso']);
    $dteEnd   = new DateTime('now');

    $dteDiff  = $dteStart->diff($dteEnd);

    $años_ = $dteDiff->format("%y");

    if($now < $fecha){
      $años --;
    }
    $result = dbquery("select * from `sso`.`view_empleados` order by nombre;");
    $empleados;
    while($row = mysqli_fetch_assoc($result)){
      $empleados[] = $row;
    }
    $result = dbquery("select * from RH.view_diasferiados;");
    $dias_feriados;
    while($row = mysqli_fetch_assoc($result)){
      $dias_feriados[] = $row['descripcion'];
    }

    $array_fecha = explode('-', $array['fnacimiento']);
    $dias_feriados[] = date('Y')."-".$array_fecha[1]."-".$array_fecha[2];
    $dias_feriados[] = (date('Y')+1)."-".$array_fecha[1]."-".$array_fecha[2];

    $ls = array(
      'periodos' => array(
          $a_fecha[2].'-'.$a_fecha[1].'-'.($años-1)." al ".($a_fecha[2]-1).'-'.$a_fecha[1].'-'.($años),
          $a_fecha[2].'-'.$a_fecha[1].'-'.$años." al ".($a_fecha[2]-1).'-'.$a_fecha[1].'-'.($años+1),
          $a_fecha[2].'-'.$a_fecha[1].'-'.($años+1)." al ".($a_fecha[2]-1).'-'.$a_fecha[1].'-'.($años+2),
        ),
      'periodos_name' => array(
        'Extemporane',
        '',
        'Adelantado'
      ),
      'años'=>$años_,
      'dias'=> array(
        dias_que_corresponde($años_-1),
        dias_que_corresponde($años_),
        dias_que_corresponde($años_+1)
      ),
      'empleados' => $empleados,
      'diasFeriados' => $dias_feriados,
      'id_emp' => $id
    );

    return array('access'=> true,'ls'=>$ls,'array'=>$array);
  }
  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function set_solicitud($param){
  $token = $param['accessToken'];
  if (valid_token($token)){
    $id_emp = $param['id_emp'];
    $periodo = str_replace(' al ','/',$param['periodos'][$param['periodo']]);
    $dias_solicitados = $param['diasSolicitados'];
    $dias_disfrutados = $param['diasDisfrutados'];
    $dias_disponibles = $param['diasRestante'];
    $fecha_del = $param['rango'][0];
    $fecha_al = $param['rango'][1];
    $id_bck1 = $param['back1'];
    $id_bck2 = $param['back2'];
    $id_bck3 = $param['back3'];
    $superior = $param['superior'];
    $observaciones = $param['observaciones'];
    $folio = date("YmdHis").$id;
    $api ="http:".$param['api']."set_acepto_solicitud?argumento=$folio";

    //$q_solicitud ="call `RH`.`set_solicitud_vacaciones`('$folio', '$id_emp', '$periodo', '$dias_solicitados', '$dias_disponibles', '$dias_disfrutados', '$fecha_del', '$fecha_al', '$id_bck1', '$id_bck2', '$id_bck3', '$superior', '$observaciones')";

    $result = true;//dbquery($q_solicitud);
    $bck1_emp="";
    $bck2_emp="";
    $bck3_emp="";
    $super_emp="";
    $emp="";


    if($result) {
      foreach($param['empleados'] as $key=>$value) {
        if($value->id==$id_bck1){
          $bck1_emp = $value;
        }
        if($value->id==$id_bck2){
          $bck2_emp = $value;
        }
        if($value->id==$id_bck3){
          $bck3_emp = $value;
        }
        if($value->id==$superior){
          $super_emp = $value;
        }
        if($value->id==$id_emp){
          $emp = $value;
        }
      }
      if($bck1_emp!=""){
        $asunto = 'Solicitud de Back Up para vacaciones de '.$emp->nombre.' '.$emp->paterno;
        $body = utf8_encode("Por la presente se le hace saber, que el compañero $emp->nombre $emp->paterno tomara vacaciones del $fecha_del al $fecha_al, y solicita que usted le cubra en ese periodo. <a src=\"$api&id=$bck1_emp->id&tipo=bk1\">Acepto</a>");
        dbquery("call  `RH`.`set_email_out`('$bck1_emp->email','$asunto','$body')");
      }
      if($bck2_emp!=""){
        $asunto = 'Solicitud de Back Up para vacaciones de '.$emp->nombre.' '.$emp->paterno;
        $body = utf8_encode("Por la presente se le hace saber, que el compañero $emp->nombre $emp->paterno tomara vacaciones del $fecha_del al $fecha_al, y solicita que usted le cubra en ese periodo. <a src=\"$api&id=$bck2_emp->id&tipo=bk2\">Acepto</a>");
        dbquery("call  `RH`.`set_email_out`('$bck2_emp->email','$asunto','$body')");
      }
      if($bck3_emp!=""){
        $asunto = 'Solicitud de Back Up para vacaciones de '.$emp->nombre.' '.$emp->paterno;
        $body = utf8_encode("Por la presente se le hace saber, que el compañero $emp->nombre $emp->paterno tomara vacaciones del $fecha_del al $fecha_al, y solicita que usted le cubra en ese periodo. <a src=\"$api&id=$bck3_emp->id&tipo=bk3\">Acepto</a>");
        dbquery("call  `RH`.`set_email_out`('$bck3_emp->email','$asunto','$body')");
      }
      if($super_emp!=""){
        $asunto = 'Solicitud de Back Up para vacaciones de '.$emp->nombre.' '.$emp->paterno;
        $body = utf8_encode("Por la presente se le hace saber, que el compañero $emp->nombre $emp->paterno tomara vacaciones del $fecha_del al $fecha_al, y solicita su Autorizacion. <a src=\"$api&id=$super_emp->id&tipo=sup\">Acepto</a>");
        dbquery("call  `RH`.`set_email_out`('$super_emp->email','$asunto','$body')");
      }

    }

    return array('access'=>$result, 'folio'=>$folio);
  }

  return array('access'=> false, 'execute'=>'toSSO',"msg"=>"Token not found");
}

function generate_pdf_solicitud_vacaciones($param) {
  $token = $param['accessToken'];
  if (valid_token($token)){
    $folio = $param['argument'];
    $q_solicitud = "select * from `RH`.`view_vacaciones` where folio = '$folio';";
    $result = dbquery($q_solicitud);
    $array = mysqli_fetch_assoc($result);
    $vence = explode('/', $array['periodo']);
    $array['vence'] = $vence[1];
    $array['periodo'] = str_replace('/', ' al ',$array['periodo']);
    $mes  = array(
      '',
      'Enero',
      'Febrero',
      'Marzo',
      'Abril',
      'Mayo',
      'Junio',
      'Julio',
      'Agosto',
      'Septiembre',
      'Octubre',
      'Noviembre',
      'Diciembre'
    );
    $del = explode('-',$array['fecha_del']);
    $array['del_anio']=$del[0];
    $array['del_mes']=$mes[$del[1]*1];
    $array['del_dia']=$del[2];
    $al = explode('-',$array['fecha_al']);
    $array['al_anio']=$al[0];
    $array['al_mes']=$mes[$al[1]*1];
    $array['al_dia']=$al[2];

    # Contenido HTML del documento que queremos generar en PDF.
    $html = pdftemplate("./dom-template/vacaciones-template.html",$array);
    pdfrender($html,'cosos',false,true);
    exit;
  }
}



$_sys;
$_sys= new request();
set_header();
$_sys->param['apps'] = "recursoshumanos";//<---  deposita la app que usamos

echo json_encode(array("app"=>$_sys->app,"param"=>$_sys->param, "fun" =>fun_api($_sys)));




//ng build --aot --prod && xcopy /y dist .\..\RRHH /s/c && rd /S /Q dist && md .\..\RRHH\api &&  xcopy /y api .\..\Facturas\api /s
