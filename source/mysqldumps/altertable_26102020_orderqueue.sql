
ALTER TABLE orderqueue ADD COLUMN orq_effectiveon DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER orq_queuetype