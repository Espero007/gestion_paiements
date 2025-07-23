# GPaiements

**GPaiement** est une plateforme web PHP permettant de gérer facilement les activités, les acteurs et les paiements associés. Elle s’adresse aux organisateurs d’événements, responsables administratifs ou toute structure ayant besoin de suivre et d’automatiser la gestion des paiements.

---

## 🚀 Fonctionnalités principales

- **Création et gestion des activités** : Ajoutez, modifiez et supprimez des activités.
    * Chaque activité peut contenir des informations détaillées (nom, description, période, centre, la note génératrice , le timbre , les titres associés, les différents responsables et leur titres etc.).
    * Nous avons plusieurs types d'activités :
      - **Activité de type 1** : En plus des informations çi dessus, elle prend en compte le taux journalier.
      - **Activité de type 2** : Ce type d'activité en plus du taux journalier, associe à chaque titre une indemnité forfaitaire.
      - **Activité de type 3** : Ce type d'activité, exclue le taux journalier, mais prend en compte le taux par tâches, les frais de déplacements journaliers et associe à chaque titre une indemnité forfaitaire.

- **Gestion des participants** : Ajoutez des participants, renseignez leurs informations et associez-les à des activités. Vous avez également la posssibilité  d'ajouter des comptes bancaires aux participants (au plus 3 comptes bancaires par participant).
- **Authentification sécurisée** : Accès protégé par compte utilisateur.
- **Export des données** : Génération de documents PDF (états de paiement, ordres de virement, attestations, note de service, liste des Relevés d'Identité Bancaire(RIB) des participants etc.).
- **Gestion des rôles et titres** : Attribuez des rôles/titres aux acteurs selon l’activité.

---

## 🔧 Prérequis

- PHP 8.1 ou plus
- MySQL / MariaDB
- Apache ou Nginx
- Extensions PHP : `pdo`, `mbstring`, `intl`, `zip` 
- Xampp

---

## ⚙️ Installation sous Windows

### 1. Télécharger XAMPP

- Rendez-vous sur le site officiel : [https://www.apachefriends.org/fr/index.html](https://www.apachefriends.org/fr/index.html)
- Cliquez sur “Télécharger” pour la version Windows.
- Une fois le fichier téléchargé (`xampp-windows-x64-xx.x.x-x-installer.exe`), double-cliquez dessus pour lancer l’installation.

### 2. Installer XAMPP

- Lors de l’installation, laissez les options par défaut (Apache, MySQL, PHP, phpMyAdmin, etc.).
- Choisissez le dossier d’installation (par défaut : `C:\xampp`).
- Terminez l’installation et lancez le panneau de contrôle XAMPP.
- Laissez les paramètres de xampp par défaut tels quels.

### 3. Démarrer les services nécessaires

- Ouvrez le panneau de contrôle XAMPP (`xampp-control.exe`).
- Cliquez sur “Start” pour **Apache** et **MySQL**.
- Vérifiez que les deux services sont bien en vert.

### 4. Télécharger le projet

- Téléchargez le projet depuis le dépôt github. Accédez au dépôt GitHub : en cliquant sur le lien suivant : [https://github.com/Espero007/gestion_paiements.git](https://github.com/Espero007/gestion_paiements.git)
  

### 5. Lancer le serveur interne PHP

- Ouvrez une invite de commandes (cmd) ou PowerShell.
- Placez-vous dans le dossier dézippé du projet :
  ```bash
  cd C:\chemin_vers_votre_dossier\
  ```
- Lancez le serveur interne PHP sur le port de votre choix tout en veillant à ce que ce port ne soit pas occupé par une autre application (exemple : 8000)  :
  ```bash
  php -S localhost:port
  ```
- L’application sera accessible à l’adresse [http://localhost:port](http://localhost:port)
   - Exemple : Pour le port 8000 vous aurez accès à l'application via l'adresse  [http://localhost:8000](http://localhost:8000) 
### 6. Configurez votre navigateur
- 1. Allez dans les paramètres de votre navigateurs
- 2. Allez dans 

---

## 📝 Guide d’utilisation

> **Astuce :** Des captures d’écran sont insérées pour illustrer chaque étape.  
> *(Remplacez les chemins d’images par vos propres captures si besoin)*

### 1. Connexion & création de compte

- Rendez-vous sur la page de connexion.
- Créez un compte si vous n’en avez pas.
- Connectez-vous avec vos identifiants.

![Connexion](assets/img_readme/connexion.png)

### 2. Création d’une activité

- Cliquez sur “Créer une activité”.
- Remplissez le formulaire (nom, description, période, centre, etc.).
- Validez pour enregistrer l’activité.

![Créer activité](assets/img_readme/creer_activite.png)

### 3. Ajout de participants

- Accédez à l’activité créée.
- Cliquez sur “Associer des acteurs” ou “Ajouter un participant”.
- Remplissez les informations requises (nom, titre, coordonnées bancaires, etc.).
- Enregistrez.

![Ajouter participant](assets/img_readme/ajouter_participant.png)

### 4. Gestion du compte utilisateur

- Une fois connecté, vous pouvez modifier vos informations personnelles depuis votre espace utilisateur en accédant à paramètre dans la barre des tâches.
- Vous pouvez changer votre mot de passe, mettre à jour vos informations de contact, etc.
- Vous avez également la possibilité de désactiver votre compte.

![Profil utilisateur](assets/img_readme/profil_utilisateur.png)

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

- Ifè Léonce Sokey Amour COMLAN  — ifeleoncecomlan@email.com
- Olowun-Tobi MONSI — onellemonsiotojisca@email.com
- Espéro AKANDO — esperoakando@email.com
- Judicael GBAGUIDI — judicael.gbaguidi@email.com

*N’hésitez pas à nous contacter pour toute question ou suggestion !*

---

## 🙋‍♂️ Support

Pour toute question ou suggestion, ouvrez une issue sur le dépôt GitHub ou contactez l’équipe de développement.
