<?php
//https://phppot.com/php/php-contact-form-with-google-recaptcha/
include 'init.php';
if(checkLastActivity()){
	header('location: home.php');
}
//$ip = getInfoIP(getClientIP());
function validate_rechapcha($response){
	// Verifying the user's response (https://developers.google.com/recaptcha/docs/verify)
	$verifyURL = 'https://www.google.com/recaptcha/api/siteverify';

	$query_data = [
		'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
		'response' => $response,
		'remoteip' => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'])
	];
	// Collect and build POST data
	$post_data = http_build_query($query_data, '', '&');

	// Send data on the best possible way
	if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec'))
	{
		// Use cURL to get data 10x faster than using file_get_contents or other methods
		$ch = curl_init($verifyURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-type: application/x-www-form-urlencoded'));
		$response = curl_exec($ch);
		curl_close($ch);
	}
	else
	{
		// If server not have active cURL module, use file_get_contents
		$opts = array('http' =>
			array(
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content' => $post_data
			)
		);
		$context = stream_context_create($opts);
		$response = file_get_contents($verifyURL, false, $context);
	}

	// Verify all reponses and avoid PHP errors
	if ($response)
	{
		$result = json_decode($response, true);
		if ($result['success'] != true)
		{
			return true;
		}
		else
		{
			return $result;
		}
	}

	// Dead end
	return false;
}


function validate_phone_number($phone)
{
 // Allow +, - and . in phone number
 $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
 // Remove "-" from number
 $phone_to_check = str_replace("-", "", $filtered_phone_number);
 // Check the lenght of number
 // This can be customized if you want phone number from a specific country
 if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
	return false;
 } else {
   return true;
 }
}
$ip = getInfoIP("77.77.216.97");
if($_POST){
	if($_POST['fullname'] && 
		$_POST['phone'] && 
		$_POST['address'] &&
		$_POST['email'] && 
		$_POST['country'] && 
		$_POST['password'] && 
		$_POST['ppassword']
	){
		if(strlen($_POST['fullname']) < 5){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Polje Ime i Prezime mora da ima najmanje 6 karaktera!';
		}else if(validate_phone_number($_POST['phone']) != true){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Broj koji ste unijeli nije validan!';							
		}else if(strlen($_POST['address']) < 5){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Polje Adresa mora da ima najmanje 6 karaktera!';			
		}else if(strlen($_POST['country']) < 2){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Polje Država mora da ima najmanje 2 karaktera!';			
		}else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Pogrešan format email adrese!';			
		}else if(strlen($_POST['password']) < 7){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Šifra mora da ima najmanje 8karaktera!';
		}else if($_POST['password'] != $_POST['ppassword']){
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Šifra i potvrdna šifra nisu iste!';				
		}else{
			if (isset($_POST['g-recaptcha-response'])) {
				if(validate_rechapcha($_POST['g-recaptcha-response'])){
				// AKO JE SVE OK REGAJ GA
					$data = Array(
						"fullname" => $_POST['fullname'],
						"phoneNumber" => $_POST['phone'],
						"address" => $_POST['address'],
						"country" => $_POST['country'],
						"status" => 0,
						"role" => 0,
						"email" => $_POST['email'],
						"password" => $_POST['password']
					);
					$id = $db->insert('users', $data);
					if($id){
						$_SESSION['success'] = true;
						$_SESSION['success_message'] = 'Uspješno ste se registrovali!<br>Sada se možete prijaviti.';
					}else{
						$_SESSION['error'] = true;
						$_SESSION['error_message'] = 'Već postoji nalog sa unesenim email-om!';					
					}
					addActivity("Register");
				}else{
					$_SESSION['error'] = true;
					$_SESSION['error_message'] = 'Molimo vas da potvrdite da niste robot (bot)!';					
				}
			}else{
				$_SESSION['error'] = true;
				$_SESSION['error_message'] = 'Molimo vas da potvrdite da niste robot (bot)!';			
			}
		}
	}else{
		$_SESSION['error'] = true;
		$_SESSION['error_message'] = 'Niste popunili sva polja forme!';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>Nostalgija - Registracija</title>
	<!-- Custom fonts for this template-->
	<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<!-- Custom styles for this template-->
	<link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
	<div class="container">
		<!-- Outer Row -->
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-6 col-md-9">
				<div class="card o-hidden border-0 shadow-lg my-5">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
							<div class="col-lg-12">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4">NOSTALGIJA - REGISTRACIJA</h1>
									</div>
									<?php if(isset($_SESSION['error'])){?>
									<div class="card mb-4 py-3 border-bottom-danger">
										<div class="card-body">
										  <?php echo $_SESSION['error_message'];?>
										</div>
									 </div>
									<?php 
									unset($_SESSION['error']);
									unset($_SESSION['error_message']);
									} ?>
									<?php if(isset($_SESSION['success'])){?>
									<div class="card mb-4 py-3 border-bottom-success">
										<div class="card-body">
										  <?php echo $_SESSION['success_message'];?>
										</div>
									 </div>
									<?php 
									unset($_SESSION['success']);
									unset($_SESSION['success_message']);
									} ?>															
									<form class="user" method="POST" action="">
										<div class="form-group">
											<input type="text" class="form-control form-control-user" name="fullname" placeholder="Ime i Prezime" value="<?php if(isset($_POST['fullname'])){echo $_POST['fullname'];}?>">
										</div>
										<div class="form-group">
											<input type="text" class="form-control form-control-user" name="phone" placeholder="Telefon (npr. +38762123123)" value="<?php if(isset($_POST['phone'])){echo $_POST['phone'];}?>">
										</div>
										<div class="form-group">
											<input type="text" class="form-control form-control-user" name="email" placeholder="Email" value="<?php if(isset($_POST['email'])){echo $_POST['email'];}?>">
										</div>
										<div class="form-group">
											<input type="text" class="form-control form-control-user" name="address" placeholder="Adresa" value="<?php if(isset($_POST['address'])){echo $_POST['address'];}?>">
										</div>										
										<div class="form-group">
											<input type="text" class="form-control form-control-user" name="country" placeholder="Država" value="<?php if(isset($_POST['country'])){echo $_POST['country'];}?>">
										</div>
										<div class="form-group">
											<input type="password" class="form-control form-control-user" name="password" placeholder="Šifra" value="<?php if(isset($_POST['password'])){echo $_POST['password'];}?>">
										</div>
										<div class="form-group">
											<input type="password" class="form-control form-control-user" name="ppassword" placeholder="Potvrda šifre" value="<?php if(isset($_POST['ppassword'])){echo $_POST['ppassword'];}?>">
										</div>
										<div class="g-recaptcha"
											data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
										<button type="submit" class="btn btn-primary btn-user btn-block">
										Registruj se
										</button>
									</form>
									<hr>
									<div class="text-center">
										<a class="small" href="index.php">Već si registrovan?</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

  <!-- Bootstrap core JavaScript-->
  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="assets/js/sb-admin-2.min.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
</body>
</html>