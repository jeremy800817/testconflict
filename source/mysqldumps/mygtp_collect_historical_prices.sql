SET @mydate = '2020-10-01 16:00:00';

INSERT INTO myhistoricalprice (hpr_low, hpr_high, hpr_open, hpr_close, hpr_priceon, hpr_priceproviderid, hpr_status, hpr_createdon, hpr_modifiedon, hpr_createdby, hpr_modifiedby)

SELECT
MIN(pa.pst_companysellppg) as hpr_low,
MAX(pa.pst_companysellppg) as hpr_high,
(SELECT p.pst_companysellppg FROM pricestream p WHERE p.pst_id = MIN(pa.pst_id) ) as hpr_open,
(SELECT p.pst_companysellppg FROM pricestream p WHERE p.pst_id = MAX(pa.pst_id) ) as hpr_close,
pa.pst_createdon as hpr_priceon,
pa.pst_providerid as hpr_priceproviderid,
1, '2020-01-06 08:00:00', '2020-01-06 08:00:00',0,0

FROM pricestream pa
WHERE pa.pst_createdon >= @mydate
GROUP BY pa.pst_providerid, DATE(CONVERT_TZ(pa.pst_createdon, '+00:00', '+08:00'))
ORDER BY pa.pst_createdon ASC;