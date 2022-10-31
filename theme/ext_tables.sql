CREATE TABLE tx_theme_domain_model_inline_textmedia (
    parent_uid int(11) DEFAULT '0' NOT NULL,
    parent_table varchar(255) DEFAULT '' NOT NULL,
    header varchar(255) DEFAULT '' NOT NULL,
    subheader varchar(255) DEFAULT '' NOT NULL,
    bodytext mediumtext,
    assets int(11) unsigned DEFAULT '0' NOT NULL,
    imageorient tinyint(4) unsigned DEFAULT '0' NOT NULL,
    imagecols tinyint(4) unsigned DEFAULT '0' NOT NULL,
    layout int(11) unsigned DEFAULT '0' NOT NULL,
    frame_class varchar(60) DEFAULT 'default' NOT NULL,
);
CREATE TABLE tt_content (
    inline_textmedia int(11) unsigned DEFAULT '0' NOT NULL,
    content_type varchar(60) DEFAULT 'assets' NOT NULL,
    bodytext2 mediumtext,
    assets2 int(11) unsigned DEFAULT '0' NOT NULL,
    frame_class2 varchar(60) DEFAULT 'default' NOT NULL,
    products text,
    parents text,
    icon varchar(60) DEFAULT '' NOT NULL,
    layout varchar(60) DEFAULT 'default' NOT NULL,
);
