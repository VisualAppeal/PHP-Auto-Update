<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>PHP Auto Update</title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container mt-4">
			<p>This is the test index.</p>

			<p><a class="btn btn-primary" href="/example/client/update/index.php">Update now!</a></p>

			<p>Contents of <code>somefile.php</code>:</p>
			<pre><code><?php require(__DIR__ . '/somefile.php'); ?></code></pre>
		</div>
	</body>
</html>
