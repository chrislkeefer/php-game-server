<?php
// IP and port of the UDP server
$ip = '127.0.0.1';
$port = 12345;

// Create a UDP socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

if (!$socket) {
    die("Could not create socket\n");
}

// Message to send to the server
$message = '{"command":"connect"}';

// Send the message to the server
socket_sendto($socket, $message, strlen($message), 0, $ip, $port);

// Receive the response from the server
$buffer = '';
socket_recvfrom($socket, $buffer, 512, 0, $ip, $port);

// Print the response
echo "$buffer\n";

// Close the socket
socket_close($socket);