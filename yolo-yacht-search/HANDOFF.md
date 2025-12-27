# Handoff File for Next Session (v91.26)

## 🚀 Completed Work (v91.26)

**Feature:** Sync Bases for Accurate Map Markers
- **Goal:** Fix the issue where the map marker was either missing or incorrectly placed (e.g., Preveza marker on land).
- **Solution:** Implemented a new feature to fetch and store all 1,500+ base coordinates directly from the Booking Manager API.
- **Implementation Details:**
    - New `wp_yolo_bases` database table created.
    - New "Sync Bases" button added to the Settings page.
    - Map logic updated to prioritize database coordinates for accurate `place` mode (marker pin).
    - Fallback logic implemented: If coordinates are not found, the map shows the general Ionian area without a misleading marker.

**Critical Fixes Included:**
- **Map Marker Fix:** Corrected the coordinates for Preveza Main Port (now at the waterfront).
- **Map Mode Logic:** Switched from unreliable hardcoded coordinates to API-sourced coordinates, ensuring the map always shows a marker for known bases.

## ❓ Open Questions / Next Steps

1. **Orphan Marinas:** We were unable to query the database to find "orphan" marinas (home_base names that don't match a base in the API).
    - **Next Step:** The user needs to run the "Sync Bases" button first. Then, we can run the SQL query again to see which `home_base` names are still missing coordinates.
    - **SQL Query to Run:** `SELECT DISTINCT home_base FROM 1XlDhIVb_yolo_yachts WHERE home_base IS NOT NULL AND home_base != '' ORDER BY home_base;`

2. **Equilibrium Yacht:** The user noted "Equilibrium" yacht from Apeiron Yachting was syncing, but Apeiron is not a friend company.
    - **Status:** The total yacht count was correct (80), meaning "Equilibrium" belongs to one of the three synced companies (Istion, Sailing in Blue, or Butch Sailing).
    - **Next Step:** If the user wants to add Apeiron Yachting, we need their company ID.

3. **Map Fallback Preference:** The current fallback for unknown bases is to show the Ionian area without a marker. The user should confirm if they prefer this, or if they would rather:
    - Show a marker at a default location (e.g., Lefkada)
    - Hide the map section entirely

## 🔗 GitHub Links

| Resource | Link | Notes |
| :--- | :--- | :--- |
| **Latest Commit** | [Link will be provided after commit] | Commit hash for v91.26 documentation update. |
| **Plugin Zip (v91.26)** | [Link will be provided after commit] | The final plugin zip file. |
| **CHANGELOG.md** | [Link will be provided after commit] | Detailed history of changes. |
| **README.md** | [Link will be provided after commit] | Overview of the latest features. |
| **Handoff File** | [Link will be provided after commit] | This document. |
