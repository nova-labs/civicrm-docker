CREATE TABLE IF NOT EXISTS civirule_pre210_rule (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NULL,
    label VARCHAR(128) NULL,
    trigger_params TEXT NULL,
    is_active TINYINT NULL DEFAULT 1,
    description VARCHAR(256) NULL,
    help_text TEXT NULL,
    PRIMARY KEY (id))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS civirule_pre210_rule_condition (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rule_id INT UNSIGNED NULL,
    condition_link VARCHAR(3) NULL,
    condition_id INT UNSIGNED NULL,
    condition_params TEXT NULL,
    is_active TINYINT NULL DEFAULT 1,
    PRIMARY KEY (id))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS civirule_pre210_rule_action (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rule_id INT UNSIGNED NULL,
    action_id INT UNSIGNED NULL,
    action_params TEXT NULL,
    delay TEXT NULL,
    ignore_condition_with_delay TINYINT NULL default 0,
    is_active TINYINT NULL DEFAULT 1,
    PRIMARY KEY (id))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;
