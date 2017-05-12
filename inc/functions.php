<?php 

include 'inc/functions_core.php';

/*
* Converte Alephseq em JSON *
*/
function processaAlephseq($line) {

	global $marc;
	global $i;
	global $id;
	 
	$id = substr($line, 0, 9);
	$field = substr($line, 10, 3);
	//$ind_1 = substr($line, 13, 1);
	//$ind_2 = substr($line, 14, 1);	

	
	$control_fields = array("LDR","FMT","001","008");
	$repetitive_fields = array("100","650","651","655","700","856","946","952","CAT");
	
	if (in_array($field,$control_fields)) {
		$marc["record"][$field]["content"] = trim(substr($line, 18));			
		
	} elseif (in_array($field,$repetitive_fields)) {				
		$content = explode("\$", substr($line, 18));					
		foreach ($content as &$content_line) {
			if (!empty($content_line)) {
				$marc["record"][$field][$i][substr($content_line, 0, 1)] = trim(substr($content_line, 1));
			}
						
		
		}
		
	
	} else {	
		$content = explode("\$", substr($line, 18));	
		foreach ($content as &$content_line) {
			if (!empty($content_line)) {
				$marc["record"][$field][substr($content_line, 0, 1)][] = trim(substr($content_line, 1));
			}			
		
		}
			
	}
	
	//$marc["record"][$field]["ind_1"] = $ind_1;
	//$marc["record"][$field]["ind_2"] = $ind_2;
	
	$i++;

}

