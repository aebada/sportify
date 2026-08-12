-- Sportify potential partners CRM
-- Run via: php sportify migrate   (schema.php) or apply manually on MySQL.

CREATE TABLE IF NOT EXISTS partner_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    type VARCHAR(40) NOT NULL DEFAULT 'media',
    subtype VARCHAR(80) NULL,
    country VARCHAR(8) NULL,
    city VARCHAR(80) NULL,
    league VARCHAR(120) NULL,
    website VARCHAR(255) NULL,
    email VARCHAR(190) NULL,
    email_confidence VARCHAR(30) NOT NULL DEFAULT 'needs_research',
    source_url VARCHAR(500) NULL,
    invite_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    invited_at DATETIME NULL,
    accepted_at DATETIME NULL,
    notes TEXT NULL,
    tags JSON NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_partner_type (type),
    INDEX idx_partner_invite (invite_status),
    INDEX idx_partner_email (email),
    INDEX idx_partner_country (country)
);

CREATE TABLE IF NOT EXISTS partner_invite_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partner_id INT NOT NULL,
    campaign VARCHAR(120) NOT NULL DEFAULT 'official_partner',
    from_address VARCHAR(190) NOT NULL,
    to_address VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'queued',
    error_message TEXT NULL,
    meta JSON NULL,
    created_at DATETIME NULL,
    INDEX idx_invite_partner (partner_id),
    INDEX idx_invite_status (status)
);
