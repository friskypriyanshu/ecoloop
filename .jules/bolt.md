## 2024-05-24 - [Lazy Loading Images in Hidden Sections]
**Learning:** EcoLoop renders all hidden sections upfront on the initial page load since it relies on client-side JS for tab routing. This causes eager loading of all images (like QR codes and marketplace items), slowing down initial page render.
**Action:** Always add `loading="lazy"` to media elements inside hidden tabs to defer their requests until the user navigates to them.
