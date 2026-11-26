/* =========================================================
   NEHWELL — main.js
   Minimal, vanilla JS only. Handles:
     1. Country -> currency + B2B/B2C mode switching
     2. Cart stored in localStorage (cart logic only —
        ready to be swapped for session/DB calls in PHP)
     3. Small render helpers reused across pages
   No build step, no frameworks.
   ========================================================= */

const NEH = (function () {

  const LOCALE_KEY = "nehwell_country";
  const CART_KEY    = "nehwell_cart";

  const EXCHANGE_KEY = "nehwell_rates";
// let LIVE_RATES = {};
window.LIVE_RATES = {};

  /* ---------------- Locale ---------------- */

  // function getLocale() {
  //   const code = localStorage.getItem(LOCALE_KEY) || "IN";
  //   return COUNTRIES.find(c => c.code === code) || COUNTRIES[0];
  // }

  // function setLocale(code) {
  //   localStorage.setItem(LOCALE_KEY, code);
  //   applyLocaleToPage();
  // }

  // function formatPrice(inrAmount) {
  //   const loc = getLocale();
  //   const converted = inrAmount * loc.rate;
  //   // INR shown with no decimals (common in Indian retail), others with 2
  //   const decimals = loc.currency === "INR" ? 0 : 2;
  //   const num = converted.toLocaleString(undefined, {
  //     minimumFractionDigits: decimals,
  //     maximumFractionDigits: decimals
  //   });
  //   return `${loc.symbol}${num}`;
  // }

  /* ---------------- Locale ---------------- */





async function loadExchangeRates() {

    let rates = JSON.parse(localStorage.getItem(EXCHANGE_KEY));

    if (rates && rates.date === new Date().toDateString()) {
        return rates.data;
    }

    try {

        const res = await fetch("https://open.er-api.com/v6/latest/INR");
        const json = await res.json();

        if (json.result === "success") {

            localStorage.setItem(EXCHANGE_KEY, JSON.stringify({
                date: new Date().toDateString(),
                data: json.rates
            }));

            return json.rates;
        }

    } catch(e) {
        console.error(e);
    }

    return {};
}



/* Detect visitor country only first time */
async function detectCountry() {
  try {
    // Free Geo-IP API
    const res = await fetch("https://ipapi.co/json/");
    const data = await res.json();

    if (data && data.country_code) {
      const code = data.country_code.toUpperCase();

      const exists = COUNTRIES.find(c => c.code === code);

      if (exists) {
        localStorage.setItem(LOCALE_KEY, code);
      } else {
        localStorage.setItem(LOCALE_KEY, "US"); // Default for unsupported countries
      }
    } else {
      localStorage.setItem(LOCALE_KEY, "IN");
    }
  } catch (e) {
    localStorage.setItem(LOCALE_KEY, "IN");
  }
}

function getLocale() {
  const code = localStorage.getItem(LOCALE_KEY) || "IN";
  return COUNTRIES.find(c => c.code === code) || COUNTRIES[0];
}

function setLocale(code) {
  localStorage.setItem(LOCALE_KEY, code);
  applyLocaleToPage();
}

function formatPrice(inrAmount) {
  const loc = getLocale();

  // const rate = LIVE_RATES[loc.currency] || 1;
  const rate = LIVE_RATES[loc.currency] || 1;

const converted = inrAmount * rate;

  const decimals = loc.currency === "INR" ? 0 : 2;

  const num = converted.toLocaleString(undefined, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  });

  return `${loc.symbol}${num}`;
}

async function applyInitialLocale() {

    if (!localStorage.getItem(LOCALE_KEY)) {
        await detectCountry();
    }

    // LIVE_RATES = await loadExchangeRates();
window.LIVE_RATES = await loadExchangeRates();
    applyLocaleToPage();
}




  /* Update every element tagged with a base INR price */
  


//   function updatePriceEls(scope) {

//     const root = scope || document;

//     root.querySelectorAll("[data-price-inr]").forEach(el => {

//         const inr = parseFloat(el.dataset.priceInr);

//         if (!isNaN(inr)) {

//             el.textContent = formatPrice(inr);

//         }

//     });

// }


function updatePriceEls(scope) {

    const root = scope || document;

    const loc = getLocale();

    root.querySelectorAll("[data-price-inr]").forEach(function(el){

        // Agar value already current currency me hai
        if(el.dataset.currency &&
           el.dataset.currency === loc.currency){

            return;
        }

        const inr = parseFloat(el.dataset.priceInr);

        if(isNaN(inr)) return;

        el.textContent = formatPrice(inr);

    });

}

  /* Toggle B2B-only / B2C-only blocks + badges based on current mode */
  function applyLocaleToPage() {
    const loc = getLocale();

    document.querySelectorAll(".js-locale-name").forEach(el => el.textContent = loc.name);
    document.querySelectorAll(".js-locale-currency").forEach(el => el.textContent = loc.currency);
    document.querySelectorAll(".js-mode-badge").forEach(el => {
      el.textContent = loc.mode === "B2B" ? "B2B Pricing" : "Retail (B2C)";
    });

    document.querySelectorAll(".b2b-only").forEach(el => {
      el.classList.toggle("d-none", loc.mode !== "B2B");
    });
    document.querySelectorAll(".b2c-only").forEach(el => {
      el.classList.toggle("d-none", loc.mode !== "B2C");
    });

    // highlight active country in the picker, if rendered
    document.querySelectorAll(".js-country-option").forEach(el => {
      el.classList.toggle("active", el.getAttribute("data-code") === loc.code);
    });

    updatePriceEls();
    document.dispatchEvent(new CustomEvent("neh:localechange", { detail: loc }));
  }

  function buildCountryPicker(container) {
    if (!container) return;
    container.innerHTML = COUNTRIES.map(c => `
      <button type="button" class="dropdown-item js-country-option d-flex justify-content-between align-items-center"
              data-code="${c.code}">
        <span>${c.name}</span>
        <span class="text-muted-soft small">${c.currency}</span>
      </button>
    `).join("");

    container.querySelectorAll(".js-country-option").forEach(btn => {
      btn.addEventListener("click", () => setLocale(btn.getAttribute("data-code")));
    });
  }

  /* ---------------- Cart ---------------- */

  function readCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
    catch (e) { return []; }
  }
  function writeCart(items) {
    localStorage.setItem(CART_KEY, JSON.stringify(items));
    renderCartCount();
  }
  function addToCart(id, qty) {
    qty = qty || 1;
    const items = readCart();
    const existing = items.find(i => i.id === id);
    if (existing) existing.qty += qty; else items.push({ id, qty });
    writeCart(items);
  }
  function setQty(id, qty) {
    let items = readCart();
    if (qty <= 0) items = items.filter(i => i.id !== id);
    else items.forEach(i => { if (i.id === id) i.qty = qty; });
    writeCart(items);
  }
  function removeFromCart(id) {
    writeCart(readCart().filter(i => i.id !== id));
  }
  function cartItemsWithProduct() {
    return readCart().map(i => ({ ...i, product: PRODUCTS.find(p => p.id === i.id) }))
      .filter(i => i.product);
  }
  function cartCount() {
    return readCart().reduce((sum, i) => sum + i.qty, 0);
  }
  function cartSubtotalINR() {
    return cartItemsWithProduct().reduce((sum, i) => sum + i.product.price * i.qty, 0);
  }
  function renderCartCount() {
    document.querySelectorAll(".js-cart-count").forEach(el => el.textContent = cartCount());
  }
  function clearCart() { writeCart([]); }

  /* ---------------- Render helpers ---------------- */

  function renderStars(rating) {
    const full = Math.round(rating);
    return "★".repeat(full) + "☆".repeat(5 - full);
  }

  function productCardHTML(p) {
    const discount = p.mrp > p.price ? Math.round(100 - (p.price / p.mrp) * 100) : 0;

    return `
      <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card">

          <a href="product.php?id=${p.id}" class="text-decoration-none">
            <div class="thumb">
              <img src="${p.image}" alt="${p.name}" class="img-fluid product-img">
            </div>
          </a>

          <div class="body">

            ${p.badge ? `<span class="badge badge-soft mb-2">${p.badge}</span>` : ""}

            <div class="cat text-uppercase">${p.cat}</div>

            <a href="product.php?id=${p.id}" class="text-decoration-none">
              <div class="name" style="color:var(--text)">
                ${p.name}
              </div>
            </a>

            <div class="rating small mb-1">
              ${renderStars(p.rating)}
              <span class="text-muted-soft">(${p.rating})</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="price js-price" data-price-inr="${p.price}">
                ${formatPrice(p.price)}
              </span>

              ${discount ? `
                <span class="price-strike js-price" data-price-inr="${p.mrp}">
                  ${formatPrice(p.mrp)}
                </span>

                <span class="small text-success">
                  ${discount}% OFF
                </span>
              ` : ""}
            </div>

            <div class="b2b-only mb-2 d-none">
              <span class="b2b-note">
                Bulk pricing on 10+ units · GST extra
              </span>
            </div>

            <button class="btn btn-brand btn-sm w-100 js-add-to-cart" data-id="${p.id}">
              <i class="bi bi-cart-plus"></i> Add to Cart
            </button>

          </div>

        </div>
      </div>`;
}




  function bindAddToCartButtons(scope) {
    const root = scope || document;
    root.querySelectorAll(".js-add-to-cart").forEach(btn => {
      btn.addEventListener("click", () => {
        addToCart(parseInt(btn.getAttribute("data-id"), 10), 1);
        const original = btn.innerHTML;
        btn.innerHTML = `<i class="bi bi-check2"></i> Added`;
        setTimeout(() => btn.innerHTML = original, 900);
      });
    });
  }

  function qs(name) {
    return new URLSearchParams(window.location.search).get(name);
  }

  /* ---------------- Init (called on every page) ---------------- */

  function init() {
  buildCountryPicker(document.getElementById("countryPickerList"));

  applyInitialLocale();   // <-- applyLocaleToPage() ki jagah

  renderCartCount();
  bindAddToCartButtons(document);

  document.addEventListener("neh:rendered", () => bindAddToCartButtons(document));
}

  document.addEventListener("DOMContentLoaded", init);

  
  return {
    getLocale,
    setLocale,
    formatPrice,
    updatePriceEls,
    addToCart,
    setQty,
    removeFromCart,
    cartItemsWithProduct,
    cartCount,
    cartSubtotalINR,
    clearCart,
    renderStars,
    productCardHTML,
    bindAddToCartButtons,
    qs
};
})();








