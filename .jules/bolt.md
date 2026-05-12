## 2024-05-24 - Eager Loading Bottleneck with Client-Side Routing
**Learning:** The application uses client-side JavaScript routing to hide/show tabs, meaning `index.php` renders all HTML content (including hidden sections and their nested media) on the initial page load. This causes unnecessary network requests for images the user might never see, increasing initial load time and bandwidth usage.
**Action:** Mitigate eager loading bottlenecks by adding `loading="lazy"` to media elements (like images and external API calls) within hidden sections to defer loading until they become visible.
