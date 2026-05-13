
## 2024-05-18 - Client-Side Routing and Eager Loading
**Learning:** The application uses client-side routing to hide/show tabs, but all HTML content for all tabs is rendered on the initial page load by `index.php`. This means media in hidden tabs (like external API-generated QR codes or uploaded item images) load eagerly, creating a bottleneck on initial load.
**Action:** When working with this architecture, mitigate this by ensuring media elements within hidden sections utilize `loading="lazy"` to defer requests until the elements actually become visible to the user.
