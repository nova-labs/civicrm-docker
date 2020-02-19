CREATE TABLE IF NOT EXISTS civirule_rule_tag (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rule_id INT UNSIGNED NULL,
  rule_tag_id INT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC),
  INDEX fk_rule_idx (rule_id ASC),
  CONSTRAINT fk_rule_id
    FOREIGN KEY (rule_id)
    REFERENCES civirule_rule (id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
