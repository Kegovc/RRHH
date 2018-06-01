<?php
function dbconnection(){
    $user = 'root';
    $password = 'root';
    $db = 'sso';
    $host = 'localhost';
    $port = 3306;

    $link = mysqli_init();
    if(!$link){
        die("mysqli_init failed");
    }
    else{
        $success = mysqli_real_connect(
           $link,
           $host,
           $user,
           $password,
           $db,
           $port
        );
    if(!$success)
        die("Connect Error: " . mysqli_connect_error());
    }
    return $link;
}

function dbquery($query,&$error=""){
    $link=dbconnection();
    $result= mysqli_query($link, $query)or die($error= mysqli_error($link));
    mysqli_close($link);
    return $result;
}

function dbquery_call($query,$flags,&$array,&$error=""){
    $error="";
    $link=dbconnection();
    $result= mysqli_query($link, $query)or die($error= mysqli_error($link));
    if($error==""){
        $resultcall= mysqli_query($link, $flags)or die($error= mysqli_error($link));
        while ($row = mysqli_fetch_assoc($resultcall)){
            $array=$row;
        }
    }
    mysqli_close($link);
    return $result;
}

