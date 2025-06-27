<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php';
//include_once 'includes/code.php';
?>
<?php include_once 'includes/header.php'; ?>

<body class="horizontal lightp">
	<div class="wrapper">
		<main role="main" class="main-content">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header card-bg text-center">
								<h4 class="card-text">Create Users</h4>
							</div>
							<div class="card-body">
								<?= getMessage(@$msg, @$sts); ?>
								<form class="form-horizontal" method="POST" action="includes/code.php" id="formData">
									<input type="hidden" name="action" value="add_new_user">
									<input type="hidden" name="new_user_id" value="<?= @$_REQUEST['user_edit_id'] ?>">

									<div class="form-group row">
										<label class="col-sm-2 control-label">Full Name</label>
										<div class="col-sm-4">
											<input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter Full Name" autocomplete="off" required value="<?= @$fetchusers['fullname'] ?>" />
										</div>
										<label class="col-sm-2 control-label">Username</label>
										<div class="col-sm-4">
											<input type="text" class="form-control" pattern="^[a-zA-Z0-9]*$" id="check_userName" name="username" placeholder="Enter Username" autocomplete="off" required value="<?= @$fetchusers['username'] ?>" />
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 control-label">Phone Number</label>
										<div class="col-sm-4">
											<input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" autocomplete="off" required value="<?= @$fetchusers['phone'] ?>" />
										</div>
										<label class="col-sm-2 control-label">Email</label>
										<div class="col-sm-4">
											<input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" autocomplete="off" required value="<?= @$fetchusers['email'] ?>" />
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 control-label">Password</label>
										<div class="col-sm-4">
											<input type="text" class="form-control" id="password" name="password" placeholder="Enter Password" autocomplete="off" />
											<small class="text-danger">Password will be encrypted automatically for security.</small>
											<input type="hidden" name="old_password" value="<?= isset($_REQUEST['user_edit_id']) ? $fetchusers['password'] : '123456' ?>" />
										</div>

										<label class="col-sm-2 control-label">User Role</label>
										<div class="col-sm-4">
											<select class="form-control" name="user_role" required>
												<option value="">Select Role</option>
												<option <?= (@$fetchusers['user_role'] == "admin") ? "selected" : "" ?> value="admin">Admin</option>
												<option <?= (@$fetchusers['user_role'] == "subadmin") ? "selected" : "" ?> value="subadmin">Sub Admin</option>
												<option <?= (@$fetchusers['user_role'] == "manager") ? "selected" : "" ?> value="manager">Manager</option>
												<option <?= (@$fetchusers['user_role'] == "cashier") ? "selected" : "" ?> value="cashier">Cashier</option>
												<option <?= (@$fetchusers['user_role'] == "localusers") ? "selected" : "" ?> value="localusers">Local User</option>
											</select>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 control-label">Status</label>
										<div class="col-sm-4">
											<select class="form-control" name="status" required>
												<option <?= (@$fetchusers['status'] == "1") ? "selected" : "" ?> value="1">Active</option>
												<option <?= (@$fetchusers['status'] == "0") ? "selected" : "" ?> value="0">Not Active</option>
											</select>
										</div>

										<label class="col-sm-2 control-label">Address</label>
										<div class="col-sm-4">
											<input type="text" class="form-control" id="address" name="address" placeholder="Enter Address" autocomplete="off" required value="<?= @$fetchusers['address'] ?>" />
										</div>

										<div class="col-sm-2 text-right offset-10 mt-2">
											<?= $users_button; ?>
										</div>
									</div>
								</form>
								<br><br>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<div class="card mt-2">
							<div class="card-header cyan-bgcolor text-center">
								<h4>Users List</h4>
							</div>
							<div class="card-body">
								<?php getMessage(@$msg, @$sts); ?>
								<table class="table table-bordered table-striped example1" id="myTable">
									<thead class="text-center">
										<tr>
											<th>User ID</th>
											<th>Username</th>
											<th>Email</th>
											<th>Phone</th>
											<th>Address</th>
											<th>User Role</th>
											<th>Status</th>
											<th>Action</th>
											<th>Set Privileges</th>
										</tr>
									</thead>
									<tbody class="text-center" id="tableData">
										<?php
										$sql = "SELECT * FROM users";
										$result = mysqli_query($dbc, $sql);

										if (mysqli_num_rows($result) > 0) {
											while ($row = mysqli_fetch_array($result)) {
										?>
												<tr>
													<td><?= $row['user_id']; ?></td>
													<td><?= ucwords($row['username']); ?></td>
													<td><?= $row['email']; ?></td>
													<td><?= $row['phone']; ?></td>
													<td><?= ucwords($row['address']); ?></td>
													<td><?= ucwords($row['user_role']); ?></td>
													<td>
														<?php if ($row['status'] == '1'): ?>
															<span class="label label-lg label-info">Available</span>
														<?php else: ?>
															<span class="label label-lg label-danger">Not Available</span>
														<?php endif; ?>
													</td>
													<td>
														<?php if (@$userPrivileges['nav_delete'] == 1 || $fetchedUserRole == "admin"): ?>
															<form action="users.php" method="POST" class="d-inline">
																<input type="hidden" name="user_del_id" value="<?= $row['user_id'] ?>">
																<button type="submit" class="btn btn-admin2 btn-sm m-1">Delete</button>
															</form>
														<?php endif ?>

														<?php if (@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin"): ?>
															<form action="users.php" method="POST" class="d-inline">
																<input type="hidden" name="user_edit_id" value="<?= $row['user_id'] ?>">
																<button type="submit" class="btn btn-admin btn-sm m-1">Edit</button>
															</form>
														<?php endif ?>
													</td>
													<td>
														<a href="privileges.php?new_user_id=<?= base64_encode($row['user_id']) ?>" target="_blank" class="btn btn-admin2 text-white">
															<i class="fa fa-user"></i>
														</a>
													</td>
												</tr>
										<?php
											}
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

				</div> <!-- .row -->
			</div> <!-- .container-fluid -->

		</main> <!-- main -->
	</div> <!-- .wrapper -->

</body>

</html>
<?php include_once 'includes/foot.php'; ?>