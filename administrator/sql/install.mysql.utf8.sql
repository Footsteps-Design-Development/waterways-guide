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