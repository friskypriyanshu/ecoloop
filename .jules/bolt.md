## 2026-05-07 - [Client-Side Routing Eager Loading Bottleneck]
**Learning:** In applications using client-side routing where HTML for hidden tabs is rendered in the initial DOM (like `index.php` doing `display: none;`), the browser will still eagerly load all resources (like `<img>` tags and external API calls) within those hidden sections. This wastes bandwidth and delays the main page `load` event.
**Action:** Always add `loading="lazy"` to media elements within hidden tab sections to defer network requests until the tab actually becomes visible.
