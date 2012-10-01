<!doctype html>
<head>
	<meta charset="UTF-8">
	<title><?= $page->title ?> --- <?= $site['name'] ?></title>
	<link type="text/css" rel="stylesheet" href="<?= $this->style ?>">
	<!--<script src="http://code.jquery.com/jquery-latest.min.js"></script>-->

</head>

<body>
<div id="page">

<header>
 <hgroup>
 <h1><?= $site['name'] ?></h1>
 <h2><?= $site['tagline']?></h2>
 </hgroup>
</header>

<nav id="main">
<?= $page->menu_li ?>
</nav>

<article>
<h1><?= $page->title ?></h1>

<?= $page->content ?>

</article>

<footer>
<p>Contact me: <?= $site['email'] ?>
<em>Mr Arrow with the <?= $site['theme'] ?> theme.</em>
</footer>

</div> <!-- /page -->

</body>