/*
* Processa o fixes *
*/
function fixes($marc) {
	
	global $i;

	//print_r($marc);
	$body = [];
		
	if (isset($marc["record"]["020"])) {
		$body["doc"]["isbn"] = $marc["record"]["020"]["a"][0]; 
	}
	
	if (isset($marc["record"]["024"])) {
		$body["doc"]["doi"] = $marc["record"]["024"]["a"][0];
	}
	
	if (isset($marc["record"]["041"])) {
		$language_correct = decode::language($marc["record"]["041"]["a"][0]);
		$body["doc"]["language"][] = $language_correct;
	}
	
	if (isset($marc["record"]["044"])) {
		$country_correct = decode::country($marc["record"]["044"]["a"][0]);
		$body["doc"]["country"][] = $country_correct;
	}				
	
	if (isset($marc["record"]["100"])) {
	
		foreach (($marc["record"]["100"]) as $person) { 
			$author["person"]["name"] = $person["a"];
			if (!empty($person["4"])) {
				$potentialAction_correct = decode::potentialAction($person["4"]);
				$author["person"]["potentialAction"] = $potentialAction_correct;				
			}
			if (!empty($person["d"])) {
				$author["person"]["date"] = $person["d"];
			}						
			if (!empty($person["8"])) {
				$author["person"]["affiliation"]["name"] = $person["8"];
			}
			if (!empty($person["9"])) {	
				$author["person"]["affiliation"]["location"] = $person["9"];
			}
			if (!empty($potentialAction_correct)) {
				$author["person"]["USP"]["autor_funcao"] = $person["a"] . " / " . $potentialAction_correct;
			}



		}
		
		$body["doc"]["author"][] = $author;
		unset($person);
		unset($author); 
	}
	
	if (isset($marc["record"]["245"])) {
		$body["doc"]["name"] = $marc["record"]["245"]["a"][0]; 
	}

	if (isset($marc["record"]["246"])) {
		$body["doc"]["alternateName"] = $marc["record"]["246"]["a"][0]; 
	}	

	
	
	if (isset($marc["record"]["260"])) {
		if (isset($marc["record"]["260"]["b"])){
			$body["doc"]["publisher"]["organization"]["name"] = $marc["record"]["260"]["b"][0];
		}
		if (isset($marc["record"]["260"]["a"])){	
			$body["doc"]["publisher"]["organization"]["location"] = $marc["record"]["260"]["a"][0];
		}	 
	}

	if (isset($marc["record"]["382"]["a"])) {
		foreach (($marc["record"]["382"]["a"]) as $meio_de_expressao) {
			$body["doc"]["USP"]["meio_de_expressao"][] = $meio_de_expressao;
		} 
	}

	if (isset($marc["record"]["500"]["a"])) {
		foreach (($marc["record"]["500"]["a"]) as $notas) {
			$body["doc"]["USP"]["notes"][] = $notas;
		} 

	}			
	

	if (isset($marc["record"]["502"])) {
		$body["doc"]["inSupportOf"] = $marc["record"]["502"]["a"][0]; 
	}
	
	if (isset($marc["record"]["520"])) {
		foreach (($marc["record"]["520"]["a"]) as $description) {
			$body["doc"]["description"][] = $description;
		} 
	}	
	
	if (isset($marc["record"]["536"])) {
		foreach (($marc["record"]["536"]) as $funder) {
			$body["doc"]["funder"][] = $funder["a"];
		} 
	}	
	
	if (isset($marc["record"]["590"])) {
		if (!empty($marc["record"]["590"]["d"])){
			$body["doc"]["USP"]["areaconcentracao"] = $marc["record"]["590"]["d"][0];
		}
		if (!empty($marc["record"]["590"]["m"])){
			$body["doc"]["USP"]["fatorimpacto"] = $marc["record"]["590"]["m"][0];
		}
		if (!empty($marc["record"]["590"]["n"])){
			$body["doc"]["USP"]["grupopesquisa"] = explode(";", $marc["record"]["590"]["n"][0]);
		}					

	}
	
	if (isset($marc["record"]["599"])) {
		$body["doc"]["USP"]["programa_pos_sigla"] = $marc["record"]["599"]["a"][0];
		$body["doc"]["USP"]["programa_pos_nome"] = $marc["record"]["599"]["b"][0]; 
	}	
	
	
	if (isset($marc["record"]["650"])) {
		foreach (($marc["record"]["650"]) as $subject) {
			$body["doc"]["about"][] = $subject["a"];
		}
	}

	if (isset($marc["record"]["651"])) {
		foreach (($marc["record"]["651"]) as $subject) {
			$body["doc"]["about"][] = $subject["a"];
		}
	}

	if (isset($marc["record"]["655"])) {
		foreach ($marc["record"]["655"] as $genero_e_forma) {
			$body["doc"]["USP"]["about"]["genero_e_forma"][] = $genero_e_forma["a"];
		}
	}		
	
	if (isset($marc["record"]["700"])) {
	
		foreach (($marc["record"]["700"]) as $person) { 
			$author["person"]["name"] = $person["a"];
			if (!empty($person["8"])) {			
				$author["person"]["affiliation"]["name"] = $person["8"];
			}
			if (!empty($person["9"])) {
				$author["person"]["affiliation"]["location"] = $person["9"];
			}
			if (!empty($person["4"])) {
				$potentialAction_correct = decode::potentialAction($person["4"]);
				$author["person"]["potentialAction"] = $potentialAction_correct;
			}
			if (!empty($potentialAction_correct)) {
				$author["person"]["USP"]["autor_funcao"] = $person["a"] . " / " . $potentialAction_correct;
			}							
			$body["doc"]["author"][] = $author;
			unset($person);
			unset($author);			
		} 
	}
	
	
	if (isset($marc["record"]["711"])) {
		$body["doc"]["releasedEvent"] = $marc["record"]["711"]["a"][0];
	}	

	if (isset($marc["record"]["773"])) {
		$body["doc"]["isPartOf"] = $marc["record"]["773"]["t"][0];
	}
	
	if (isset($marc["record"]["856"])) {
	
		foreach ($marc["record"]["856"] as $url) {
			if ($url["3"] == "Documento completo" | $url["3"] == "BDTD" | $url["3"] == "Servidor ECA" ) {
				$body["doc"]["url"][] = $url["u"];
			}					
		} 	


	}			
	
	if (isset($marc["record"]["945"])) {
		if (isset($marc["record"]["945"]["j"])){
			$body["doc"]["datePublished"] = $marc["record"]["945"]["j"][0];
		}		
		$body["doc"]["type"] = $marc["record"]["945"]["b"][0];
		
		if (isset($marc["record"]["945"]["l"])){
			$body["doc"]["USP"]["internacionalizacao"] = $marc["record"]["945"]["l"][0];
		}
		
		
		switch ($marc["record"]["945"]["b"][0]) {
		    case "MONOGRAFIA/LIVRO":
			$body["doc"]["numberOfPages"] = $marc["record"]["300"]["a"][0];
		    break;
		    case "TESE":
			$body["doc"]["dateCreated"] = $marc["record"]["945"]["i"][0];
		    break;		    
		}		
		
		

	}
	
	if (isset($marc["record"]["946"])) {
	
		foreach (($marc["record"]["946"]) as $authorUSP) {
			$authorUSP_array["name"] = $authorUSP["a"];
			$authorUSP_array["unidadeUSP"] = $authorUSP["e"];
			
			if (isset($authorUSP["g"])) {
				$authorUSP_array["departament"] = $authorUSP["g"];
			}	
			$body["doc"]["authorUSP"][] = $authorUSP_array;
			$body["doc"]["unidadeUSP"][] = $authorUSP["e"];	
		}

	}
	
	if (isset($marc["record"]["952"])) {
		foreach ($marc["record"]["952"] as $subject_BDTD) {			
			if (isset($subject_BDTD["f"])) {
				$body["doc"]["USP"]["about_BDTD"][] = $subject_BDTD["a"];
			}	
		}
	}
	
	if (isset($marc["record"]["CAT"])) {
		foreach ($marc["record"]["CAT"] as $CAT) {
			if (isset ($CAT["a"])){
				$CAT_array["cataloger"] = $CAT["a"];
			} else {
				$CAT_array["cataloger"] = "N/A";
			} 	
			$CAT_array["date"] = substr($CAT["c"], 0, -2);
			$body["doc"]["USP"]["CAT"][] = $CAT_array;
		}
		unset($CAT);
		unset($CAT_array); 	
		
	}		
	
	
	
	$body["doc_as_upsert"] = true;
	return $body;
	
}

