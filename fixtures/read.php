<?php
declare(strict_types = 1);

@unlink('/tmp/read.sock');
$socket = stream_socket_server('unix:///tmp/read.sock');

while (true) {
    $client = stream_socket_accept($socket);

    while(!feof($client)) {
        fwrite($client, 'foo');
    }
}
