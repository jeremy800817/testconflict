ALTER TABLE priceadjuster ADD COLUMN paj_tier smallint(6) COMMENT 'For Price Tier' AFTER paj_effectiveendon;

/* update existing tier data */
/* update tier 1 */
UPDATE priceadjuster 
SET paj_tier = 0
WHERE CAST(paj_effectiveon AS TIME(0)) = '23:00:00';

/* update tier 2 */
UPDATE priceadjuster 
SET paj_tier = 1 
WHERE CAST(paj_effectiveon AS TIME(0)) = '10:00:00';