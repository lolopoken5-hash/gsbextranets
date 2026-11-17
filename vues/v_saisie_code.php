<div class="page-content container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-wrapper">
                <div class="box">
                    <div class="content-wrap">
                        <legend>Vérification en deux étapes</legend>
                        <p>Un code à 6 chiffres a été envoyé par e-mail. Il est valable une minute.</p>
                        <?php if (isset($erreurCode)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erreurCode, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <form method="post" action="index.php?uc=connexion&amp;action=valideCode">
                            <input name="code2FA" class="form-control" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="Code à 6 chiffres" required>
                            <br>
                            <input type="submit" class="btn btn-primary signup" value="Vérifier le code">
                        </form>
                        <a href="index.php?uc=connexion&amp;action=saisieCode">Renvoyer un nouveau code</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
