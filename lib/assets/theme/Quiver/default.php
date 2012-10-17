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
 <h3><?= trim($site['name']) ?></h3>
 </hgroup>
</header>

<nav id="main" class="gfont">
<?= $this->menu_li ?>
</nav>

<article>

<?= $this->content ?>

</article>

<footer class="gfont">
<?= (empty($site['email']))?'':"<div id=\"mail\">$site[email]</div>"; ?>
<div id="theme"><em>MrArrow with the <?= $site['theme'] ?> theme.</em></div>
</footer>

</div> <!-- /page -->

</body>
