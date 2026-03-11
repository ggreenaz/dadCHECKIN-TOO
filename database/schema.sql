-- =============================================================
-- CheckIn Platform — Multi-Tenant Schema
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- organizations
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS organizations (
    organization_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255)  NOT NULL,
    slug            VARCHAR(100)  NOT NULL UNIQUE,
    timezone        VARCHAR(100)  NOT NULL DEFAULT 'UTC',
    settings        JSON          NULL COMMENT 'Arbitrary org-level config',
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- locations
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS locations (
    location_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    address         VARCHAR(500) NULL,
    settings        JSON         NULL,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_locations_org FOREIGN KEY (organization_id)
        REFERENCES organizations (organization_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- departments
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    department_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    description     VARCHAR(500) NULL,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    sort_order      SMALLINT    NOT NULL DEFAULT 0,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dept_org_name (organization_id, name),
    CONSTRAINT fk_departments_org FOREIGN KEY (organization_id)
        REFERENCES organizations (organization_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- hosts
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hosts (
    host_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id     INT UNSIGNED NULL COMMENT 'NULL = available at all locations',
    department_id   INT UNSIGNED NULL,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NULL,
    phone           VARCHAR(30)  NULL,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hosts_org    FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE,
    CONSTRAINT fk_hosts_loc    FOREIGN KEY (location_id)     REFERENCES locations     (location_id)     ON DELETE SET NULL,
    CONSTRAINT fk_hosts_dept   FOREIGN KEY (department_id)   REFERENCES departments   (department_id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- visit_reasons
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS visit_reasons (
    reason_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id  INT UNSIGNED NOT NULL,
    location_id      INT UNSIGNED NULL COMMENT 'NULL = org-wide',
    label            VARCHAR(255) NOT NULL,
    requires_approval TINYINT(1) NOT NULL DEFAULT 0,
    duration_limit   SMALLINT UNSIGNED NULL COMMENT 'Max visit minutes, NULL means unlimited',
    active           TINYINT(1)  NOT NULL DEFAULT 1,
    sort_order       SMALLINT    NOT NULL DEFAULT 0,
    created_at       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reasons_org      FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE,
    CONSTRAINT fk_reasons_location FOREIGN KEY (location_id)     REFERENCES locations     (location_id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- visitors
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS visitors (
    visitor_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(100) NOT NULL,
    last_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(255) NULL,
    phone        VARCHAR(30)  NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_visitor_phone (phone),
    UNIQUE KEY uq_visitor_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- visits
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS visits (
    visit_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_id      INT UNSIGNED NOT NULL,
    organization_id INT UNSIGNED NOT NULL,
    location_id     INT UNSIGNED NULL,
    host_id         INT UNSIGNED NULL,
    reason_id       INT UNSIGNED NULL,
    status          ENUM('waiting','checked_in','with_host','completed','auto_completed','no_show','cancelled')
                    NOT NULL DEFAULT 'checked_in',
    check_in_time   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    check_out_time  DATETIME     NULL,
    notes           TEXT         NULL,
    legacy_id       INT UNSIGNED NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_legacy_id (legacy_id),
    CONSTRAINT fk_visits_visitor  FOREIGN KEY (visitor_id)      REFERENCES visitors      (visitor_id)      ON DELETE RESTRICT,
    CONSTRAINT fk_visits_org      FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE,
    CONSTRAINT fk_visits_location FOREIGN KEY (location_id)     REFERENCES locations     (location_id)     ON DELETE SET NULL,
    CONSTRAINT fk_visits_host     FOREIGN KEY (host_id)         REFERENCES hosts         (host_id)         ON DELETE SET NULL,
    CONSTRAINT fk_visits_reason   FOREIGN KEY (reason_id)       REFERENCES visit_reasons (reason_id)       ON DELETE SET NULL,
    INDEX idx_visits_checkin   (check_in_time),
    INDEX idx_visits_org_date  (organization_id, check_in_time),
    INDEX idx_visits_status    (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    role            ENUM('super_admin','org_admin','location_admin','staff') NOT NULL DEFAULT 'staff',
    auth_provider   ENUM('local','google','microsoft','ldap') NOT NULL DEFAULT 'local',
    password_hash   VARCHAR(255) NULL,
    google_id       VARCHAR(255) NULL,
    microsoft_id    VARCHAR(255) NULL,
    ldap_dn         VARCHAR(500) NULL,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    last_login      DATETIME    NULL,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_email_org (email, organization_id),
    CONSTRAINT fk_users_org FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- custom_fields
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS custom_fields (
    field_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id     INT UNSIGNED NULL COMMENT 'NULL = org-wide',
    label           VARCHAR(255) NOT NULL,
    field_type      ENUM('text','textarea','select','checkbox','date') NOT NULL DEFAULT 'text',
    required        TINYINT(1)  NOT NULL DEFAULT 0,
    options         JSON        NULL COMMENT 'Array of strings for select fields',
    sort_order      SMALLINT    NOT NULL DEFAULT 0,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fields_org      FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE,
    CONSTRAINT fk_fields_location FOREIGN KEY (location_id)     REFERENCES locations     (location_id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- custom_field_values
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS custom_field_values (
    value_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id   INT UNSIGNED NOT NULL,
    field_id   INT UNSIGNED NOT NULL,
    value      TEXT         NULL,
    UNIQUE KEY uq_visit_field (visit_id, field_id),
    CONSTRAINT fk_cfv_visit  FOREIGN KEY (visit_id)  REFERENCES visits        (visit_id)  ON DELETE CASCADE,
    CONSTRAINT fk_cfv_field  FOREIGN KEY (field_id)  REFERENCES custom_fields (field_id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- notification_rules
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notification_rules (
    rule_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id     INT UNSIGNED NULL,
    reason_id       INT UNSIGNED NULL COMMENT 'NULL = all reasons',
    trigger_event   ENUM('check_in','check_out','waiting_limit') NOT NULL,
    channel         ENUM('email','sms','slack','webhook')        NOT NULL,
    recipient_type  ENUM('host','fixed_address','role')          NOT NULL,
    recipient_value VARCHAR(500) NULL,
    active          TINYINT(1)  NOT NULL DEFAULT 1,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_org      FOREIGN KEY (organization_id) REFERENCES organizations (organization_id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_location FOREIGN KEY (location_id)     REFERENCES locations     (location_id)     ON DELETE SET NULL,
    CONSTRAINT fk_notif_reason   FOREIGN KEY (reason_id)       REFERENCES visit_reasons (reason_id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
