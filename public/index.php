<?php
include_once __DIR__ . '/../vendor/autoload.php';

$requisicao = array(
	"messages"=>array(
			0=>array(
				"id"=>"false_558399711150@c.us_3EB02CA3A26371A62F72",
				"body"=>"Piauiense
Parnayba ML
1u
Min 1.66",
				"fromMe"=>0,
				"self"=>0,
				"isForwarded"=>0,
				"author"=>"558399711150@c.us",
				"time"=>1614138201,
				"chatId"=>"553195121104-1601482705@g.us",
				"messageNumber"=>51985,
				"type"=>"chat",
				"senderName"=>"MÃ£e",
				"caption"=>NULL,
				"quotedMsgBody"=>NULL,
				"quotedMsgId"=>NULL,
				"quotedMsgType"=>NULL,
				"chatName"=>"MÃ£e"
			)),
	"instanceId"=>"194066");

$funcaoTipster = array(
	"5522997157745-1566406220@g.us" => "funcaoRegys",
	"553195121104-1601482705@g.us" => "funcaoWR",
	"558182315715-1594862914@g.us" => "funcaoFagner",
);
//$requisicao["messages"][0]["body"] = file_get_contents('../vendor/aymanrb/php-unstructured-text-parser/examples/test_txt_files/m_0.txt');


function verificatipster($mensagem, $funcaoTipster){
		if(isset($funcaoTipster[$mensagem["messages"][0]["chatId"]])){
			$funcaoTipster[$mensagem["messages"][0]["chatId"]]($mensagem["messages"][0]["body"]);
		}
}

function funcaoRegys($mensagem){
	$parser = new aymanrb\UnstructuredTextParser\TextParser('../vendor/aymanrb/php-unstructured-text-parser/examples/templates');
	//Mudar para diretório referente ao GitHub!!!!
	$textToParse = preg_replace("/^[ \t]*[\r\n]+/m", "", strtolower($mensagem));
	$parseResults = $parser->parseText($textToParse, true)->getParsedRawData();
	if((array_key_exists("time", $parseResults) || array_key_exists("partida", $parseResults)) && strpos($textToParse, "aposta") === false){
		$mercado = defineMercado($textToParse, $parseResults);
		$linhaDB = procuraDB($parseResults, $mercado);
		$parseResults["oddmin"] = calculaOddmin($parseResults["odd"]);
		if(isset($linhaDB)){	
			echo construirAposta($linhaDB, $mercado, $parseResults["odd"], $parseResults["oddmin"]);
		}
	} else {
		$parseResults = $parser->parseText($textToParse)->getParsedRawData();
		if((array_key_exists("time", $parseResults) || array_key_exists("partida", $parseResults))  && strpos($textToParse, "aposta") === false){
			$mercado = defineMercado($textToParse, $parseResults);
			$linhaDB = procuraDB($parseResults, $mercado);
			$parseResults["oddmin"] = calculaOddmin($parseResults["odd"]);
			if(isset($linhaDB)){
				echo construirAposta($linhaDB, $mercado, $parseResults["odd"], $parseResults["oddmin"]);
			}
		}
	}
}

function funcaoWR($mensagem){
	$parser = new aymanrb\UnstructuredTextParser\TextParser('../vendor/aymanrb/php-unstructured-text-parser/examples/templatesWR');
	//Mudar para diretório referente ao GitHub!!!!
	$textToParse = preg_replace("/^[ \t]*[\r\n]+/m", "", strtolower($mensagem));
	$parseResults = $parser->parseText($textToParse, true)->getParsedRawData();
	if(array_key_exists("time", $parseResults) == false && array_key_exists("partida", $parseResults) == false){
		$parseResults = $parser->parseText($textToParse)->getParsedRawData();
	}
	$token = 'nijbp88m5fkl2w0r';
	$APIurl = 'https://eu27.chat-api.com/instance194066/';
	file_get_contents($APIurl."sendMessage?token=".$token."&chatId=558393389126@c.us&body=".urlencode($textToParse));
	print_r($textToParse);
	echo "<br><br>";
	print_r($parseResults);
	if((array_key_exists("time", $parseResults) || array_key_exists("partida", $parseResults)) && strpos($textToParse, "aposta") === false && strpos($textToParse, "live") === false){
		$mercado = defineMercado($textToParse, $parseResults);
		$linhaDB = procuraDB($parseResults, $mercado);
		if(array_key_exists("odd", $parseResults) == false || is_numeric($parseResults["odd"]) == false){
			$parseResults["odd"] = calculaOdd($linhaDB, $mercado);
		}
		$parseResults["oddmin"] = calculaOddmin($parseResults["odd"]);
		if(isset($linhaDB)){	
			echo construirAposta($linhaDB, $mercado, $parseResults["odd"], $parseResults["oddmin"]);
		}
	}
}

function funcaoFagner($mensagem){
	echo "Fagner";
}

