-- Table pl_plans : plans journaliers (préfixe pl_) pour l'éditeur plan.php
-- à exécuter une fois sur la base (legaragemoderne3)

CREATE TABLE IF NOT EXISTS `pl_plans` (
  `date_plan` DATE NOT NULL PRIMARY KEY,
  `zones` MEDIUMTEXT NOT NULL,
  `time_modif` INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
