<?php


if(!isset($_GET['action'])){
	$_GET['action'] = 'demandeConnexion';
}
$action = $_GET['action'];
switch($action){
	
	case 'demandeConnexion':{
		include("vues/v_connexion.php");
		break;
	}
	case 'valideConnexion':{
		$login = $_POST['login'] ?? '';
		$mdp = $_POST['mdp'] ?? '';
		$connexionOk = is_string($login)
			&& is_string($mdp)
			&& $pdo->checkUser($login,$mdp);
		if(!$connexionOk){
			ajouterErreur("Login ou mot de passe incorrect");
			include("vues/v_erreurs.php");
			include("vues/v_connexion.php");
		}
		else { 
			$infosMedecin = $pdo->donneLeMedecinByMail($login);
			if (!is_array($infosMedecin)) {
				ajouterErreur("Login ou mot de passe incorrect");
				include("vues/v_erreurs.php");
				include("vues/v_connexion.php");
				break;
			}

			$_SESSION['medecinEnAttente2FA'] = [
				'id' => (int) $infosMedecin['id'],
				'nom' => $infosMedecin['nom'],
				'prenom' => $infosMedecin['prenom'],
				'mail' => $infosMedecin['mail']
			];
			$action = 'saisieCode';
		}

		break;
	}
	case 'saisieCode':
		break;
	case 'valideCode': {
		$medecinEnAttente = $_SESSION['medecinEnAttente2FA'] ?? null;
		$code = $_POST['code2FA'] ?? '';
		if (
			!is_array($medecinEnAttente)
			|| !isset($medecinEnAttente['id'], $medecinEnAttente['nom'], $medecinEnAttente['prenom'])
			|| !is_string($code)
			|| !preg_match('/^\d{6}$/', $code)
			|| !$pdo->verifieEtEffaceCode2FA($medecinEnAttente['id'], $code)
		) {
			$erreurCode = "Le code est invalide ou a expiré. Demandez un nouveau code.";
			include("vues/v_saisie_code.php");
			break;
		}

		session_regenerate_id(true);
		connecter(
			$medecinEnAttente['id'],
			$medecinEnAttente['nom'],
			$medecinEnAttente['prenom']
		);
		unset($_SESSION['medecinEnAttente2FA']);
		include("vues/v_sommaire.php");
		break;
	}

	default :{
		include("vues/v_connexion.php");
		break;
	}
}

if ($action === 'saisieCode') {
	$medecinEnAttente = $_SESSION['medecinEnAttente2FA'] ?? null;
	if (
		!is_array($medecinEnAttente)
		|| !isset($medecinEnAttente['id'], $medecinEnAttente['mail'])
	) {
		ajouterErreur("Veuillez d'abord saisir votre login et votre mot de passe.");
		include("vues/v_erreurs.php");
		include("vues/v_connexion.php");
	} else {
		$code = genererCode2FA();
		if (!$pdo->enregistreCode2FA($medecinEnAttente['id'], $code)) {
			unset($_SESSION['medecinEnAttente2FA']);
			ajouterErreur("Impossible de préparer le code de vérification.");
			include("vues/v_erreurs.php");
			include("vues/v_connexion.php");
		} elseif (!envoyerCode2FA($medecinEnAttente['mail'], $code)) {
			$codeEfface = $pdo->effaceCode2FA($medecinEnAttente['id']);
			unset($_SESSION['medecinEnAttente2FA']);
			$messageErreur = "L'envoi du code par e-mail a échoué. Veuillez réessayer.";
			if (!$codeEfface) {
				$messageErreur .= " Le code temporaire n'a pas pu être effacé.";
			}
			ajouterErreur($messageErreur);
			include("vues/v_erreurs.php");
			include("vues/v_connexion.php");
		} else {
			include("vues/v_saisie_code.php");
		}
	}
}
?>