class decode {

	/* Pegar o tipo de material */
	static function get_type($material_type){
		switch ($material_type) {
		    case "ARTIGO DE JORNAL":
			return "article-newspaper";
		    break;
		    case "ARTIGO DE PERIODICO":
			return "article-journal";
		    break;
		    case "PARTE DE MONOGRAFIA/LIVRO":
			return "chapter";
		    break;
		    case "APRESENTACAO SONORA/CENICA/ENTREVISTA":
			return "interview";
		    break;
		    case "TRABALHO DE EVENTO-RESUMO":
			return "paper-conference";
		    break;
		    case "TRABALHO DE EVENTO":
			return "paper-conference";
		    break;     
		    case "TESE":
			return "thesis";
		    break;          
		    case "TEXTO NA WEB":
			return "post-weblog";
		    break;
		}
	}
	
	/* Decodificar idioma */
	static function language($language){
		switch ($language) {
		    case "por":
				return "Português";
		    	break;
		    case "eng":
				return "Inglês";
		    	break;
		    case "spa":
				return "Espanhol";
		    	break;
		    case "fre":
				return "Francês";
		    	break;
		    case "mul":
				return "Multiplos idiomas";
		    	break;
		    case "ger":
				return "Alemão";
		    	break;
		    case "ita":
				return "Italiano";
		    	break;																						
		    default:
		    	return $language;		    		    
		}
	}
	
