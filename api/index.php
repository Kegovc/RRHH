<?php
session_start();

require 'db_controller.php';
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
         $row['descripcion'] = utf8_encode($row['descripcion']);
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
         $row['descripcion'] = utf8_encode($row['descripcion']);
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
    $cia =strtoupper( $param['cia']);
    $nivel =strtoupper( $param['nivel']);
    $numero_emp =strtoupper( $param['numero_emp']);
    $status_rh =strtoupper( $param['status_rh']);
    $pagadora =strtoupper( $param['pagadora']);
    $razon_social =strtoupper( $param['razon_social']);
    $nombre =strtoupper( $param['nombre']);
    $segundo_nombre =strtoupper( $param['segundo_nombre']);
    $paterno =strtoupper( $param['paterno']);
    $materno =strtoupper( $param['materno']);
    $fingreso =strtoupper( $param['fingreso']);
    $puesto =strtoupper( $param['puesto']);
    $division =strtoupper( $param['division']);
    $departamento =strtoupper( $param['departamento']);
    $lugar_prestacion =strtoupper( $param['lugar_prestacion']);
    $horario =strtoupper( $param['horario']);
    $genero =strtoupper( $param['genero']);
    $fnacimiento =strtoupper( $param['fnacimiento']);
    $nacionalidad =strtoupper( $param['nacionalidad']);
    $estado_nacimiento =strtoupper( $param['estado_nacimiento']);
    $ciudad_nacimiento =strtoupper( $param['ciudad_nacimiento']);
    $numero_ss =strtoupper( $param['numero_ss']);
    $numero_infonavit =strtoupper( $param['numero_infonavit']);
    $rfc =strtoupper( $param['rfc']);
    $curp =strtoupper( $param['curp']);
    $tsangre =strtoupper( $param['tsangre']);
    $nivel_estudios =strtoupper( $param['nivel_estudios']);
    $carrera =strtoupper( $param['carrera']);
    $titulo =strtoupper( $param['titulo']);
    $direccion =strtoupper( $param['direccion']);
    $cruces =strtoupper( $param['cruces']);
    $colonia =strtoupper( $param['colonia']);
    $estado =strtoupper( $param['estado']);
    $municipio =strtoupper( $param['municipio']);
    $cp =strtoupper( $param['cp']);
    $personal_email =strtoupper( $param['personal_email']);
    $tcasa =strtoupper( $param['tcasa']);
    $cell =strtoupper( $param['cell']);
    $estado_civil =strtoupper( $param['estado_civil']);
    $emergencias_nombre =strtoupper( $param['emergencias_nombre']);
    $emergencias_parentesco =strtoupper( $param['emergencias_parentesco']);
    $emergencias_cel =strtoupper( $param['emergencias_cel']);
    $emergencias_oficina =strtoupper( $param['emergencias_oficina']);
    $emergencias_casa =strtoupper( $param['emergencias_casa']);
    $banco =strtoupper( $param['banco']);
    $clabe =strtoupper( $param['clabe']);
    $dia_pago =strtoupper( $param['dia_pago']);
    $casa_propia =strtoupper( $param['casa_propia']);
    $medio_transporte =strtoupper( $param['medio_transporte']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
        $row['descripcion'] = utf8_encode($row['descripcion']);
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
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
    $dm_descripcion=strtoupper($param['dm_descripcion']);
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
    $materno = $param['materno'];
    $nombre = $param['nombre'];
    $paterno = $param['paterno'];
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
    ksort($param);
    print_r($param);
    return array('access'=> true, "ls" => "");
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
       $row['descripcion'] = utf8_encode($row['descripcion']);
        $array[]=$row;
      }
      return array("access"=>true,"ls"=>$array);
    }
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

$_sys;
$_sys= new request();
set_header();
$_sys->param['apps'] = "recursoshumanos";//<---  deposita la app que usamos
echo json_encode(array("app"=>$_sys->app,"param"=>$_sys->param, "fun" =>fun_api($_sys)));




//ng build --aot --prod && xcopy /y dist .\..\RRHH /s/c && rd /S /Q dist && md .\..\RRHH\api &&  xcopy /y api .\..\Facturas\api /s
