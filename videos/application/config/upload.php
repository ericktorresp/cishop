<?php
$config['upload_path'] = './uploads/';
$config['allowed_types'] = 'gif|jpg|png';
$config['max_size'] = '1000';
$config['max_width']  = '1024';
$config['max_height']  = '768';
$config['max_filename'] = 16;
$config['overwrite'] = FALSE;
$config['file_name'] = random_string('alnum',12).'.'.end(explode('.',$_FILES['userfile']['name']));