	/* Decodificar pais */
	static function country($country){
		switch ($country) {
		    case "ag":
				return "Argentina";
		    	break;
		    case "at":
				return "Austrália";
		    	break;
		    case "au":
				return "Áustria";
		    	break;
		    case "be":
				return "Bélgica";
		    	break;																	
		    case "bl":
				return "Brasil";
		    	break;
		    case "bo":
				return "Bolívia";
		    	break;
		    case "bu":
				return "Bulgária";
		    	break;
		    case "cau":
				return "Estados Unidos";
		    	break;
		    case "cc":
				return "China";
		    	break;
		    case "ch":
				return "China";
		    	break;
		    case "ci":
				return "Croácia";
		    	break;
		    case "ck":
				return "Colômbia";
		    	break;
		    case "cl":
				return "Chile";
		    	break;
		    case "cr":
				return "Costa Rica";
		    	break;
		    case "cu":
				return "Cuba";
		    	break;
		    case "dcu":
				return "Estados Unidos";
		    	break;
		    case "dk":
				return "Dinamarca";
		    	break;
		    case "dr":
				return "República Dominicana";
		    	break;
		    case "ec":
				return "Equador";
		    	break;
		    case "enk":
				return "Inglaterra";
		    	break;
		    case "et":
				return "Etiópia";
		    	break;
		    case "fi":
				return "Finlândia";
		    	break;
		    case "flu":
				return "Estados Unidos";
		    	break;
		    case "fr":
				return "França";
		    	break;
		    case "gb":
				return "República de Kiribati";
		    	break;
		    case "gr":
				return "Grécia";
		    	break;
		    case "gw":
				return "Alemanha";
		    	break;
		    case "hk":
				return "Hong-Kong";
		    	break;
		    case "hu":
				return "Hungria";
		    	break;
		    case "ie":
				return "Irlanda";
		    	break;
		    case "ii":
				return "Índia";
		    	break;
		    case "ilu":
				return "Estados Unidos";
		    	break;
		    case "ir":
				return "Irã";
		    	break;
		    case "is":
				return "Israel";
		    	break;
		    case "it":
				return "Itália";
		    	break;
		    case "ja":
				return "Japão";
		    	break;
		    case "ko":
				return "Coreia do Sul";
		    	break;
		    case "mau":
				return "Estados Unidos";
		    	break;
		    case "mdu":
				return "Estados Unidos";
		    	break;
		    case "mx":
				return "México";
		    	break;
		    case "ne":
				return "Holanda";
		    	break;
		    case "nl":
				return "Nova Caledonia";
		    	break;
		    case "no":
				return "Noruega";
		    	break;
		    case "nr":
				return "Nigéria";
		    	break;
		    case "nju":
				return "Estados Unidos";
		    	break;
		    case "nyu":
				return "Estados Unidos";
		    	break;
		    case "nz":
				return "Nova Zelândia";
		    	break;
		    case "pau":
				return "Estados Unidos";
		    	break;
		    case "pe":
				return "Peru";
		    	break;
		    case "pk":
				return "Paquistão";
		    	break;
		    case "pl":
				return "Polônia";
		    	break;
		    case "pr":
				return "Porto Rico";
		    	break;
		    case "po":
				return "Portugal";
		    	break;
		    case "py":
				return "Paraguai";
		    	break;	
		    case "riu":
				return "Estados Unidos";
		    	break;
		    case "rm":
				return "Romênia";
		    	break;
		    case "ru":
				return "Rússia";
		    	break;
		    case "sa":
				return "África do Sul";
		    	break;
		    case "si":
				return "Singapura";
		    	break;
		    case "sp":
				return "Espanha";
		    	break;
		    case "stk":
				return "Escócia";
		    	break;
		    case "sw":
				return "Suécia";
		    	break;
		    case "sz":
				return "Suiça";
		    	break;
		    case "th":
				return "Tailândia";
		    	break;
		    case "ts":
				return "Emirados Árabes Unidos";
		    	break;
		    case "tu":
				return "Turquia";
		    	break;
		    case "xr":
				return "República Checa";
		    	break;
		    case "xx":
				return "Desconhecido";
		    	break;
		    case "xxk":
				return "Reino Unido";
		    	break;																																																																																																																																																																																																																																																																																																				
		    case "xxu":
				return "Estados Unidos";
		    	break;
		    case "xxc":
				return "Canadá";
		    	break;
		    case "ua":
				return "Egito";
		    	break;
		    case "uy":
				return "Uruguai";
		    	break;
		    case "uk":
				return "Reino Unido";
		    	break;
		    case "yu":
				return "Iugoslávia";
		    	break;
		    case "vau":
				return "Estados Unidos";
		    	break;
		    case "ve":
				return "Venezuela";
		    	break;
		    case "xr":
				return "República Tcheca";
		    	break;
		    case "wau":
				return "Estados Unidos";
		    	break;																																											
		    default:
		    	return $country;		    		    
		}
	}

	/* Decodificar função */
	static function potentialAction($potentialAction){
		switch ($potentialAction) {
		    case "adapt":
				return "Adaptação";
		    	break;
		    case "arranjo mus":
				return "Arranjo musical / Arranjador musical";
		    	break;
		    case "comp":
				return "Compilador";
		    	break;
		    case "compos":
				return "Compositor musical";
		    	break;
		    case "coord pesq musico":
				return "Coordenador de pesquisa musicológica";
		    	break;																						
		    case "co-orient":
				return "Co-orientador";
		    	break;
		    case "ed":
				return "Editor";
		    	break;
		    case "elab":
				return "Elaborador";
		    	break;
		    case "pref":
				return "Prefácio";
		    	break;
		    case "rev":
				return "Revisor";
		    	break;
		    case "text":
				return "Autor texto";
		    	break;
		    case "trad":
				return "Tradução";
		    	break;
		    case "transc":
				return "Transcrição";
		    	break;																																				
		    case "orient":
				return "Orientador";
		    	break;
			
		    default:
		    	return $potentialAction;	    
		}
	}

}	 


?>