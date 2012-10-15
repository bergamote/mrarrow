<!doctype html>
<head>
	<meta charset="UTF-8">
	<title><?= $this->title ?> --- <?= $site['name'] ?></title>
</head>

<body>

	<h1>
		<?= $site['name'] ?>
	</h1>

	<nav>
		<?= $this->menu_li ?>
	</nav>

	<article>
		<?= $this->content ?>
	</article>

</body>

