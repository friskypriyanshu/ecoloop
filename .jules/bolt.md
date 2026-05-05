## 2024-05-05 - Eager Loading in Client-Side Routing
**Learning:** In applications using client-side JS routing (like `index.php`), all HTML and nested media are rendered upfront. External APIs and large images in hidden tabs can cause major initial load bottlenecks.
**Action:** Always add `loading="lazy"` to `<img>` and `<iframe>` elements that are initially hidden by the router logic to defer the network requests until they become visible.
