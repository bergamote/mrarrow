<!doctype html>
<head>
	<meta charset="UTF-8">
	<title><?= $this->title ?> | <?= $site['name'] ?></title>
	<link rel="stylesheet" href="<?= $this->rel ?>style.css">
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="<?= $this->rel ?>script.js"></script>
</head>

<body>
<div id="page">

<header>
 <hgroup>
 <h3><?= $site['name'] ?></h3>
 <!--<h2><?= $site['tagline']?></h2>-->
 </hgroup>
</header>

<nav id="main">
<?= $this->menu_li ?>
</nav>

<article>

<?= $this->content ?>

</article>

<footer>
<?= (empty($site['email']))?'':"<div id=\"mail\">$site[email]</div>"; ?>
<div id="theme"><em>Mr Arrow with the <?= $site['theme'] ?> theme.</em></div>
</footer>

</div> <!-- /page -->

</body>
