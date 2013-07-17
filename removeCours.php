<?php
	$url = urlencode('http://e-uapv2013.univ-avignon.fr/creation_cours/listes2.php');
	require_once('CAS.php');
	$cas_host = 'cas.univ-avignon.fr';
	$cas_port = 443;
	$cas_context = '';
	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	phpCAS::setNoCasServerValidation();

	if(phpCAS::isAuthenticated())
		$uid = PHPCas::getUser();
?>

<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script type="text/javascript" src="fonctions.js"></script>
</head>
<body>

<?php
	$cours = array();
	$contenu = file('coursExistant.csv');
	$i = 0;

	foreach ($contenu as $ligne)
	{
		$tl= explode(';',$ligne);
		//On récupère les cours associés au user connecté
		if ($tl[3] == $uid)
		{
			$cours[$i]['nom'] = $tl[0];
			$cours[$i]['id'] = $tl[1];
			$i++;
		}
	}
?>

<div id='blocPrincipal'>
    <div id="blocHeader">
        <div id='header'></div>
        <h1>Désinscription d'un espace de cours</h1>
        <div id='toolbar'>
            <p>Créer mon espace de cours sur la plateforme e-uapv</p>
        </div>
    </div>
    <form name="formu" class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="cours">Choisir un cours</label>
            <div class="controls">
                <select name="cours" id="cours" class="span4">
                    <option value="" selected> ---- </option>
									<?php
										foreach($cours as $c)
											echo "<option value='".$c['id']."'>".$c['nom']."</option>";
									?>
                </select>
            </div>
        </div>
        <div class='right'>
					<img src='ajax-loader.gif' id='loading' />
					<button onclick="removeCourse()" class="btn btn-info" id='supprimer' type="button">Me supprimer du cours</button>
				</div>
		</form>
    <input type="hidden" id="auteurUid" value="<?php echo $uid; ?>" />
</body>