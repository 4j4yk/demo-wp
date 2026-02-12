# Demo Faceted Discovery

Minimal WordPress plugin that demonstrates faceted search over reports.

## What It Includes

- Report post type: `discovery_report`
- Filter taxonomies: `discovery_topic`, `discovery_region`
- REST endpoint: `/wp-json/discovery/v1/reports`
- Contextual facet counts for topics and regions
- Auto-seeded demo content on first activation (3 reports + 4 terms)

## Quick Install (Manual)

1. Copy this folder to `wp-content/plugins/demo-faceted-discovery`.
2. Activate **Demo Faceted Discovery** in WordPress Admin.
3. Go to **Settings > Permalinks** and click **Save Changes** once.

## Sample Data

On first activation, the plugin automatically creates:

- Topic terms: `health`, `housing`
- Region terms: `north-america`, `europe`
- Reports:
1. Community Health Access Snapshot
2. Affordable Housing Supply Outlook
3. Regional Health Equity Brief

Create taxonomy terms:

- Topic terms (`discovery_topic`): `health`, `housing`
- Region terms (`discovery_region`): `north-america`, `europe`

Create at least 3 report posts (`discovery_report`):

1. Report A: topic `health`, region `north-america`
2. Report B: topic `housing`, region `north-america`
3. Report C: topic `health`, region `europe`

## API Examples

```bash
curl "https://example.test/wp-json/discovery/v1/reports"
curl "https://example.test/wp-json/discovery/v1/reports?topic=health"
curl "https://example.test/wp-json/discovery/v1/reports?region=north-america&search=equity"
```

## Code Map

- `includes/class-discovery-plugin.php`: plugin bootstrap and activation
- `includes/content/class-discovery-report-type.php`: post type registration
- `includes/content/class-discovery-taxonomies.php`: taxonomy registration
- `includes/rest/class-discovery-reports-rest.php`: REST route, filtering, and facet counts
