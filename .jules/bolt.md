## 2024-05-03 - Eager Loading in Client-Side Routed Tabs
**Learning:** The application uses client-side routing to hide/show tabs, meaning index.php renders all HTML content upfront. Eager loading of images inside hidden sections creates a bottleneck on initial load.
**Action:** Always add loading="lazy" to media elements and external API calls within hidden sections.
