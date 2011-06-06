<?php
    $data = "";
    foreach( $_POST as $k=>$v ){
        $data .= $k."=>".(is_array($v) ? serialize($v) : $v)."\r\n\r\n";
    }
    sleep(5);
    file_put_contents('11.txt',$data);
?>