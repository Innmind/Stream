<?php
declare(strict_types = 1);

@unlink('/tmp/oob.sock');
$socket = stream_socket_server('tcp://127.0.0.1:1234');

while (true) {
    $client = stream_socket_accept($socket);

    while(!feof($client)) {
        stream_socket_sendto($client, 'foo', STREAM_OOB);
    }
}
