# Waterways Guide Component for Joomla 5

A Joomla 5 component for managing moorings, hazards, bridges, locks, and other waterway information.

## Version

See `<version>` in [com_waterways_guide.xml](com_waterways_guide.xml)

## Requirements

- Joomla 5.x
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.4+

## Project Structure

```
waterways-guide/
├── admin/                      # Administrator backend
│   ├── forms/                  # Form XML definitions
│   │   ├── guide.xml           # Guide edit form
│   │   ├── filter_guides.xml   # Guides list filter
│   │   ├── filter_requests.xml # Requests list filter
│   │   └── filter_changelogs.xml
│   ├── language/en-GB/         # Language files
│   ├── services/
│   │   └── provider.php        # Dependency injection
│   ├── sql/
│   │   ├── install.mysql.utf8.sql
│   │   └── uninstall.mysql.utf8.sql
│   ├── src/
│   │   ├── Controller/         # Admin controllers
│   │   ├── Extension/          # Component extension class
│   │   ├── Field/              # Custom form fields
│   │   ├── Helper/             # Helper classes
│   │   ├── Model/              # Admin models
│   │   ├── Table/              # Database table classes
│   │   └── View/               # Admin views
│   └── tmpl/                   # Admin templates
│       ├── changelogs/
│       ├── guide/
│       ├── guides/
│       └── requests/
├── site/                       # Frontend
│   ├── cli/                    # CLI scripts
│   ├── helpers/                # Legacy helpers
│   ├── language/en-GB/
│   ├── pdf/                    # PDF generation assets
│   ├── services/
│   │   └── provider.php
│   ├── src/
│   │   ├── Controller/
│   │   ├── Helper/
│   │   ├── Router/
│   │   └── View/
│   │       ├── Kml/            # KML export view
│   │       ├── Pdf/            # PDF export view
│   │       └── Wwg/            # Main waterways guide view
│   └── tmpl/
│       └── wwg/
├── media/                      # Public assets
│   ├── css/
│   ├── images/
│   └── js/
├── J3/                         # Legacy Joomla 3 code (reference)
├── com_waterways_guide.xml     # Component manifest
└── README.md
```

## Database Tables

The component uses the following tables (with Joomla prefix `#__`):

| Table | Description |
|-------|-------------|
| `#__waterways_guide` | Main guide entries (moorings, hazards, etc.) |
| `#__waterways_guide_requests` | Access requests from members |
| `#__waterways_guide_services` | Service/facility codes lookup |
| `#__waterways_guide_country` | Country lookup table |
| `#__waterways_guide_changelog` | Audit log of changes |

## Installation

1. Create a ZIP package of the component
2. In Joomla Administrator, go to System > Install > Extensions
3. Upload and install the package

## Migration from Legacy Tables

If migrating from an older installation that uses `tbl*` prefixed tables, run the following SQL queries to copy data to the new component tables.

### Prerequisites

- Backup your database before running migration queries
- Replace `#__` with your actual Joomla table prefix (e.g., `jos_`)
- The legacy tables are assumed to be: `tblwwg`, `tblwwgrequest`, `tblservices`, `tblcountry`, `tblwwgchangelog`

### Migration Queries

```sql
-- ============================================
-- MIGRATION: Legacy tbl* tables to Joomla 5 component tables
-- ============================================

-- 1. Migrate Guide Data (tblwwg -> #__waterways_guide)
INSERT INTO `#__waterways_guide` (
    GuideID, GuideNo, GuideVer, GuideCountry, GuideWaterway,
    GuideSummary, GuideName, GuideLatLong, GuideLocation, GuideRef,
    GuideMooring, GuideFacilities, GuideCodes, GuideCosts, GuideRating,
    GuideAmenities, GuideContributors, GuideRemarks, GuideLat, GuideLong,
    GuideOrder, GuideDocs, GuidePostingDate, GuideCategory, GuideUpdate,
    GuideStatus, GuideEditorMemNo
)
SELECT
    GuideID, GuideNo, GuideVer, GuideCountry, GuideWaterway,
    GuideSummary, GuideName, GuideLatLong, GuideLocation, GuideRef,
    GuideMooring, GuideFacilities, GuideCodes, GuideCosts, GuideRating,
    GuideAmenities, GuideContributors, GuideRemarks, GuideLat, GuideLong,
    GuideOrder, GuideDocs, GuidePostingDate, GuideCategory, GuideUpdate,
    GuideStatus, GuideEditorMemNo
