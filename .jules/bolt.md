## 2026-05-09 - [Lazy Loading Mitigates Eager Rendering]
**Learning:** The application uses client-side JavaScript routing to hide/show tabs, meaning index.php renders all HTML content upfront. Due to index.php rendering all hidden tab content upfront, mitigate eager loading bottlenecks by adding loading='lazy' to media elements.
**Action:** Adding loading='lazy' to images within hidden sections.
