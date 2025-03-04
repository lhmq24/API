<?php
    $server_ip = getHostByName(getHostName()); 
    echo json_encode(["server_ip" => $server_ip]);
?>