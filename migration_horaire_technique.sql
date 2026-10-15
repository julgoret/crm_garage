-- Migration : ajout du champ horaires techniques (VARCHAR 255)
-- à exécuter une fois sur la base

ALTER TABLE `prospects` ADD `horaire_technique` VARCHAR(255) NOT NULL DEFAULT '' AFTER `horaires`;
