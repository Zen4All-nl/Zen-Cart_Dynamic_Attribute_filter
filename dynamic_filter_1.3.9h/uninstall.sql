SET @cid=0;
SELECT (@cid:=configuration_group_id) as cid 
FROM configuration_group
WHERE configuration_group_title= 'Dynamic Filter';
DELETE FROM configuration WHERE configuration_group_id = @cid;
DELETE FROM configuration_group WHERE configuration_group_id = @cid;