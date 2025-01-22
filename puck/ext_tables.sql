CREATE TABLE tt_content (
    container_width int DEFAULT '0' NOT NULL,
    container_offset int DEFAULT '0' NOT NULL,
    text_column_width int DEFAULT '0' NOT NULL,
    media_column_width int DEFAULT '0' NOT NULL,
    item_column_width int DEFAULT '0' NOT NULL,
);

CREATE TABLE pages (
    doktype int DEFAULT '0' NOT NULL,
);

CREATE TABLE tx_formal_field (
    step_parent int UNSIGNED DEFAULT '0' NOT NULL,
    field_parent int UNSIGNED DEFAULT '0' NOT NULL,
    default_value varchar(255) DEFAULT NULL,
    min varchar(255) DEFAULT NULL,
    max varchar(255) DEFAULT NULL,
);

CREATE TABLE tx_formal_field_option (
    field_parent int UNSIGNED DEFAULT '0' NOT NULL,
);

CREATE TABLE tx_formal_step (
    form_parent int UNSIGNED DEFAULT '0' NOT NULL,
);

CREATE TABLE tx_formal_form_submission (
     form int UNSIGNED DEFAULT '0' NOT NULL,
     plugin int UNSIGNED DEFAULT '0' NOT NULL,
);