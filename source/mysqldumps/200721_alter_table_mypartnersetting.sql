ALTER TABLE `mypartnersetting`
	ADD COLUMN `psg_dgpeakpartnerbuycommission` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `psg_dgpartnersellcommission`;
ALTER TABLE `mypartnersetting`
	ADD COLUMN `psg_dgpeakpartnersellcommission` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `psg_dgpeakpartnerbuycommission`;
ALTER TABLE `mypartnersetting`
	ADD COLUMN `psg_dgpeakhourfrom` DATETIME DEFAULT NULL AFTER `psg_dgpeakpartnersellcommission`;
ALTER TABLE `mypartnersetting`
	ADD COLUMN `psg_dgpeakhourto` DATETIME DEFAULT NULL AFTER `psg_dgpeakhourfrom`;