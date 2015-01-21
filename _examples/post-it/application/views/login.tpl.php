<?php /* Starfish Framework Template protection */ die(); ?>

<div class="container">
	<div class="row">
		<div class="col-sm-6 col-md-4 col-md-offset-4">
			<img class="profile-img" src="{/}public/logo-simplu.png" alt="">

			<h1 class="text-center login-title">{site_title}</h1>
			<p class="text-center login-description">{site_description}</p>

			<div class="account-wall">
				<form class="form-signin" action="{/}login" method="post">
					<input type="text" class="form-control" placeholder="User" required="" autofocus="" name="user" />
					<input type="password" class="form-control" placeholder="Password" required="" name="pass" />
					<input type="password" class="form-control" placeholder="Key" required="" name="encode" />
					<input type="password" class="form-control" placeholder="Phrase" required="" name="encrypt" />
					<button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
					<?php starfish::obj('errors')->display('authentication_error', '<div class="alert alert-warning text-center">', '</div>'); ?>
				</form>

			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<?php starfish::obj('errors')->display('authentication_error', '<div class="alert alert-warning text-center">', '</div>'); ?>
			</div>
		</div>
	</div>
</div>