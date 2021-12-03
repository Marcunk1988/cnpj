<?php

require('funcoes.php');

$cnpj = $_POST['CNPJ'];
$captcha = $_POST['CAPTCHA'];

// pega html resposta da receita
$getHtmlCNPJ = getHtmlCNPJ($cnpj, $captcha);

if($getHtmlCNPJ)
{
	// volova os dados em um array
	$campos = parseHtmlCNPJ($getHtmlCNPJ);
	print_r($campos);
}

?>