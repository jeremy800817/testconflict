ALTER TABLE documents
    ADD COLUMN doc_scheduledon datetime 
AFTER doc_status

ALTER TABLE documents
    ADD COLUMN doc_printon datetime
AFTER doc_scheduledon
    
    