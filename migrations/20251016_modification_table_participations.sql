-- Il y avait une erreur, le titre ne devrait pas être nullable
ALTER TABLE participations CHANGE id_titre id_titre INT(11) NOT NULL;