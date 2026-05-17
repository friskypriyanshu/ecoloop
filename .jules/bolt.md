## 2026-05-17 - Native Lazy Loading in PHP/SPA
**Learning:** In a PHP app that acts as an SPA (all tabs rendered upfront but hidden via CSS), images in hidden tabs are still fetched eagerly by the browser, causing a performance bottleneck.
**Action:** Always apply `loading="lazy"` to images that reside within hidden elements or below the fold to defer loading until they become visible.
