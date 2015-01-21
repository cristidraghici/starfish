<?php /* Starfish Framework Template protection */ die(); ?>

<div class="container-fluid">
	<ul class="cbp-vimenu">
		<li><a href="{/}notes">Notes</a></li>
		<li class="cbp-vicurrent"><a href="{/}categories">Categories</a></li>
		<li><a href="{/}users">Users</a></li>
		<li><a href="{/}logout" class="logout">Logout</a></li>
	</ul>

	<div class="main">

		<div class="row">
			<h1>Categories</h1>
			<p>Your categories for the notes added</p>

			<?php if (isset($list) && count($list) > 0) : ?>
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
								<a href="{/}categories/delete/<?php echo $value['_id'];?>">delete</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<p>No information to display.</p>
			<?php endif; ?>


			<form class="form-inline" role="form" method="post" action="{/}categories/add">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">Title</div>
						<input class="form-control" type="text" required="" name="name" placeholder="">
					</div>
				</div>
				<button type="submit" class="btn btn-default">Create</button>
			</form>
		</div>

	</div>
</div>