-- Augmentation de la taille du champ "nom" au niveau de la table liée aux activités

ALTER TABLE activites CHANGE nom nom VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT 
NULL;