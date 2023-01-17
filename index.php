<?php
$mutasi=[];
if(isset($_POST['submit'])){
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	// $norek = $_POST['norek'];
	$bank = $_POST['bank'];
	$tgl1 = $_POST['tgl1'];
	$tgl2 = $_POST['tgl2'];
	// $company_id = $_POST['company_id'];
	// $transaksi = $_POST['transaksi'];
	
	if($bank=='bni_web'){
		require 'Bni_web.php';
		$obj = new Bni;
		$obj->login($user, $pass, $norek, $transaksi, $tgl1, $tgl2);
		$mutasi = $obj->get_mutasi();
	}elseif($bank=='bni_mobile'){
		require 'Bni_mobile.php';
		$obj = new Bni($user, $pass, $norek);
		$mutasi = $obj->get_transactions($tgl1, $tgl2);
		$obj->logout();
	}elseif($bank=='mandiri_bisnis'){
		require 'Mandiri_bisnis.php';
		$obj = new Mandiri_bisnis();
		$mutasi = $obj->get_transactions($company_id, $user, $pass, $norek, $tgl1, $tgl2);
		
	}elseif($bank=='bri'){
		require 'Bri.php';
		$bri = new Bri($user, $pass, $norek);
		$bri->login(); 
		if($bri->login()){
			$bri->get_mutasi($tgl1,$tgl2); 
		}
		$mutasi = $bri->getOutput();
	}elseif($bank=='mandiri_lama'){die('disabled');
		require 'Mandiri_lama.php';
		$mandiri = new Mandiri_lama();
		$mutasi = $mandiri->set_credential($user, $pass, $norek)->set_date($tgl1,$tgl2)->check_mutasi()->respond();
	}elseif($bank=='mandiri_baru'){
		require 'Mandiri_baru2.php';
		$mandiri = new Mandiri_baru();
		
		//$mutasi = $mandiri->get_transactions($user, $pass, $norek, $tgl1,$tgl2);
		$mutasi = $mandiri->get_transactions_and_saldo($user, $pass, $norek, $tgl1,$tgl2);
		// var_dump($mutasi);exit;
	}elseif($bank=='jenius'){
		require 'Jenius.php';
		$jenius = new Jenius();
		$jenius->login($user, $pass); 
		$mutasi = $jenius->get_mutasi();
		
	}elseif($bank=='bca'){
		require 'Bca.php';
		$bca = new Bca;
		$mutasi = $bca->getMutasi($user, $pass, $norek, $tgl1, $tgl2);
		
	}elseif($bank=='BPD'){
		require 'BPD.php';
		$parser = new IbParser;
		$mutasi = $parser->getTransactions($bank, $user, $pass, $tgl1, $tgl2);
		
	}else{
		$mutasi[] = 'bank tidak dikenal';
	}
}
?><!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.standalone.min.css" />
</head>
<body>
<form method="post">
username <input required type="text"  name="user" ><br>
password <input required type="password" name="pass" ><br>
<!-- company id <input  type="text" name="company_id" > for bisnis<br> -->
tanggal <input required type="text" class="datepicker" name="tgl1" value="<?=date('d/m/Y')?>"> - <input required type="text" class="datepicker" value="<?=date('d/m/Y')?>" name="tgl2"><br>
<!-- transaksi <select name="transaksi">
<option value="">semua</value>
<option value="D">debet</value>
<option value="C">kredit</value>
</select><br> -->
Bank <select required id="bank" name="bank">
<option value="BPD">Bank Jateng</value>
<!-- <option  disabled value="mandiri_lama">Mandiri Lama</value> -->
<!-- <option value="mandiri_baru">Mandiri Baru</value> -->
<!-- <option value="mandiri_bisnis">Mandiri Bisnis</value> -->
<!-- <option disabled value="bni_web">BNI Web</value> -->
<!-- <option  value="bni_mobile">BNI Mobile</value> -->
<!-- <option  value="bri">BRI</value> -->
<!-- <option value="jenius">Jenius (BTPN)</value> -->
<!-- <option disabled value="telkomsel">Telkomsel</value> -->
<!-- <option value="ovo">OVO</value> -->
</select><br>
<input type="submit" name="submit">
</form>
<hr>
json
<div><?=json_encode($mutasi);?></div>
print_r
<pre><?php print_r($mutasi);?></pre>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function(){
	$( ".datepicker" ).datepicker({
		format: 'dd/mm/yyyy',
		//startDate: '-30d'
	});
	
	$('#bank').change(function(){
		if($(this).val()=='ovo'){
			if(confirm('Want to test ovo?')){
				window.location = 'test_ovo.php';
			}
		}
	});
})
</script>
</body>
</html>