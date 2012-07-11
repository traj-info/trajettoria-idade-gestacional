<?php
/*
Plugin Name: Trajettoria Idade Gestacional
Plugin URI: http://www.trajettoria.com
Description: Calcula a idade gestacional da mulher.
Version: 1.0
Author: Victor Kurauchi
Author URI: http://www.trajettoria.com
License: GPLv2
*/

class idade_gestacional {
	
	public static function calc_handler(){																#função de controle do shortcode, que redireciona para a função principal.
		$traj = idade_gestacional::calc_idade_gestacional();
		return $traj;
	}
	
	public static function Initialize(){
		
	}
	
	public static function Install() {

	}
	
	public static function obtemSemanas($d) {															#Função que obtém as semanas da IG de acordo com os dias calculados
		$a = floor($d / 7);																				# a = Semanas (Dividindo dias recuperador por 7 para obter qtde de semanas)
		$b = ($d % 7);																					# b = Dias restantes (Pegando o resto da divisão para obter dias restantes)
		return $a." Semanas e ".$b." Dias </span></div>";
	}
	
	public static function calculaDum($dataMenstruacao) {
	
		$dataFormatada = str_replace("/", "-", $dataMenstruacao);										#Substituindo as barras digitas pelo - para poder realizar a conversão de datas
	
		$dataAtual = date("Y-m-d");																		#Obtendo data atual para calcular DUM
	
		$novaDataMenst = date("Y-m-d", strtotime($dataFormatada));										#Convertendo DUM informada para Ano/Mes/Dia
	
		$dataMenstruacao_timestamp = strtotime($novaDataMenst);											#Convertendo datas para timestamp (Formato unix)
		$dataAtual_timestamp = strtotime($dataAtual);													#Convertendo datas para timestamp (Formato unix)
	
		$diff = $dataAtual_timestamp - $dataMenstruacao_timestamp;										#Subtraindo data atual pela data da última menstruação
	
		return round($diff / 86400);
	
	}
	
	public static function calculaUsg($dataUltrassom) {
	
		$dataFormatada = str_replace("/", "-", $dataUltrassom);											#Substituindo as barras digitas pelo - para poder realizar a conversão de datas
	
		$dataAtual = date("Y-m-d");																		#Obtendo data atual para calcular USG
	
		$novaDataUltrassom = date("Y-m-d", strtotime($dataFormatada));									#Convertendo data do primeiro ultrassom para Ano/Mes/Dia
	
		$dataUltrassom_timestamp = strtotime($novaDataUltrassom);										#Convertendo datas para timestamp (Formato unix)
		$dataAtual_timestamp = strtotime($dataAtual);													#Convertendo datas para timestamp (Formato unix)
	
		$diff = $dataAtual_timestamp - $dataUltrassom_timestamp;										#Subtraindo data atual pela data da última menstruação
	
		return round($diff / 86400);
	
	}
	
	public static function FilterData($variable){
	
		#gera warning se a conexao com o banco nao estiver estabelecida
		return mysql_real_escape_string(strip_tags($variable));
	}
	
	public static function ValidaData($dat){
		
		$pattern = '/^[0-3][0-9]\/[0-1][0-9]\/20[0-9][0-9]$/';
		if (preg_match($pattern, $dat) == true )  {
			$data = explode("/","$dat"); 																					#fatia a string $dat em pedados, usando / como referência
			$d = $data[0];
			$m = $data[1];
			$y = $data[2];
		
			$res = checkdate($m,$d,$y);																						#verifica se a data é válida, onde 1 true = válida, 0 false = inválida
			if ($res != 1){
				echo "<div class='msgErro'><span>Formato de data inv&aacute;lido, digite novamente (DD/MM/AAAA)</span></div>";
			}
		}		
		else
			echo "<div class='msgErro'><span>Digite apenas n&uacute;meros na data, e no formato (DD/MM/AAAA)</span></div>";
	}
	
	public static function validaNumeros($string) {
		$pattern = '/^\d{1,2}$/';
			if (preg_match($pattern, $string) != true)
		echo "<div class='msgErro'><span>Digite apenas n&uacute;meros nos campos de Semana e Dias</span></div>";
	}
	
