<?php
session_start();

require 'db_controller.php';

require 'xls_controller.php';
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

$_sys;
$_sys= new request();
set_header();
$_sys->param['apps'] = "recursoshumanos";//<---  deposita la app que usamos

echo json_encode(array("app"=>$_sys->app,"param"=>$_sys->param, "fun" =>fun_api($_sys)));




//ng build --aot --prod && xcopy /y dist .\..\RRHH /s/c && rd /S /Q dist && md .\..\RRHH\api &&  xcopy /y api .\..\Facturas\api /s
