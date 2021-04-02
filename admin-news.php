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

if(!($user['role'] >= 2)){
	header('location: home.php');
}

$cats = $db->get("categories");

if($_GET){
	if(isset($_GET['visible']) && isset($_GET['id'])){
		if($_GET['visible'] == true || $_GET['visible'] == false){
			if($_GET['visible'] == "true"){
				$status = 1;
			}else if($_GET['visible'] == "false"){
				$status = 0;
			}
			
			$data = Array(
				'active' => $status,
			);
			$db->where('id', $_GET['id']);
			if($db->update('news', $data)){
				$_SESSION['success'] = true;
				$_SESSION['success_date'] = time();
				if($status == 0){
					$_SESSION['success_message'] = "Uspješno ste sakrili preporuku ID: ". $_GET['id']."!";
					addActivity("Admin je sakrio preporuku ID: ". $_GET['id']);	
					header('location: admin-news.php');
				}else if($status == 1){
					$_SESSION['success_message'] = "Uspješno ste vratili vidljivost preporuci ID: ". $_GET['id']."!";
					addActivity("Admin je vratio vidljivost preporuci ID: ". $_GET['id']);	
					header('location: admin-news.php');
				}
			}
		}
	}
}

if($_GET){
	if(isset($_GET['delete']) && isset($_GET['id'])){
		if($_GET['delete'] == true || $_GET['delete'] == false){
			if($_GET['delete'] == "true"){
				$db->where('id', $_GET['id']);
				if($db->delete('news')){
					$_SESSION['success'] = true;
					$_SESSION['success_date'] = time();				
					$_SESSION['success_message'] = 'Uspješno ste obrisali preporuku ID: '.$_GET['id'].'!';
					addActivity("Admin je obrisao preporuku ID: ". $_GET['id']);	
					header('location: admin-news.php');
				}
			}
		}
	}
}