	public static function calc_idade_gestacional() {																		#função principal do plugin, onde será feita toda a lógica do mesmo.
	
		if( $is_widget )
		echo $before_widget . $before_title . $after_title;
	
		?>
		
		<script type="text/javascript">																	
				function desabilita() {
					
					if (document.getElementById("chkRemember").checked == true) 
						document.getElementById("dtUltimaMenstruacao").disabled = false;
					else 
						document.getElementById("dtUltimaMenstruacao").disabled = true;
				}
		</script>
		
		<form id="frmCalculo" name="frmCalculo" method="post" action="">
			
			<div class="remember_check"><label for="chkRemember">Recorda-se com exatid&atilde;o da data de sua &uacute;ltima menstrua&ccedil;&atilde;o ? </label>
			<input type="checkbox" name="chkRemember" id="chkRemember" onclick="desabilita()" /></div>
			
			<div class="ultimaMenst_input"><label for="dtUltimaMenstruacao">Informe a data da sua &uacute;ltima menstrua&ccedil;&atilde;o  (DD/MM/AAAA)</label>
			<input type="text" disabled="true" name="dtUltimaMenstruacao" maxlength="10" id="dtUltimaMenstruacao" class="datepicker" value="<?php echo trim(idade_gestacional::FilterData($_POST['dtUltimaMenstruacao'])); ?>" /></div>
			
			
			<div class="primeiroUltra_input"><label for="dtUltrassom">Informe a data em que foi realizado o primeiro ultrassom  (DD/MM/AAAA)</label>
			<input type="text" name="dtUltrassom" id="dtUltrassom" maxlength="10" class="datepicker" value="<?php echo trim(idade_gestacional::FilterData($_POST['dtUltrassom'])); ?>"/></div>
			
			<div class="idadeUltra_input"><label>Informe a idade gestacional estimada do primeiro ultrassom </label>
			<input type="text" name="idadeSemana" id="idadeSemana" size="2" maxlength="2" value="<?php echo trim(idade_gestacional::FilterData($_POST['idadeSemana'])); ?>" /><label>semanas</label>
			<input type="text" name="idadeDia" id="idadeDia" size="2" maxlength="1" value="<?php echo trim(idade_gestacional::FilterData($_POST['idadeDia'])); ?>" /><label>dias</label></div>
			
			<input type="submit" value="Calcular" name="submit" id="submit" />
			
		</form> 
		
			<?php 
			if(isset($_POST['submit'])) {
			
				/* if (trim(idade_gestacional::FilterData($_POST['dtUltrassom'])) > date("d-m-Y"))
					echo "<div class='msgErro'><span>Data do primeiro ultrassom n&atilde;o pode ser maior que a data atual</span></div>"; */
				
				if (trim(idade_gestacional::FilterData($_POST['dtUltimaMenstruacao'])) < 0)												#Validando datas
					echo "<div class='msgErro'><span>Data da &Uacute;ltima Menstrua&ccedil;&atilde;o n&atilde;o pode ser negativa</span></div>";
		
				if (trim(idade_gestacional::FilterData($_POST['dtUltrassom'])) < 0)														#Validando datas
					echo "<div class='msgErro'><span>Data do Ultrassom n&atilde;o pode ser negativa</span></div>";
		
				if (trim(idade_gestacional::FilterData($_POST['idadeDia'])) < 0)														#Validando campo dia
					echo "<div class='msgErro'><span>Campo Dia n&atilde;o pode ser negativo</span></div>";
				
				if (trim(idade_gestacional::FilterData($_POST['idadeSemana'])) < 0)														#Validando campo semana
					echo "<div class='msgErro'><span>Campo Semana n&atilde;o pode ser negativo</span></div>";
				
				if (trim(idade_gestacional::FilterData($_POST['dtUltimaMenstruacao'])) != null)											#Validando datas
					idade_gestacional::ValidaData(idade_gestacional::FilterData($_POST['dtUltimaMenstruacao']));
		
				if (trim(idade_gestacional::FilterData($_POST['dtUltrassom'])) != null) 												#Validando datas
					idade_gestacional::ValidaData(idade_gestacional::FilterData($_POST['dtUltrassom']));
		
				if (trim(idade_gestacional::FilterData($_POST['idadeSemana'])) > 44) {													#Se o número de semanas informada for maior que 44, então exibir mensagem de erro
					echo "<div class='msgErro'><span>Dado incorreto (M&aacuteximo de 44 semanas)</span></div>";
				}
		
				if (trim(idade_gestacional::FilterData($_POST['idadeDia'])) > 6) {														#Se o número de dias for maior que 6, então exibir mensagem de erro
					echo "<div class='msgErro'><span>Dado incorreto (M&aacuteximo de 6 dias)</span></div>";
				}
		
				if (trim(idade_gestacional::FilterData($_POST['idadeDia'])) != NULL)													#Validação para campos apenas numéricos
					idade_gestacional::validaNumeros($_POST['idadeDia']);
		
				if (trim(idade_gestacional::FilterData($_POST['idadeSemana'])) != NULL)													#Validação para campos apenas numéricos
					idade_gestacional::validaNumeros($_POST['idadeSemana']);
				
				if (trim(idade_gestacional::FilterData($_POST[$total_dias_dum])) > 315) 
					echo "<div class='msgErro'><span>Quantidade de semanas da &Uacute;ltima menstrua&ccedil;&atilde;o n&atilde;o deve ser maior do que 45</span></div>";
				
				if (trim(idade_gestacional::FilterData($_POST[$total_dias_usg])) > 315) 
					echo "<div class='msgErro'><span>Quantidade de semanas do Ultrassom n&atilde;o deve ser maior do que 45</span></div>";
				
				if (($_POST[$dataUltrassom]) == NULL || ($_POST['idadeSemana']) == NULL || ($_POST['idadeDia']) == NULL) 
					echo "<div class='msgErro'><span>Dados devem ser preenchidos!</span></div>";

			if( $is_widget )
				echo $after_widget;
			
			$dataMenstruacao = trim(idade_gestacional::FilterData($_POST['dtUltimaMenstruacao']));
			$dataUltrassom = trim(idade_gestacional::FilterData($_POST['dtUltrassom']));
			
				
				echo "<hr>";
				
					$total_dias_dum = idade_gestacional::calculaDum($dataMenstruacao);
					
					if ($total_dias_dum < 315 && $total_dias_dum >0) {
						$dum_format = str_replace("/", "-", $dataMenstruacao);
						$newdum_format = date("Y-m-d", strtotime($dum_format));
						$dpp_dum = strtotime($newdum_format . "+280 days");
						
						echo "<div class='textoCalculo' id='ig_dum'><span>Sua idade gestacional conforme a Data da &Uacute;ltima Menstrua&ccedil;&atilde;o: ".idade_gestacional::obtemSemanas($total_dias_dum);
						echo "<div class='textoCalculo'><span>Data Prov&aacute;vel do Parto: ".date('d/m/Y', $dpp_dum)."</span><div>";
					}
					
					$dias_usg = idade_gestacional::calculaUsg($dataUltrassom);
					$semanas_ig_ultrassom = trim(idade_gestacional::FilterData($_POST['idadeSemana']));		#Obtendo idade gestacional do primeiro ultrassom
					$dias_ig_ultrassom = trim(idade_gestacional::FilterData($_POST['idadeDia']));			#Obtendo idade gestacional do primeiro ultrassom
					
					$calculo = ($semanas_ig_ultrassom * 7) + $dias_ig_ultrassom ;							#Obtém as semanas de IG do ultrassom e multiplica por 7 para descobrir os dias, e adiciona mais os dias que foram informados (De acordo com a IG do primeiro ultrassom)
					$total_dias_usg = $calculo + $dias_usg;
					
					if ($total_dias_usg < 315 && $total_dias_usg > 0) {
						$format = str_replace("/", "-", $dataUltrassom);									#Calculando a data provável do parto, de maneira que se obtém as datas digitadas,
						$newformat = date("Y-m-d", strtotime($format));										#e elas são formatadas para timestamp e calcula-se o dia do parto
						$idadePrimeiroUltrassom = strtotime("-".$calculo." days");
						$dataPrimeiroUltrassom = strtotime($newformat . "+280 days");
						
						$UltrassomFormatado = date("Y-m-d",$dataPrimeiroUltrassom );						#Aqui passamos a data do ultrassom obtida no timestamp para um formato Y/m/d								
						$dpp_usg = strtotime($UltrassomFormatado . "-".$calculo." days") ;					#e assim é possível realizar o cálculo entre Data do ultrassom - Idade no dia do ultrassom
						
						echo "<div class='textoCalculo' id='ig_usg'><span>Sua idade gestacional atual conforme o Ultrassom: ".idade_gestacional::obtemSemanas($total_dias_usg);
						echo "<div class='textoCalculo'><span>Data Prov&aacute;vel do Parto: ".date('d/m/Y', $dpp_usg)."</span><div>";
					}
				
					#Calculando a diferença entre as IGs					
					if ($total_dias_usg > $total_dias_dum) {												#Se a IG conforme USG for maior que a IG conforme DUM, então
						$diff = $total_dias_usg - $total_dias_dum ;											#calcular a diferença entre Idade Gestacional USG - Idade Gestacional DUM
					}					
					else {																					#Se a IG conforme DUM for maior que a IG conforme USG, então		
						$diff = $total_dias_dum - $total_dias_usg	;										#calcular a diferença entre Idade Gestacional DUM - Idade Gestacional USG
					}
	
					#Condições de idade gestacional provável
					echo "<hr>";
					
					if (isset($_POST['chkRemember']) != NULL && $_POST['chkRemember'] != "" ) {																#Se ela se lembra da DUM
					
						if ($total_dias_dum < 315) {
						
							if ($diff <= 10) {																												#Se a diferença for <= 10 dias, considerar DUM
								echo "<div class='textoCalculo'><span>Idade Gestacional prov&aacute;vel: ".idade_gestacional::obtemSemanas($total_dias_dum);
							}
							else if ($total_dias_usg <= 91) {																								#Senão, considerar USG desde que IGu <= 13 semanas
								echo "<div class='textoCalculo'><span>Idade Gestacional prov&aacute;vel: ".idade_gestacional::obtemSemanas($total_dias_usg);
							}
							else {																															#E se USG > 13 semanas, considerar DUM
								echo "<div class='textoCalculo'><span>Idade Gestacional prov&aacute;vel: ".idade_gestacional::obtemSemanas($total_dias_dum);
							}
						}
					}
					
					else if (isset($_POST['chkRemember']) == NULL && $_POST['chkRemember'] == "") {															#Se ela não se lembra da DUM
						
						if (trim(idade_gestacional::FilterData($_POST['dtUltrassom'])) != NULL ) 	{
							if ($total_dias_usg < 315)  {
								if ($total_dias_usg > 91) {
									echo "<div class='textoCalculo'><span>Idade Gestacional prov&aacute;vel (Com poss&iacute;vel erro de data) : ".idade_gestacional::obtemSemanas($total_dias_usg);
								} 
								else 
									echo "<div class='textoCalculo'><span>Idade Gestacional prov&aacute;vel: ".idade_gestacional::obtemSemanas($total_dias_usg);
							
							}
						}
					}
			}
		}

}
register_activation_hook(__FILE__,array('idade_gestacional','Install'));
add_filter('init', array('idade_gestacional','Initialize'));
add_shortcode("traj-idade-gestacional", array('idade_gestacional','calc_handler'));		

add_action( 'init', 'wpapi_date_picker' );
function wpapi_date_picker() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-datepicker', 'http://jquery-ui.googlecode.com/svn/trunk/ui/jquery.ui.datepicker.js', array('jquery', 'jquery-ui-core' ) );
}

add_action( 'wp_footer', 'wpapi_print_scripts');
function wpapi_print_scripts() {
	
	?>
<script type="text/javascript">
	jQuery(document).ready(function() {
	    jQuery('.datepicker').datepicker({
	        dateFormat : 'dd/mm/yy'
	    });
	});
</script>
    <?php
}

?>