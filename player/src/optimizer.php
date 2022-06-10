<?php
// html 출력 최적화 스크립트
// Original Code From: https://stackoverflow.com/a/48123642

$html = ob_get_contents();
ob_end_clean();

$search = array(
'/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/m',
'/(\n|^)(\x20+|\t)/',
'/(\n|^)\/\/(.*?)(\n|$)/',
'/\n\n/',
'/\<\!--.*?-->/',
'/(\x20+|\t)/', # Delete multispace (Without \n)
'/\>\s+\</', # strip whitespaces between tags
'/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
'/=\s+(\"|\')/'); # strip whitespaces between = "'

$replace = array(
"$1",
"\n",
"\n",
" ",
"",
" ",
"><",
"$1>",
"=$1");

echo preg_replace($search,$replace,$html);