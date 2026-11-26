/**
 * Shubhankar Fashion - Wishlist (localStorage based)
 * ---------------------------------------------------
 * Stores full product data per item so wishlist.php can render
 * the wishlist page without hitting the database.
 *
 * Storage shape:
 * {
 *   "12": { id:"12", name:"...", price:"999", roughPrice:"1499", image:"...", url:"...", sizes:"S,M,L" },
 *   "45": { ... }
 * }
 */

const WISHLIST_KEY = "shubhankar_wishlist";

function getWishlist() {
  try {
    const raw = localStorage.getItem(WISHLIST_KEY);
    return raw ? JSON.parse(raw) : {};
  } catch (e) {
    return {};
  }
}

function saveWishlist(data) {
  localStorage.setItem(WISHLIST_KEY, JSON.stringify(data));
}

function isInWishlist(id) {
  const list = getWishlist();
  return Object.prototype.hasOwnProperty.call(list, String(id));
}

function toggleWishlist(id, productData) {
  const list = getWishlist();
  id = String(id);

  if (list[id]) {
    delete list[id];
  } else {
    list[id] = productData;
  }

  saveWishlist(list);
  updateWishlistCount();
  return !!list[id];
}

function removeFromWishlist(id) {
  const list = getWishlist();
  delete list[String(id)];
  saveWishlist(list);
  updateWishlistCount();
}

function updateWishlistCount() {
  const count = Object.keys(getWishlist()).length;
  document.querySelectorAll(".js-wishlist-count").forEach(function (el) {
    el.textContent = count;
    el.classList.toggle("d-none", count === 0);
  });
}

function readHeartDataset(btn) {
  return {
    id: btn.dataset.id,
    name: btn.dataset.name || "",
    price: btn.dataset.price || "",
    roughPrice: btn.dataset.roughPrice || "",
    image: btn.dataset.image || "",
    url: btn.dataset.url || "",
    sizes: btn.dataset.sizes || ""
  };
}

function paintHeart(btn, active) {
  const icon = btn.querySelector("i");
  btn.classList.toggle("is-active", active);
  if (icon) {
    icon.classList.toggle("bi-heart-fill", active);
    icon.classList.toggle("bi-heart", !active);
  }
}

function initWishlistHearts() {
  document.querySelectorAll(".js-wishlist-toggle").forEach(function (btn) {
    paintHeart(btn, isInWishlist(btn.dataset.id));

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const data = readHeartDataset(btn);
      const nowActive = toggleWishlist(data.id, data);
      paintHeart(btn, nowActive);
    });
  });

  updateWishlistCount();
}

document.addEventListener("DOMContentLoaded", function () {
  initWishlistHearts();
});