FROM `tblwwg`;

-- 2. Migrate Access Requests (tblwwgrequest -> #__waterways_guide_requests)
INSERT INTO `#__waterways_guide_requests` (
    memberid, GuideCountry, GuideWaterway,
    GuideRequestDate, GuideRequestMethod, GuideRequestStatus
)
SELECT
    memberid, GuideCountry, GuideWaterway,
    GuideRequestDate, GuideRequestMethod, GuideRequestStatus
FROM `tblwwgrequest`;

-- 3. Migrate Services Lookup (tblservices -> #__waterways_guide_services)
INSERT INTO `#__waterways_guide_services` (
    ID, ServiceID, ServiceDescGB, ServiceHelpGB,
    ServiceCategory, ServiceSortOrder
)
SELECT
    ID, ServiceID, ServiceDescGB, ServiceHelpGB,
    ServiceCategory, ServiceSortOrder
FROM `tblservices`;

-- 4. Migrate Country Lookup (tblcountry -> #__waterways_guide_country)
INSERT INTO `#__waterways_guide_country` (
    iso, name, printable_name, iso3, numcode, postzone
)
SELECT
    iso, name, printable_name, iso3, numcode, postzone
FROM `tblcountry`;

-- 5. Migrate Change Log (tblwwgchangelog -> #__waterways_guide_changelog)
INSERT INTO `#__waterways_guide_changelog` (
    LogID, User, MemberID, Subject, ChangeDesc, ChangeDate
)
SELECT
    LogID, User, MemberID, Subject, ChangeDesc, ChangeDate
FROM `tblwwgchangelog`;
```

### Post-Migration Verification

After running the migration, verify the data counts match:

```sql
-- Verify migration counts
SELECT 'Guides' as TableName,
       (SELECT COUNT(*) FROM tblwwg) as Legacy,
       (SELECT COUNT(*) FROM `#__waterways_guide`) as New;

SELECT 'Requests' as TableName,
       (SELECT COUNT(*) FROM tblwwgrequest) as Legacy,
       (SELECT COUNT(*) FROM `#__waterways_guide_requests`) as New;

SELECT 'Services' as TableName,
       (SELECT COUNT(*) FROM tblservices) as Legacy,
       (SELECT COUNT(*) FROM `#__waterways_guide_services`) as New;

SELECT 'Countries' as TableName,
       (SELECT COUNT(*) FROM tblcountry) as Legacy,
       (SELECT COUNT(*) FROM `#__waterways_guide_country`) as New;

SELECT 'Changelog' as TableName,
       (SELECT COUNT(*) FROM tblwwgchangelog) as Legacy,
       (SELECT COUNT(*) FROM `#__waterways_guide_changelog`) as New;
```

## Admin Backend Features

### Guides Management
- List view with search and filtering by country, waterway, category, and status
- Full CRUD operations for guide entries
- Support for 5 categories: Mooring, Hazard, Bridge, Lock, Other

### Pending Requests
- View and manage access requests from members
- Approve or reject requests

### Change Log
- Read-only audit trail of all changes made to guides

## Guide Categories

| ID | Category |
|----|----------|
| 1  | Mooring  |
| 2  | Hazard   |
| 3  | Bridge   |
| 4  | Lock     |
| 5  | Other    |

## Guide Status Values

| Value | Status      |
|-------|-------------|
| 0     | Unpublished |
| 1     | Published   |
| 2     | Archived    |
| -2    | Trashed     |

## Namespace

The component uses PSR-4 autoloading with the namespace:
```
Joomla\Component\WaterWaysGuide
```

## License

GNU General Public License version 2 or later

## Author

Russell English - russell@footsteps-design.co.uk

## Changelog

### 3.0.1
- Fixed admin controller option routing (underscore in component name)

### 3.0.0
- Full admin backend with Guides, Requests, and Changelog management
- Joomla 5 native MVC architecture
- PSR-4 namespacing throughout

### 2.5.0
- Added admin backend UI

### 2.0.0
- Initial Joomla 5 migration
- Frontend waterways guide view
- PDF and KML export functionality
