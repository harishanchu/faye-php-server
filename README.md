#Faye PHP Library

This is a PHP library for interacting with the Faye server.
Faye is a publish-subscribe messaging system based on the Bayeux protocol.

##Installation

You can clone or download the library files.

##Faye constructor

Use the Faye server configurations to create a new Faye instance.

    $host = 'YOUR_FAYE_HOST';
    $mountPoint = 'YOUR_FAYE_MOUNT_POINT';
    $port = 'YOUR_FAYE_PORT'; //by default this will be 8000

    $faye = new Faye($host, $mountPoint, $port);

##Publishing/Triggering messges

To trigger a message on a channel use the trigger function.

    $faye->trigger( 'my-channel', 'my_event', 'hello world' );

###Arrays

Objects are automatically converted to JSON format:

    $array['status'] = 'success';
    $array['action'] = 'continue';

    $faye->trigger('my_channel', 'my_event', $array);

The output of this will be:

    "{'event': 'my_event', 'message': {'status': 'success', 'action': 'continue'}}"
    
##License

Copyright 2013, Harish A <ccoollh@gmail.com>. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
