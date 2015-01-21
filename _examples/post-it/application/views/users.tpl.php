<?php /* Starfish Framework Template protection */ die(); ?>
<div class="container-fluid">
	<ul class="cbp-vimenu">
		<li><a href="{/}notes">Notes</a></li>
		<li><a href="{/}categories">Categories</a></li>
		<li class="cbp-vicurrent"><a href="{/}users">Users</a></li>
		<li><a href="{/}logout" class="logout">Logout</a></li>
	</ul>

	<div class="main">

		<div class="row">
			<h1>Users</h1>
			<p>The list of users allowed to use this application</p>

			<?php if (count($list) > 0) : ?>
			<div class="panel panel-default">
				<table class="table table-striped table-bordered">

					<thead>
						<tr>
							<th>Name</th>
							<th width="1%"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($list as $key=>$value): ?>
						<tr>
							<td><?php echo $value['name'];?></td>
							<td width="20%" align="center">
								<a href="{/}users/delete/<?php echo $value['_id'];?>">delete</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<p>No information to display.</p>
			<?php endif; ?>


			<form class="form-inline" role="form" method="post" action="{/}users/add">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">User</div>
						<input class="form-control" type="text" required="" name="user" placeholder="">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">Password</div>
						<input class="form-control" type="password" required="" name="pass" placeholder="">
					</div>
				</div>
				<button type="submit" class="btn btn-default">Create</button>
			</form>
		</div>

	</div>
</div>