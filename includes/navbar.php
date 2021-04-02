		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
			<!-- Sidebar - Brand -->
			<a class="sidebar-brand d-flex align-items-center justify-content-center" href="home.php">
				<div class="sidebar-brand-icon rotate-n-15">
					<i class="fas fa-laugh-wink"></i>
				</div>
				<div class="sidebar-brand-text mx-3">NOSTALGIJA</div>
			</a>
			<!-- Divider -->
			<hr class="sidebar-divider my-0">

			<!-- Nav Item - Dashboard -->
			<li class="nav-item active">
				<a class="nav-link" href="home.php">
					<i class="fas fa-fw fa-tachometer-alt"></i>
					<span>Početna</span>
				</a>
			</li>

			<!-- Divider -->
			<hr class="sidebar-divider">

			<!-- Heading -->
			<div class="sidebar-heading">
				UPLATE
			</div>

			<!-- Nav Item - Charts -->
			<li class="nav-item">
				<a class="nav-link" href="pay.php">
					<i class="fas fa-fw fa-wallet"></i>
					<span>Uplatite članstvo</span>
				</a>
			</li>

			<?php if($user['role'] >= 2){?>
			<!-- Heading -->
			<div class="sidebar-heading">
				ADMIN
			</div>
			<!-- Nav Item - admin -->
			<li class="nav-item">
				<a class="nav-link" href="admin-users.php">
					<i class="fas fa-fw fa-users"></i>
					<span>Pregled članova</span>
				</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="admin-pays.php">
					<i class="fas fa-fw fa-wallet"></i>
					<span>Pregled uplata</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="admin-activity.php">
					<i class="fas fa-fw fa-rss"></i>
					<span>Pregled svih aktivnosti</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="admin-news.php">
					<i class="fas fa-fw fa-newspaper"></i>
					<span>Pregled svih preporuka</span>
				</a>
			</li>
			<?php } ?>
			<!-- Divider -->
			<hr class="sidebar-divider d-none d-md-block">

			<!-- Sidebar Toggler (Sidebar) -->
			<div class="text-center d-none d-md-inline">
				<button class="rounded-circle border-0" id="sidebarToggle"></button>
			</div>
		</ul>