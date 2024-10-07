ALTER TABLE mytransfergold ADD COLUMN gtb_isnotifyrecipient smallint(6) DEFAULT 0 COMMENT 'For Checking If Notification Has Been Sent Out' AFTER gtb_expireon;

/* update existing tier data */
/* update tier 1 */
UPDATE mytransfergold 
SET gtb_isnotifyrecipient = 0
WHERE gtb_isnotifyrecipient = null;
