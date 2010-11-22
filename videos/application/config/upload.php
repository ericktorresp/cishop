<?php
$config['upload_path'] = './uploads/videos/';
$config['allowed_types'] = 'zip';
$config['max_size'] = '5000';
//$config['max_width']  = '1024';
//$config['max_height']  = '768';
$config['max_filename'] = 16;
$config['overwrite'] = TRUE;
$config['file_name'] = random_string('alnum',12).'.'.end(explode('.',$_FILES['userfile']['name']));