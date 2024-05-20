--
-- Table structure for table `waterways_guide`
--

CREATE TABLE `#__waterways_guide` (
  `GuideID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__waterways_guide_requests` (
  `memberid` bigint(20) NOT NULL DEFAULT '0',
  `GuideCountry` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideWaterway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideRequestDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `GuideRequestMethod` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `GuideRequestStatus` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__waterways_guide_services` (
  `ID` bigint(20) NOT NULL DEFAULT 0,
  `ServiceID` varchar(4) NOT NULL DEFAULT '',
  `ServiceDescGB` mediumtext DEFAULT NULL,
  `ServiceHelpGB` varchar(100) DEFAULT NULL,
  `ServiceCategory` mediumtext DEFAULT NULL,
  `ServiceSortOrder` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__waterways_guide_country` (
  `iso` char(2) NOT NULL DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `printable_name` varchar(80) NOT NULL DEFAULT '',
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `postzone` char(3) DEFAULT NULL,
  PRIMARY KEY (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__waterways_guide_changelog` (
  `LogID` bigint NOT NULL AUTO_INCREMENT,
  `User` mediumtext COLLATE utf8mb4_unicode_ci,
  `MemberID` bigint DEFAULT NULL,
  `Subject` longtext COLLATE utf8mb4_unicode_ci,
  `ChangeDesc` longtext COLLATE utf8mb4_unicode_ci,
  `ChangeDate` datetime DEFAULT NULL,
  PRIMARY KEY (`LogID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;