
## 2024-05-19 - Deferring images on hidden tabs
**Learning:** EcoLoop's architecture renders all hidden tab content upfront in `index.php` on initial page load, which includes external API calls (like the QR code) and item images that aren't immediately visible to the user.
**Action:** Always mitigate this specific eager loading bottleneck by using `loading="lazy"` on media elements inside sections that are hidden on initial load, deferring the network requests until they enter the viewport.
