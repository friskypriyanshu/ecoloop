## 2024-06-25 - Mitigate Eager Loading of Hidden Tabs Content
**Learning:** The application uses client-side JavaScript routing to show/hide tabs, meaning `index.php` renders all HTML content upfront. This includes images and external API calls (like generating QR codes via api.qrserver.com) for hidden tabs, causing unnecessary eager loading bottlenecks.
**Action:** Always add `loading="lazy"` to media elements (like `<img>` tags) located within hidden tabs to defer their loading until they actually become visible to the user.
