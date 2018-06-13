<?php
$str= "select distinct(app),name from `sso`.`view_sso_profile` WHERE email='".$_SESSION['nuser']."' order by app asc";
$result=mysql_query($str);
while ($row = mysql_fetch_array($result)) { ?>
<li>
    <a href="javascript:void(0);" onclick="abrirCerrar('<?php echo $row['app']; ?>li')"><?php echo $row['name']; ?></a>
    <ul class="nav" id="<?php echo $row['app']; ?>li" style="display: none;">
        <?php
        $substr="select app,url from `sso`.`view_sso_profile` WHERE email='".$_SESSION['nuser']."' and app='".$row['app']."'";
        $subresult=  mysql_query($substr)or die(mysql_er);
        while ($row1 = mysql_fetch_array($subresult)) {
                $leng=strlen($row1['url']);
                if($leng>1&&$row1['url'][$leng-1]=='*'){
                   $row1['url']=substr($row1['url'],0,$leng-1);
                }?>
            <li><a href="<?php echo $rhome.$row1['app'].$row1['url'];?>"><?php echo $row1['url']; ?></a></li>
        <?php }
        ?>
    </ul>
</li>
<?php }

