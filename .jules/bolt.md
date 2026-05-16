## 2024-05-16 - Defer loading for hidden tabs
**Learning:** In a single-page application structure like EcoLoop where client-side JavaScript handles routing by hiding/showing tabs (and all HTML is rendered upfront), eager loading of images in hidden tabs can cause significant initial page load delays and unnecessary bandwidth usage.
**Action:** When working on SPAs that use CSS to hide off-screen content, immediately identify all media elements within these hidden sections and apply `loading="lazy"` to defer their loading until the content becomes visible to the user.
