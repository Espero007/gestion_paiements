# GestionPaiementActeurs

**GestionPaiementActeurs** est une plateforme web PHP permettant de gérer facilement les activités, les participants et les paiements associés. Elle s’adresse aux organisateurs d’événements, responsables administratifs ou toute structure ayant besoin de suivre et d’automatiser la gestion des paiements.

---

## 🚀 Fonctionnalités principales

- **Création et gestion des activités** : Ajoutez, modifiez et supprimez des activités.
    * Chaque activité peut contenir des informations détaillées (nom, description, période, centre, la note génératrice , le timbre , les titres associés, les différents responsables et leur titres etc.).
    * Nous avons plusieurs types d'activités :
      - **Activité de type 1** : En plus des informations çi dessus , elle prends en compte le taux journalier
      - **Activité de type 2** : Ce type type d'activité contrairement au précédent , associe à chaque titre une indemnité forfaitaire.nous avons aussi la possibilité de renseigner le taux journalier
      - **Activité de type 3** : Ce type d'activité , exclue le taux journalier , mais prends en compte le taux par tâche , les frais de déplacements journaliers et associe bien sur à chaque titre une indemnité forfaitaire.

- **Gestion des participants** : Ajoutez des participants , renseignez leurs informations et associez-les à des activités. Vous avez également la posssibilité  d'ajouter des comptes bancaires aux participants (au plus 3 comptes bancaires par participant).
- **Saisie et suivi des paiements** : Enregistrez les paiements, visualisez l’état des paiements par activité ou participant.
- **Historique et statistiques** : Consultez l’historique des paiements et des activités, générez des rapports.
- **Authentification sécurisée** : Accès protégé par compte utilisateur.
- **Export des données** : Génération de documents PDF (états de paiement, ordres de virement, attestations,note de service , Liste des Ribs des participants etc.).
- **Gestion des rôles et titres** : Attribuez des rôles/titres aux participants selon l’activité.

---

## 🔧 Prérequis

- PHP 8.1 ou plus
- MySQL / MariaDB
- Apache ou Nginx
- Extensions PHP : `pdo`, `mbstring`, `intl`
- Xampp , Lammp ou tout autre serveur web compatible PHP

---

## ⚙️ Installation

### Sous Windows (XAMPP)

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Espero007/gestion_paiements.git
   cd gestion_paiements
   ```


2. **Configurer le serveur web**
   - Place le projet dans le dossier `htdocs` de XAMPP.
   - Vérifie que `mod_rewrite` est activé dans Apache.

6. **Accéder à l’application**
   - Dans votre navigateur, allez à [http://localhost/gestion_paiements/](http://localhost/gestion_paiements/).

---

### Sous Linux (XAMPP)

1. **Installer XAMPP**  
   Télécharger et installer XAMPP depuis [apachefriends.org](https://www.apachefriends.org/fr/index.html).

2. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Espero007/gestion_paiements.git
   cd gestion_paiements
   ```

2. **Démarrer les services Apache et MySQL**
   - Dans un terminal, lancez :
     ```bash
     sudo /opt/lampp/lampp start
     ```
   - Pour arrêter les services :
     ```bash
     sudo /opt/lampp/lampp stop
     ```

3. **Placer le projet dans le dossier web**
   - Téléchargez ou clonez le dossier du projet `gestion_paiements`.

        ```bash
        git clone https://github.com/Espero007/gestion_paiements.git
        cd gestion_paiements
        ```

   - Copiez ce dossier dans `gestion_paiements` `/opt/lampp/htdocs/` :
     ```bash
     sudo cp -r gestion_paiements /opt/lampp/htdocs/
     ```

4. **Créer la base de données via phpMyAdmin**
   - Dans votre navigateur, allez à [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Cliquez sur “Nouvelle base de données”, donnez-lui un nom (ex: `gestion_paiements`), puis validez.
   - Si un fichier `database.sql` existe, importez-le via l’onglet “Importer”.

5. **Configurer la connexion à la base de données**
   - Ouvrez le fichier `includes/bdd.php` dans le projet.
   - Renseignez les informations de connexion (nom de la base, utilisateur, mot de passe).

6. **Accéder à l’application**
   - Dans votre navigateur, allez à [http://localhost/gestion_paiements/](http://localhost/gestion_paiements/).

---

## 📝 Guide d’utilisation

> **Astuce :** Des captures d’écran sont insérées pour illustrer chaque étape.  
> *(Remplacez les chemins d’images par vos propres captures si besoin)*

### 1. Connexion & création de compte

- Rendez-vous sur la page de connexion.
- Créez un compte si vous n’en avez pas.
- Connectez-vous avec vos identifiants.

![Connexion](assets/img/connexion.png)

### 2. Création d’une activité

- Cliquez sur “Créer une activité”.
- Remplissez le formulaire (nom, description, période, centre, etc.).
- Validez pour enregistrer l’activité.

![Créer activité](assets/img/creer_activite.png)

### 3. Ajout de participants

- Accédez à l’activité créée.
- Cliquez sur “Associer des acteurs” ou “Ajouter un participant”.
- Remplissez les informations requises (nom, titre, coordonnées bancaires, etc.).
- Enregistrez.

![Ajouter participant](assets/img/ajouter_participant.png)

### 4. Gestion du compte utilisateur

- Une fois connecté, vous pouvez modifier vos informations personnelles depuis votre espace utilisateur en accédant à paramètre dans la barre des tâches.
- Vous pouvez changer votre mot de passe, mettre à jour vos informations de contact, etc.
- Vous avez également la possibilité de désactiver votre compte.

![Profil utilisateur](assets/img/profil_utilisateur.png)

---

## 🗂️ Structure du projet

- `gestion_activites/` : gestion des activités, création, édition, génération de documents.
- `gestion_participants/` : gestion des participants, liaisons, informations bancaires.
- `includes/` : fichiers utilitaires, connexion BDD, constantes, fonctions communes.
- `assets/` : ressources statiques (CSS, JS, images).
- `auth/` : gestion de l’authentification.
- `pdfs_temp/` : stockage temporaire des PDF générés.
- `PHPMailer/`, `tcpdf/` : librairies tierces pour l’envoi de mails et la génération de PDF.

---

## 💡 Conseils & bonnes pratiques

- **Sauvegardez régulièrement la base de données.**
- **Ne partagez pas vos identifiants de connexion.**
- **Vérifiez les droits d’écriture sur les dossiers d’upload et de génération de PDF.**
- **Pour toute question, consultez la documentation ou contactez l’administrateur.**

---

## 👨‍💻 Auteurs

- Ifè Léonce COMLAN  — ifeleoncecomlan@email.com
- Olowun-Tobi MONSI — olowun-tobi.monsi@email.com
- Espéro AKANDO — espero.akando@email.com
- Judicael GBAGUIDI — judicael.gbaguidi@email.com

*N’hésitez pas à nous contacter pour toute question ou suggestion !*

---

## 🙋‍♂️ Support

Pour toute question ou suggestion, ouvrez une issue sur le dépôt GitHub ou contactez l’équipe de développement.
