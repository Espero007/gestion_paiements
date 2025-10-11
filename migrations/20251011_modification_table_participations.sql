-- Pour mieux gérer l'intégration de l'ancien mode de payement, il faut rendre certains champs nullable dans la table participations et c'est ce que ce script fera essentiellement

ALTER TABLE participations CHANGE id_titre id_titre INT NULL;
ALTER TABLE participations CHANGE id_compte_bancaire id_compte_bancaire INT NULL;