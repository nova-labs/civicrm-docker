CREATE TABLE IF NOT EXISTS civirule_rule_action (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rule_id INT UNSIGNED NULL,
  action_id INT UNSIGNED NULL,
  action_params TEXT NULL,
  delay TEXT NULL,
  ignore_condition_with_delay TINYINT NULL default 0,
  is_active TINYINT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC),
  INDEX fk_ra_rule_idx (rule_id ASC),
  INDEX fk_ra_action_idx (action_id ASC),
  CONSTRAINT fk_ra_rule
    FOREIGN KEY (rule_id)
    REFERENCES civirule_rule (id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT,
  CONSTRAINT fk_ra_action
    FOREIGN KEY (action_id)
    REFERENCES civirule_action (id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
