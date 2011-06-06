<?php
/**
 * 输出APPLE框架 头部 HTML 文件.
 *
 *
 */

$temp_html = <<<EOD
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>message</title>
<style type="text/css">
/* CSS Document */
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
}

h1 {
    font-size: 24px;
    font-weight: bold;
    color: #6699cc;
}

h2 {
    font-size: 14px;
    font-weight: bold;
    margin: 0px;
    padding: 0px;
    margin-bottom: 8px;
};

code, pre {
    color:#4444AA;
    font-size: 12px;
}

pre {
    margin-left: 12px;
    border-left: 1px solid #CCCCCC;
    padding: 6px 0px 6px 20px;
    line-height: 16px;
    background: #eeffee;
}

a {
    color: #3366CC;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.tip {
    background: #eeffee;
    padding: 10px;
    border: 1px solid #ccddcc;
}

.tip h2 {
    color: #006600;
}

.error {
    font-size: 18px;
    color:#ff2626;
    background: #ffeeee;
    padding: 8px;
    border: 1px solid #ddcccc;
}

.error h2 {
    color: #FF3300;
}

.track {
    font-family:Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    background-color: #FFFFCC;
    padding: 10px;
    border: 1px solid #FF9900;
}

.filedesc {
    margin-left: 16px;
    color: #666666;
}

.line-num {
    font-size: 12px;
    vertical-align: top;
}

.line-num-break {
    font-size: 12px;
    font-weight: bold;
    color: white;
    background-color: red;
    vertical-align: top;
}

.source {
    font-size: 12px;
    vertical-align: top;
}
</style>
<body>
EOD;
echo $temp_html;
unset($temp_html);
?>