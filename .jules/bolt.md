## 2024-04-19 - [Lazy Loading Critical for Hidden Tabs]
**Learning:** The application renders all HTML (including hidden client-side routed tabs) on the initial load of `index.php`. Any media within those tabs is fetched eagerly, impacting initial load time.
**Action:** Always ensure `loading="lazy"` is used on `<img>` tags, especially those in hidden tabs, to prevent unnecessary resource fetching on initial page load.
