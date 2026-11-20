var zoneImage = document.getElementById('uploadArea');
var champImage = document.getElementById('image');

zoneImage.addEventListener('click', function (event) {
    if (event.target !== champImage) {
        champImage.click();
    }
});

champImage.addEventListener('change', function () {
    var fichier = champImage.files[0];
    var apercu = document.getElementById('imagePreview');
    var plus = document.getElementById('uploadPlus');

    if (!fichier) {
        return;
    }

    if (fichier.size > 2 * 1024 * 1024) {
        alert("L'image ne doit pas dépasser 2 Mo.");
        champImage.value = '';
        return;
    }

    if (fichier.type !== 'image/jpeg' && fichier.type !== 'image/png') {
        alert('Choisissez une image JPG ou PNG.');
        champImage.value = '';
        return;
    }

    apercu.src = URL.createObjectURL(fichier);
    apercu.style.display = 'block';
    plus.style.display = 'none';
});

var formulairesSuppression = document.querySelectorAll('.delete-product-form');
for (var i = 0; i < formulairesSuppression.length; i++) {
    formulairesSuppression[i].addEventListener('submit', function (event) {
        if (!confirm('Confirmer la suppression de ce produit ?')) {
            event.preventDefault();
        }
    });
}