if($_POST){
	if($_POST['name'] && $_POST['cat'] && $_POST['body']){
		$data = Array(
			"userId" => $user['id'],
			"name" => $_POST['name'],
			"catId" => $_POST['cat'],
			"active" => isset($_POST['visible']) ? 1 : 0,
			"body" => $_POST['body'],
		);
		$id = $db->insert('news', $data);
		if($id){
			addActivity("Admin je dodao novu preporuku ID: ". $id);	
			$_SESSION['success'] = true;
			$_SESSION['success_message'] = 'Uspješno ste dodali novu preporuku!';
		}else{
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = 'Neka greška!<br> Javi znaš već kome!';
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
	<title>Nostalgija - Preporuke</title>
	<!-- Custom fonts for this template-->
	<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<!-- Custom styles for this template-->
	<link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
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
						<h1 class="h3 mb-0 text-gray-800">PREGLED SVIH PREPORUKA</h1>
					</div>
					<!-- Color System -->
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
								if(time() > ($_SESSION['success_date'])){
									unset($_SESSION['success']);
									unset($_SESSION['success_message']);
								}
							} ?>
						</div>
						<div class="card shadow mb-4">
							<div class="card-header py-3">
								<h6 class="m-0 font-weight-bold text-primary">Pregled svih preporuka</h6>
							</div>
							<div class="card-body">
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Dodaj novu preporuku</button><hr>
								<div class="table-responsive">
									<div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">
										<div class="row">
											<div class="col-sm-12 col-md-12">
												<table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
													<thead>
														<tr role="row">
															<th class="sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 58px;">ID</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 63px;">Kategorija</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 50px;">Naziv</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 50px;">Text(HTML)</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 31px;">Vidljivo</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 31px;">Objavio</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 68px;">Datum</th>
															<th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="" style="width: 67px;">Akcija</th>
														</tr>
													</thead>
													<tfoot>
														<tr>
															<th rowspan="1" colspan="1">ID</th>
															<th rowspan="1" colspan="1">Kategorija</th>
															<th rowspan="1" colspan="1">Naziv</th>
															<th rowspan="1" colspan="1">Text(HTML)</th>
															<th rowspan="1" colspan="1">Vidljivo</th>
															<th rowspan="1" colspan="1">Objavio</th>
															<th rowspan="1" colspan="1">Datum</th>
															<th rowspan="1" colspan="1">Akcija</th>
														</tr>
													</tfoot>
													<tbody>
														<?php
														$news = $db->get('news');
														foreach($news as $new){
														?>
															<tr role="row" class="odd">
																<td><?= $new['id'];?></td>
																<td><?= getCatById($new['catId'])['name'];?></td>
																<td><?= $new['name'];?></td>
																<td><?= $new['body'];?></td>
																<td>
																	<?php
																		if($new['active'] == 1){echo 'DA';}else{echo 'NE';}
																	?>
																</td>
																<td><?= getUserById($new['userId'])['fullname'];?>(<?= $new['userId'];?>)</td>
																<td><?= date("d.m.Y H:i:s", strtotime($new['date']));?></td>										
																<td>
																	<?php 
																		if($new['active'] == 1){
																	?>
																			<a href="?visible=false&id=<?= $new['id'];?>" class="btn btn-warning btn-circle">
																				<i class="fas fa-eye-slash"></i>
																			</a>
																	<?php 
																		}else{
																	?>
																			<a href="?visible=true&id=<?= $new['id'];?>" class="btn btn-success btn-circle">
																				<i class="fas fa-eye"></i>
																			</a>
																	<?php
																		}
																	?>
																	<a href="?delete=true&id=<?= $new['id'];?>" class="btn btn-danger btn-circle">
																		<i class="fas fa-times"></i>
																	</a>
																</td>										
															</tr>
														<?php 
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
						<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"id="myLargeModal" aria-labelledby="myLargeModalLabel" aria-hidden="true">
						  <div class="modal-dialog modal-lg">
							<div class="modal-content">
								 <div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Nova preporuka</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									  <span aria-hidden="true">&times;</span>
									</button>
								  </div>
								  <div class="modal-body">
										<form action="" method="POST">
										  <div class="form-group">
											<label for="name">Naziv</label>
											<input type="text" class="form-control" id="name" name="name" placeholder="Unesite naziv">
										  </div>
										  <div class="form-group">
											<label for="cats">Izaberite kategoriju</label>
											<select class="form-control" id="cats" name="cat">
											<?php foreach($cats as $cat){?>
											  <option value="<?= $cat['id'];?>"><?= $cat['name'];?></option>
											<?php } ?>
											</select>
										  </div>
										  <div class="form-group">
										  <div class="row">
										  <textarea name="body" style="height: 100px;" cols="50" id="area5">HTML <b>content</b> <i>default</i> in textarea</textarea>	
										</div>
										</div>
										  <div class="form-check">
											<input type="checkbox" name="visible" class="form-check-input" id="visible" value="1" checked>
											<label class="form-check-label" for="visible">Vidljivost</label>
										  </div>
										  <button type="submit" class="btn btn-primary">Dodaj</button>
										</form>
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
  <!-- Page level plugins -->
  <script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
	 // Call the dataTables jQuery plugin
	$(document).ready(function() {
	  $('#dataTable').DataTable({
		 "order": [[ 6, "desc" ]]
	  });
	});
	$('#myLargeModal').on('show.bs.modal', function (event) {
		setTimeout(function(){ 
			$('div[unselectable="on"]').first().css( "width", "100%" ); 
		}, 1000);
	})
   </script>
 <script type="text/javascript" src="assets/js/nicEdit-latest.js"></script> <script type="text/javascript">
//<![CDATA[
  bkLib.onDomLoaded(function() {
        new nicEditor({maxHeight : 100}).panelInstance('area5');
  });
  //]]>
  </script>
</body>
</html>
