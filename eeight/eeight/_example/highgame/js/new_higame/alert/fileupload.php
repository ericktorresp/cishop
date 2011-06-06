<?php
header("Content-type: text/html; charset=utf-8");
echo file_get_contents($_FILES['james_uploadUI_file']['tmp_name']);
?>