function defineMercado($mensagem, $parsedtext){
	if(strpos($mensagem, "ml") !== false){
		$mercado = " - Vitória";
	} else if(strpos($mensagem, "dnb") !== false){
		$mercado = " - Empate Anula Aposta";
	} else if(strpos($mensagem, "dc ht") !== false){
		$mercado = " ou Empate - 1º Tempo";
	} else if(strpos($mensagem, "dc") !== false){
		$mercado = " ou Empate";
	} else if(strpos($mensagem, "htft") !== false || strpos($mensagem, "ht/ft") !== false){
		$mercado = " - Intervalo/Final do Jogo";
	} else if(strpos($mensagem, "over") !== false && strpos($mensagem, "ht") !== false){
		$mercado = str_replace(array("ht", "gols", "gol"),array("","",""),"Mais de ".$parsedtext["gols"])." gol(s) no 1º Tempo";
	} else if(strpos($mensagem, "over") !== false){
		$mercado = str_replace(array("gols", "gol"),array("",""),"Mais de ".$parsedtext["gols"])." gol(s) na Partida";
	} else if(strpos($mensagem, "under") !== false && strpos($mensagem, "ht") !== false){
		$mercado = str_replace(array("ht", "gols", "gol"),array("","",""),"Menos de ".$parsedtext["gols"])." gol(s) no 1º Tempo";
	} else if(strpos($mensagem, "under") !== false){
		$mercado = str_replace(array("gols", "gol"),array("",""),"Menos de ".$parsedtext["gols"])." gol(s) na Partida";
	} else if((strpos($mensagem, "ah") !== false && strpos($mensagem, "ht") !== false) || array_key_exists("linhapositiva", $parsedtext) || array_key_exists("linhanegativa", $parsedtext)){
		if(array_key_exists("linhapositiva", $parsedtext)){
			$mercado = " - Handcap Asiático +".$parsedtext["linhapositiva"]." no 1º Tempo";
		}else if(array_key_exists("linhanegativa", $parsedtext)){
			$mercado = " - Handcap Asiático -".$parsedtext["linhanegativa"]." no 1º Tempo";
		} else {
			$mercado = " - Handcap Asiático ".$parsedtext["linha"]." no 1º Tempo";
		}
	} else if(strpos($mensagem, "ah") !== false || array_key_exists("linhapositiva", $parsedtext) || array_key_exists("linhanegativa", $parsedtext)){
		if(array_key_exists("linhapositiva", $parsedtext)){
			$mercado = " - Handcap Asiático +".$parsedtext["linhapositiva"];
		} else if(array_key_exists("linhanegativa", $parsedtext)){
			$mercado = " - Handcap Asiático -".$parsedtext["linhanegativa"];
		} else {
			$mercado = " - Handcap Asiático ".$parsedtext["linha"];
		}
	}
	return $mercado;
}

function procuraDB($aposta, $mercado){
	$db_handle = pg_connect("host=ec2-54-164-241-193.compute-1.amazonaws.com dbname=detfg6vttnaua8 port=5432 user=kgsgrroozfzpnv password=a2ec0dd00478fd02c6395df74d3e82adc94632e51ea2c1cca2ba94f988e591f5");
	$query = "SELECT * FROM tabelateste";
	$resultado = pg_query($db_handle, $query);
	$min = 15;
	while ($row = pg_fetch_assoc($resultado)){
		if(array_key_exists("time", $aposta)){
			if(levenshtein($aposta["time"], strtolower($row["time1"]), 1, 3, 3)<$min){
				$arrayDB = $row;
				array_push($arrayDB, "time1");
				$min = levenshtein($aposta["time"], strtolower($row["time1"]), 1, 3, 3);
			} if(levenshtein($aposta["time"], strtolower($row["time2"]), 1, 3, 3)<$min){
				$arrayDB = $row;
				array_push($arrayDB, "time2");
				$min = levenshtein($aposta["time"], strtolower($row["time2"]), 1, 3, 3);
			}
		} else if(array_key_exists("partida", $aposta)){
			if(levenshtein($aposta["partida"], strtolower($row["time1"]."  ".$row["time2"]), 1, 3, 3)<$min){
				$arrayDB = $row;
				array_push($arrayDB, "partida");
				$min = levenshtein($aposta["partida"], strtolower($row["time1"]."  ".$row["time2"]), 1, 3, 3);
			}
		}
	}
	if(strpos($mercado, "Mais") !== false || strpos($mercado, "Menos") !== false){
		$mercadoBet = "betgols";
	} else {
		$mercadoBet = "betresultado";
	}
	if(isset($arrayDB) && ($arrayDB["hora"]<time()-36000 || $arrayDB[$mercadoBet] !== null)){ //Mudar diferença da hora!!!
		$arrayDB = [];
	}
	if(isset($arrayDB)){
		$arrayDB["partida"] = "";
		return $arrayDB;
	}
}

function calculaOdd($arrayAposta, $mercado){
	if($mercado == " - Vitória"){
		$odd = $arrayAposta["odd".$arrayAposta[0]];
	} else if($mercado == " - Empate Anula Aposta"){
		$odd = ($arrayAposta["odd".$arrayAposta[0]]-($arrayAposta["odd".$arrayAposta[0]]/$arrayAposta["oddempate"]))/0.9375;
	} else {
		$odd = rand(170,190)/100;
	}
	return $odd;
}

function calculaOddmin($apostaestruturada){
	$oddmin = $apostaestruturada*0.8+0.2;
	if($oddmin < 1.56 && $apostaestruturada > 1.56){
		$oddmin = 1.56;
	}
	return $oddmin;
}

function construirAposta($arrayDB, $mercado, $odd, $oddmin){
	$mensagem = "⚠️ ".$arrayDB[$arrayDB[0]].$mercado."
	💰 1 unidade
	⚽️ @".number_format($odd, 2)."
	⚽ Mínimo @".number_format($oddmin, 2)."
	🏟️ ".$arrayDB["time1"]." x ".$arrayDB["time2"]."
	🏟️ ".$arrayDB["campeonato"]."
	".$arrayDB["link"];
	return $mensagem;
}


verificatipster($requisicao, $funcaoTipster);
//print_r($funcaoTipster[$textToParse["messages"][0]["chatId"]]);
?>
