-- Application table only. Laravel migrations are the canonical schema.
CREATE TABLE leads (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    phone VARCHAR(32) NULL,
    email VARCHAR(255) NULL,
    city VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    utm_campaign VARCHAR(255) NULL,
    product VARCHAR(255) NULL,
    budget_uah DECIMAL(14,2) NULL,
    status VARCHAR(64) NULL,
    manager VARCHAR(255) NULL,
    comment TEXT NULL,
    next_contact_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
