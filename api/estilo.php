<?php





if(!isset($_GET['url']))
	{
        error_reporting(E_ALL);
    }
else
{
	$v = new request();
}
if(isset($_SESSION['nuser'])){

    $query="select distinct(profile) as profi,permissions from `sso`.`view_sso_profile` WHERE email='".$_SESSION['nuser']."'";
    $result=  mysql_query($query);
    while ($sta = mysql_fetch_array($result)) {
        $profile=$sta['profi'];
        $tipoprofil=$sta['permissions'];
    }
}
?>

                      <?php
                        $query="select distinct(profile),permissions from `sso`.`view_sso_profile` WHERE email='".$_SESSION['nuser']."'";
                        $result=  mysql_query($query);
                        while ($sta = mysql_fetch_array($result)) {
                            $profile=$sta[0];
                            $tipoprofil=$sta[1];
                        }
                        $query= mysql_query("SELECT nombre FROM sso.sso_usr where email='".$_SESSION['nuser']."'");
                        while ($row = mysql_fetch_array($query)) {
                            $nname=$row[0];
                        }
                      ?>

                      <ul>
                      <h5>Hola: <?php echo $nname; ?></h5>
                      <p>
                      <h5>Fecha:  <?php echo date("d-M-Y"); ?></h5>
                      <p>
                      <h5>STA:  <?php echo $profile;?></h5>
                      <p>
                          <a href="<?php echo $sso;?>close" style="color: black;"><span class="label label-danger">Cerrar Sesión</span></a>
                      </ul>

 <?php
                include $hview."btp_quick_nav.php";
               ?>
