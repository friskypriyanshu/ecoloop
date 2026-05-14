## 2024-05-XX - Eager Loading Hidden Tabs Bottleneck
**Learning:** The application uses client-side JavaScript routing to hide/show tabs, meaning index.php renders all HTML content (including hidden sections and their nested media) on the initial page load.
**Action:** Always add `loading="lazy"` to media elements (like images and external API calls) within hidden sections to defer loading until they become visible.
