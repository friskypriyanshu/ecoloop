## 2024-05-24 - Lazy Loading Hidden Tab Media
**Learning:** The application uses client-side JavaScript routing to hide/show tabs. This means all HTML content across all tabs (including the Marketplace and Dashboard) is rendered and loaded on the initial page load, causing unnecessary requests for images that are not yet visible.
**Action:** Always use `loading="lazy"` on images (and implement dynamic fetching where possible) for media content within hidden tabs to prevent them from downloading immediately and blocking the initial page load.
