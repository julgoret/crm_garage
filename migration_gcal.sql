-- Migration : intégration Google Agenda
-- à exécuter une fois sur la base

ALTER TABLE `prospects` ADD `gcal_event_id` VARCHAR(128) NOT NULL DEFAULT '' AFTER `horaire_technique`;
