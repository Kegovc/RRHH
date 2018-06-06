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
      header('Content-Type: text/html; charset=UTF-8');
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

function fun_api($api){

        if (function_exists($api->app)){
               return call_user_func($api->app,$api->param);
        }
}

function get_profile($param){
  $app = $param['apps'];
  $token = $param['accessToken'];
  $q_profile="select  distinct(profile) as profi,permissions,nombre from `sso`.`view_profile_token` WHERE  app = '$app' and accessToken = '$token';";
  $result = dbquery($q_profile);
  if($result->num_rows>0){
    $row = mysqli_fetch_assoc($result);
    $row['fecha']=date("d-M-Y");
    return array('access'=>true,"ls"=>$row);
  }
  return array('access'=> false, 'execute'=>'toSSO');
}

function attack_set($param){
  $ip = get_ip();
  $atk="Call sso.sso_attack_add('USUARIO NO AUTENTIFICADO','$ip','".ObtenerNavegador($_SERVER['HTTP_USER_AGENT'])."','SE INTENTO ACCEDER SIN INICIAR SECCION','')";
  dbquery($atk);
  return array("access"=>false,"execute"=>"toSSO","msn"=>"not_logged");
}

function guard_session($param){
  $apps = $param['apps'];
  $url = $param['url'];
  $token = $param['accessToken'];
      $qq="call sso.sso_isValidPermissionsByToken ('".$token."', '".$apps."', '".$url."',@p,@r,@m)";
      $rowqq=array();
      $error;
      dbquery_call($qq,"select @r,@m,@p",$rowqq,$error);
      //print_r($rowqq);
        if($rowqq['@r']==2){ // sin permisos
          $_SESSION['error_profile']=$rowqq['@m'];
          return array("access"=>false,"execute"=>"toSSO","msn"=>"not_permission");
        }
        if($rowqq['@r']==100){ //time_out
              $_SESSION['error_profile']=$rowqq['@m'];
              return array("access"=>false,"execute"=>"logoff","msn"=>"time_out");
        }
        return array("access"=>true);



}


$_sys;
$_sys= new request();
set_header();
$_sys->param['apps'] = "recursoshumanos";//<---  deposita la app que usamos
echo json_encode(array("_sys"=>$_sys, "fun" =>fun_api($_sys)));



//ng build --aot --prod && xcopy /y dist .\..\RRHH /s/c && rd /S /Q dist && md .\..\RRHH\api &&  xcopy /y api .\..\Facturas\api /s
