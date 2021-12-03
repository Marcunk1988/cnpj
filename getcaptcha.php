<?php

// define caminho absoluto e relativo para arquivo cookie
$pasta_cookies = 'cookies_cnpj/';
define('COOKIELOCAL', str_replace('\\', '/', realpath('./')).'/'.$pasta_cookies);
define('HTTPCOOKIELOCAL', 'http://'.$_SERVER['SERVER_NAME'].str_replace(pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_BASENAME),'',$_SERVER['SCRIPT_NAME']).$pasta_cookies);

// inicia sessão
@session_start();
$cookieFile_fopen = HTTPCOOKIELOCAL.session_id();        
$cookieFile = COOKIELOCAL.session_id();

// cria arquivo onde será salva a sessão com a receita, caso ele não exista
if(!file_exists($cookieFile))
{
	$file = fopen($cookieFile, 'w');
	fclose($file);
}

// faz a chamaa para a receita que exibe o captcha
$url = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// salva os dados de sessão
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// atualiza os dados de sessão se estiverem desatualizados
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
// não utilizar returntransfer , este script replica imagem captcha da receita sem necessidade de gravar a imagem
$imgsource = curl_exec($ch);
curl_close($ch);

// se tiver imagem , mostra
if(!empty($imgsource))
{
	$img = imagecreatefromstring($imgsource);
	header('Content-type: image/jpg');
	imagejpeg($img);
}


// Script abaixo simula que mostrou a página com formulário de entrada da receita (Cnpjreva_Solicitacao2.asp)
// pois do contrário a primeira consulta não retorna válida , possivelmente por conta de algo gravado na sessão da receita
// observe que estou preservando as variaveis de sessão $sessionName=$sessionId e enviando de volta em CURLOPT_COOKIE

// pega os dados de sessão gerados na visualização do captcha dentro do cookie
$file = fopen($cookieFile_fopen, 'r');
while (!feof($file))
{$conteudo .= fread($file, 1024);}
fclose ($file);

$explodir = explode(chr(9),$conteudo);
		
$sessionName = trim($explodir[count($explodir)-2]);
$sessionId = trim($explodir[count($explodir)-1]);	

// constroe o parâmetro de sessão que será passado no próximo curl
$cookie = $sessionName.'='.$sessionId;

$ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// dados do arquivo de cookie
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
curl_setopt($ch, CURLOPT_COOKIE, $cookie);	    // dados de sessão e flag=1
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);

?>