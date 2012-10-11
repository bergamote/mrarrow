<!doctype html>
<head>
	<meta charset="UTF-8">
	<title><?= $page->title ?> --- <?= $site['name'] ?></title>
</head>

<body>

	<h1>
		<?= $site['name'] ?>
	</h1>

	<nav>
		<?= $page->menu_li ?>
	</nav>

	<article>
		<?= $page->content ?>
	</article>

</body>

