<?php
	
	if ($_REQUEST['hub_verify_token'] == "token") {
	echo $_REQUEST['hub_challenge'];
	}else{
		echo "nao autorizado";
	}
	
	require __DIR__ . '/vendor/autoload.php';
	// Configurations
	$access_token ="EAAXnGFuDWxQBAGsLO9nu9ZC7zR5UZCfASrF73aX1UslxjpRi7oJBdRXH3mPOirXDePVy4MpmJoZBggU0NQ39ZCBZCE6JHuEX7uVrOznvcm25ZAG8j3i65L4FdEaRiNaZBORtR4HLMLmXlZCIMrmoIhT7HtKMd9lPcbVOk06PWYpFoQZDZD";
	$app_id = '1661466684119828';
	$app_secret = '8c446bcf22b2a2ba6b6774fb23b27a6b';
	// Configurations - End
	
	use FacebookAds\Api;
	use FacebookAds\Object\Lead;
	use FacebookAds\Object\Fields\LeadFields;
	
	Api::init($app_id, $app_secret, $access_token);
	
	$body = file_get_contents('php://input');
	$input = json_decode($body);
	
	
		$form = new Lead(($input->entry[0]->changes[0]->value->leadgen_id));
		$formid = $input->entry[0]->changes[0]->value->form_id;
		$lead = $form->read();
		$leaddata = ($lead->{LeadFields::FIELD_DATA});
		$data = ($lead->getData());
		file_put_contents('income.txt',print_r($data,true));
		$data1 = $data[field_data][0][values][0];
		if(substr($data1,0,1) == "+"){
			//file_put_contents('erro.txt', "falhou");	
			exit;			
		}else{
					
				$retorno = FormatData($data,$formid);				
				file_put_contents('dados.txt', print_r($retorno,true));			
				$curl = curl_init();
				  // You can also set the URL you want to communicate with by doing this:
				  // We POST the data
				  curl_setopt($curl, CURLOPT_POST, 1);
				  // Set the url path we want to call					  
				  curl_setopt($curl, CURLOPT_URL, 'https://vwapps.volkswagen.com.br/integrator/home/facebook');  
				  //curl_setopt($curl, CURLOPT_URL, 'https://facelead.azurewebsites.net/Facebook-lead-Webhook/received.php');
				  //Define Header
				  //curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
				  // Make it so the data coming back is put into a string
				  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				  // Insert the data
				  curl_setopt($curl, CURLOPT_POSTFIELDS,$retorno);
				   
				  // You can also bunch the above commands into an array if you choose using: curl_setopt_array
				   curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
					    //'Content-Type: application/json',                                                                                
						'Content-Type: application/x-www-form-urlencoded',
					    'Content-Length: ' . strlen($retorno))                                                                       
					); 
				  // Send the request
				  $result = curl_exec($curl);
				  // Free up the resources $curl is using
				  curl_close($curl);		   
			  	  file_put_contents('Retorno.txt', print_r($result,true));			
				  file_put_contents('globals.txt',print_r($GLOBALS,true));  
		}
	
		
	  function FormatData($data,$modelo){	  	  
		  	  //Retorno do JSON
			  
			  //Contar a quantidade de campos no field data
			  $ctn = count($data[field_data]);
			  //enquando não varrer todos os campos..
			  $i = 0;			  
			  while($i<$ctn){
			  $nomev = $data[field_data][$i][name];
			  //valida qual o campos
			  switch($nomev){
				  case "cpf":
				              $cpf = $data[field_data][$i][values][0];
				  break;
				  case "conditional_question_1":
				              $uf  = $data[field_data][$i][values][0];	
				  break;
				  case "conditional_question_2":
				              $cidade  = $data[field_data][$i][values][0];	
				  break;
				  case "conditional_question_3":
				              $dealername  = $data[field_data][$i][values][0];	
				  break;
				  case "first_name":
				              $nome  = $data[field_data][$i][values][0];	
				  break;
				  case "last_name":
				              $sobrenome  = $data[field_data][$i][values][0];	
				  break;
				  case "email":
				              $email  = $data[field_data][$i][values][0];	
				  break;
				  case "phone_number":
				              $fonebase  = $data[field_data][$i][values][0];	
				  break;
			  }
				  $i++;
			  } 
	
			  //$cpf   		= $data[field_data][0][values][0];	 
			  //$email 	    = $data[field_data][6][values][0];
			  //$nome 		= $data[field_data][4][values][0];
			  //$sobrenome    = $data[field_data][5][values][0];
			  $NomeCompleto = $nome." ".$sobrenome;
			  //$fonebase 	= $data[field_data][7][values][0];
			  $ddd 			= substr($fonebase,3,2);
			  $telefone     = substr($fonebase,5,strlen($fonebase));
			  //$modelo       = $data[field_data][8][values][0];
			  //$dealername   = $data[field_data][3][values][0];
			  //$uf           = $data[field_data][1][values][0];
			  //$cidade       = $data[field_data][2][values][0];
			  		
		 	$response['dealers']     = array();
			$dealerdata              = array();
			$dadosPessoa 		     = array();   
			$dadosVeiculo 		     = array();   
			
					
			//Definir os dados internos do DadosPessoa		
			$dadosPessoa['nome']     	= $NomeCompleto;
			$dadosPessoa['email']       = $email;
			$dadosPessoa['ddd']         = $ddd;
			$dadosPessoa['telefone']    = $telefone;
			$dadosPessoa['cpfcnpj']     = $cpf;
			$dadosPessoa['UF']          = $uf;
			$dadosPessoa['cidade']      = $cidade;
			
			//Definir os dados do veiculo
			$dadosVeiculo['modelo']           = $modelo; //formid
			$dadosVeiculo['codigomodelo']     = "";
			$dadosVeiculo['acabamento']       = "";
			$dadosVeiculo['codigoacabamento'] = "";
			$dadosVeiculo['cor']           	  = "";
			$dadosVeiculo['codigoCor']        = "";
			$dadosVeiculo['versao']           = "";
			$dadosVeiculo['opcionais']        = "";
			$dadosVeiculo['preco']            = "";
			 
			$dealerdata 			    = $dealername;		
			array_push($response['dealers'], $dealerdata);
			
			$response['cep'] = "";
			$response['codigoOrigem'] = "229";					
			$response['dadosPessoa']  = $dadosPessoa; // Passando os dados da pessoa
			$response['dadosVeiculo']  = $dadosVeiculo; // Passando os dados da pessoa
			
			$retorno = str_replace('\\','',json_encode($response,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));		
			return $retorno;						
	  }  

  
?>