CREATE TABLE IF NOT EXISTS `#__waterways_guide` (
  `GuideID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checked_out` INT(11)  UNSIGNED,
  `checked_out_time` DATETIME NULL  DEFAULT NULL ,
  `created_by` INT(11)  NULL  DEFAULT 0,
  `modified_by` INT(11)  NULL  DEFAULT 0,
  `GuideNo` bigint(20) NOT NULL DEFAULT '0',
  `GuideVer` smallint(6) NOT NULL DEFAULT '0',
  `GuideCountry` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GuideWaterway` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GuideSummary` longtext COLLATE utf8mb4_unicode_ci,
  `GuideName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GuideLatLong` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GuideLocation` longtext COLLATE utf8mb4_unicode_ci,
  `GuideRef` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideMooring` longtext COLLATE utf8mb4_unicode_ci,
  `GuideFacilities` longtext COLLATE utf8mb4_unicode_ci,
  `GuideCodes` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideCosts` longtext COLLATE utf8mb4_unicode_ci,
  `GuideRating` int(11) DEFAULT '0',
  `GuideAmenities` longtext COLLATE utf8mb4_unicode_ci,
  `GuideContributors` longtext COLLATE utf8mb4_unicode_ci,
  `GuideRemarks` longtext COLLATE utf8mb4_unicode_ci,
  `GuideLat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '51.1',
  `GuideLong` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '2.2',
  `GuideOrder` decimal(10,2) DEFAULT NULL,
  `GuideDocs` longtext COLLATE utf8mb4_unicode_ci,
  `GuidePostingDate` datetime DEFAULT NULL,
  `GuideCategory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GuideUpdate` datetime DEFAULT NULL,
  `GuideStatus` tinyint(3) UNSIGNED DEFAULT NULL,
  `GuideEditorMemNo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`GuideID`)
  ,KEY `idx_state` (`state`)
  ,KEY `idx_checked_out` (`checked_out`)
  ,KEY `idx_created_by` (`created_by`)
  ,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__waterways_guide_requests` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `state` TINYINT(1)  NULL  DEFAULT 1,
  `ordering` INT(11)  NULL  DEFAULT 0,
  `checked_out` INT(11)  UNSIGNED,
  `checked_out_time` DATETIME NULL  DEFAULT NULL ,
  `created_by` INT(11)  NULL  DEFAULT 0,
  `modified_by` INT(11)  NULL  DEFAULT 0,
  `memberid` bigint(20) NOT NULL DEFAULT '0',
  `GuideCountry` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideWaterway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideRequestDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `GuideRequestMethod` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideRequestStatus` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
  ,KEY `idx_state` (`state`)
  ,KEY `idx_checked_out` (`checked_out`)
  ,KEY `idx_created_by` (`created_by`)
  ,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;