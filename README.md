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
- 2. Allez dans  Téléchargement
- 3. Puis cocher l'option : `Toujours demander où enrégistrer les fichiers`

---

## 📝 Guide d’utilisation

### 1. Création de Compte Connexion & 

- Une fois que vous avez accédez à a page , commencez par créer un compte en entrant vos infotmations.

![Connexion](assets/img_readme/Creation_compte.png)

- Ensuite , vous recevrai un email de confirmation qui rediregera directement vers le tableau de bord comme çi dessous : 

![Connexion](assets/img_readme/tableau_de_bord.png)

- Si vous avez déjà un compte Connectez-vous directement  avec vos identifiants de connexion (Vous pouvez cliquez surle bouton `Se souvenir de moi` pour pour ne plus à aoir à entrer vos identifiants à chaque fois).

![Connexion](assets/img_readme/connexion.png)

### 2. Création et gestion  d’une activité

- Cliquez sur “Créer une activité”.
- Remplissez le formulaire (nom, description, période, centre, etc.).
- Validez pour enregistrer l’activité.

![Créer activité](assets/img_readme/creer_activite.png)

- Après avoir créer l'activité , vous pouvez voir les informations relatives à l'activité comme çi-après : 

![Créer activité](assets/img_readme/gestion_activite.png)

- Vous pouvez cliquez sur `Autre action` pour `Supprimer` l'activité , `Générer les documents` si des participants y sont déjà associés ou `Editer l'en tête des documents` .
- Vous pouvez également modifier les informations d'une activité déjà créer en cliquant sur `Modifier les informations` .
- Toutes les activités crées sont visibles en cliquant sur `Activité` puis `Vos Activités` de la barre d'outil à gauche : 

![Créer activité](assets/img_readme/vos_activite.png)

- Vous pouvez chercher vos activité dans la barre de recherche et cliquer sur le bouton `Gerer` pour effectuer vos actions.

### 3. Création et gestion des acteurs

- Accédez à l’activité créée.
- Cliquez sur “Associer des acteurs” ou “Ajouter un participant”.
- Sélectionnez les acteurs que vous désirer associer à l'activité. Si vous n'avez encore créer aucun acteur , cliquer sur sur le bouton `Ajouter un acteur de la page çi après` : 

![Ajouter participant](assets/img_readme/ajouter_participant.png)

- Si vous déjà créer des acteurs , sélectionnez les acteurs sur la page qui s'affiche : 

![Ajouter participant](assets/img_readme/selectionner_participant.png)

- Remplissez les informations requises (nom, titre, coordonnées bancaires, etc.).

![Ajouter participant](assets/img_readme/associer_participant.png)

- Si vous finissez d'entrer les informations , cliquez sur le bouton `Relier la liaison` .


- Vous pouvez voir les participants associés à une activité en dessous comme çi après : 

![Ajouter participant](assets/img_readme/participant_activite.png)

- Vous pour rechercher un participant en tapant son nom dans la barre de recherche en haut à droite , cliquer ensuite sur `Modifier` pour modifier les informations qui lient le participant à l'activité ou  `rompre la liaison` pour retirer le participant de l'activité.

- Lorsque vous cliquer sur le bouton `Gérer le participant` , la page çi-après s'affiche : 

![Ajouter participant](assets/img_readme/gerer_participant.png)

- Cliquez sur `Modifier les informations` pour modifier les informations personnelles d'un partcipant ou `Autres actions` pour l'associer à une autre activité ou lui ajouter un compte bancaire.

### 5. Génération des documents

- Une fois que vous avez créer votre activité et ajouter des acteurs , c'est le moments de générer les différents documents.
- Dans la barre de recherche cliquez sur `Activité` puis `Vos activité` .
- Choisisez votre activité ou taper le nom de l'activité dans la barre de recherche pour aller vite.
- Cliquez sur `Gérer`
- Vous verrez en haut à droite un bouton `Générer document` .
- Editer l'en tête de votre document sur la page çi après : 

![Ajouter participant](assets/img_readme/editer_entete.png)

- Ensuite choisisez les documents que vous voulez générer puis cliquez sur continuer.
- Vous avez la possibilité de les générer en fichier zipper ou en un seul fichier non zipper.



### 5. Gestion du compte utilisateur

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

- Ifè Léonce Sokey Amour COMLAN  — ifeleoncecomlan@gmail.com
- Olowun-Tobi MONSI — onellemonsiotojisca@gmail.com
- Espéro AKANDO — esperoakando@gmail.com
- Judicael GBAGUIDI — gbaguidijudicael520@gmail.com

*N’hésitez pas à nous contacter pour toute question ou suggestion !*

---

## 🙋‍♂️ Support

Pour toute question ou suggestion, ouvrez une issue sur le dépôt GitHub ou contactez l’équipe de développement.
