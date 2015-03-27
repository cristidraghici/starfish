<?php /* Starfish Framework Template protection */ die(); ?>
<div class="container-fluid">
	<h1>Translation</h1>
	<form action="" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="pattern">Translate pattern</label>
			<input type="text" class="form-control" id="pattern" name="pattern" placeholder="Enter pattern to translate">
			<p class="help-block">Please enter the pattern to search in the file, using {T} for the strings you want translated.</p>
		</div>
		<div class="form-group">
			<label for="file">File input</label>
			<input type="file" id="file" name="file">
		</div>

		<button type="submit" class="btn btn-default">Submit</button>
	</form>

	<?php if (isset($translation) && strlen($translation) > 0): ?>
	<pre class="translation"><?php echo $translation; ?></pre>
	<?php endif; ?>
</div>