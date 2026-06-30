SELECT *
FROM {INSERT TABLE NAME}
WHERE elb_status_code LIKE '4%%' AND NOT elb_status_code = '400' OR elb_status_code LIKE '5%%'
AND day = '2025/10/21'
ORDER BY time desc
