<?php
include 'init.php';
if(!checkLastActivity()){
	unset($_SESSION['logged']);
	unset($_SESSION['key']);
	
	$_SESSION['error'] = true;
	$_SESSION['error_message'] = 'Odjavljeni ste zbog neaktivnosti!';
	header('location: index.php');
}

$email = unhashSession($_SESSION['key']);
$db->where("email", $email);
$user = $db->getOne("users");


$db->where("userId", $user['id']);
$db->orderBy("date", "desc");
$pays = $db->get("payments", 5);

if($_POST){
	if($_POST['napomena']){
		$target_dir = "uploads/";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$md5 = $target_dir.md5(time()).'.png';
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
		}
		// Check if file already exists
		if (file_exists($target_file)) {
			//echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			//echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Pogrešan format slike samo: JPG, JPEG, PNG & GIF!';
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			//echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $md5)) {
				//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
				$_SESSION['success'] = true;
				$_SESSION['success_message'] = 'Uspješno ste poslali dokaz o uplati!<br>Molimo pričekajte da administrator pogleda.';
				
				$data = Array(
					"userId" => $user['id'],
					"app" => 0,
					"status" => 0,
					"image" => 'http://localhost/keke/'.$md5,
					"message" => $_POST['napomena']
				);
				$id = $db->insert('payments', $data);
				addActivity("Dodao dokaz o uplati!");
			} else {
				$_SESSION['error'] = true;
				$_SESSION['error_message'] = 'Pogrešan format slike samo: JPG, JPEG, PNG & GIF!';				
			}
		}
	}else{
		$_SESSION['error'] = true;
		$_SESSION['error_message'] = 'Niste unijeli sve podatke!';
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
	<title>Nostalgija - Pocetna</title>
	<!-- Custom fonts for this template-->
	<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<!-- Custom styles for this template-->
	<link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
	<!-- Page Wrapper -->
	<div id="wrapper">
		<!-- Sidebar -->
		<?php include 'includes/navbar.php';?>
		<!-- End of Sidebar -->

		<!-- Content Wrapper -->
		<div id="content-wrapper" class="d-flex flex-column">
			<!-- Main Content -->
			<div id="content">
				<!-- Topbar -->
				<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
					<!-- Sidebar Toggle (Topbar) -->
					<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
						<i class="fa fa-bars"></i>
					</button>

					<!-- Topbar Navbar -->
					<ul class="navbar-nav ml-auto">
					<!-- Nav Item - User Information -->
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $user['fullname'];?></span>
								<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
							</a>
							<!-- Dropdown - User Information -->
							<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
								<a class="dropdown-item" href="profile.php">
									<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
									Profil
								</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
									<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
									Logout
								</a>
							</div>
						</li>
					</ul>
				</nav>
				<!-- End of Topbar -->
				<!-- Begin Page Content -->
				<div class="container-fluid">
					<?php
					?>
					<div class="d-sm-flex align-items-center justify-content-between mb-4">
						<h1 class="h3 mb-0 text-gray-800">ČLANARINA</h1>
					</div>
					<!-- Color System -->
					<div class="row">
						<div class="col-lg-12">
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
						</div>
						<div class="col-lg-12">
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
						</div>
						<div class="col-lg-12">
							<div class="card mt-5">
								<div class="card-header" style="border-right: 1px solid #e3e6f0">
									POŠALJITE DOKAZ O UPLATI
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-lg-6">
											<form class="user" action="" method="POST" enctype="multipart/form-data">
												<div class="form-group">
													<label for="exampleFormControlTextarea1">Napomena</label>
													<textarea class="form-control" name="napomena" rows="3"><?php if(isset($_POST['napomena'])){echo $_POST['napomena'];}?></textarea>
												</div>
												<div class="form-group">
													<div class="custom-file mb-3">
														<input type="file" class="custom-file-input" id="customFile" name="fileToUpload">
														<label class="custom-file-label" for="customFile">Izaberi sliku</label>
													</div>
												</div>
												<button type="submit" class="btn btn-primary btn-user btn-block">
												  Pošalji
												</button>
												<hr>
											</form>
										</div>
										<div class="col-lg-6">
											<div class="card-header" style="border-right: 1px solid #e3e6f0">
												PREGLED UPLATA
											</div>
											<div class="card-body">										
												<table class="table">
												  <thead>
													<tr>
													  <th scope="col">Status uplate</th>
													  <th scope="col">Datum uplate</th>
													</tr>
												  </thead>
												  <tbody>
													<?php 
													if($pays){
														foreach($pays as $pay){
														?>
														<tr>
														  <td>
															<?php 
																if($pay['status'] == 0){ 
																	echo '<i class="fas fa-clock fa-sm fa-fw mr-2" style="color:orange"></i>';
																}else if($pay['status'] == 1){
																	echo '<i class="fas fa-times fa-sm fa-fw mr-2" style="color:red"></i>';
																}else if($pay['status'] == 2){
																	echo '<i class="fas fa-check fa-sm fa-fw mr-2" style="color:Green"></i>';
																}												
															?>
														</td>
														 <td><?php echo date("d.m.Y H:i:s", strtotime($pay['date']));?></td>
														</tr>
														<?php
														}
													}else{
														echo 'Nemate nijednu uplatu!';
													}
													?>
												  </tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							 </div>
						</div>
					</div>
				</div>
			</div>
			<!-- Footer -->
			<footer class="sticky-footer bg-white">
				<div class="container my-auto">
					<div class="copyright text-center my-auto">
						<span>Copyright &copy; Your Website 2019</span>
					</div>
				</div>
			</footer>
			<!-- End of Footer -->
		</div>
	</div>
  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Da li ste sigurni da se želite odjaviti?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Kliknite na "Odjavi me" ako ste sigurni.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Otkaži</button>
          <a class="btn btn-primary" href="logout.php">Odjavi me</a>
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

  <!-- Page level plugins -->
  <script src="assets/vendor/chart.js/Chart.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="assets/js/demo/chart-area-demo.js"></script>
  <script src="assets/js/demo/chart-pie-demo.js"></script>

<script>
// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
  var fileName = $(this).val().split("\\").pop();
  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
</script>
</body>
</html>
