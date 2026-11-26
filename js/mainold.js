/* =========================================================
   NEHWELL / SHUBHANKAR — main.js

   Country -> Currency + B2B/B2C
   Default Country -> India
   Default Currency -> INR
   Default Mode -> B2B
   Live Exchange Rates
   Cart
   Product Price Conversion
   Country Dropdown
   ========================================================= */

 




const COUNTRIES = [
  {
    code: "IN",
    name: "India",
    currency: "INR",
    symbol: "₹",
    mode: "B2C",
    dial: "+91"
  },

  {
    code: "US",
    name: "USA",
    currency: "USD",
    symbol: "$",
    mode: "B2C",
    dial: "+1"
  },

  {
    code: "GB",
    name: "UK",
    currency: "GBP",
    symbol: "£",
    mode: "B2C",
    dial: "+44"
  },

  {
    code: "AE",
    name: "UAE",
    currency: "AED",
    symbol: "AED",
    mode: "B2C",
    dial: "+971"
  },

  {
    code: "AU",
    name: "Australia",
    currency: "AUD",
    symbol: "A$",
    mode: "B2C",
    dial: "+61"
  },

  {
    code: "CA",
    name: "Canada",
    currency: "CAD",
    symbol: "C$",
    mode: "B2C",
    dial: "+1"
  },

  {
    code: "DE",
    name: "Germany",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+49"
  },

  {
    code: "FR",
    name: "France",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+33"
  },

  {
    code: "IT",
    name: "Italy",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+39"
  },

  {
    code: "ES",
    name: "Spain",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+34"
  },

  {
    code: "NL",
    name: "Netherlands",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+31"
  },

  {
    code: "BE",
    name: "Belgium",
    currency: "EUR",
    symbol: "€",
    mode: "B2C",
    dial: "+32"
  },

  {
    code: "CH",
    name: "Switzerland",
    currency: "CHF",
    symbol: "CHF",
    mode: "B2C",
    dial: "+41"
  },

  {
    code: "SE",
    name: "Sweden",
    currency: "SEK",
    symbol: "kr",
    mode: "B2C",
    dial: "+46"
  },

  {
    code: "NO",
    name: "Norway",
    currency: "NOK",
    symbol: "kr",
    mode: "B2C",
    dial: "+47"
  },

  {
    code: "DK",
    name: "Denmark",
    currency: "DKK",
    symbol: "kr",
    mode: "B2C",
    dial: "+45"
  },

  {
    code: "NZ",
    name: "New Zealand",
    currency: "NZD",
    symbol: "NZ$",
    mode: "B2C",
    dial: "+64"
  },

  {
    code: "SG",
    name: "Singapore",
    currency: "SGD",
    symbol: "S$",
    mode: "B2C",
    dial: "+65"
  },

  {
    code: "MY",
    name: "Malaysia",
    currency: "MYR",
    symbol: "RM",
    mode: "B2C",
    dial: "+60"
  },

  {
    code: "TH",
    name: "Thailand",
    currency: "THB",
    symbol: "฿",
    mode: "B2C",
    dial: "+66"
  },

  {
    code: "JP",
    name: "Japan",
    currency: "JPY",
    symbol: "¥",
    mode: "B2C",
    dial: "+81"
  },

  {
    code: "KR",
    name: "South Korea",
    currency: "KRW",
    symbol: "₩",
    mode: "B2C",
    dial: "+82"
  }
];





const NEH = (function () {

    const LOCALE_KEY = "nehwell_country";
    const CART_KEY = "nehwell_cart";
    const EXCHANGE_KEY = "nehwell_rates";

    window.LIVE_RATES = {};


    /* =====================================================
       GET LOCALE
       ===================================================== */

    function getLocale() {

        const savedCode =
            localStorage.getItem(LOCALE_KEY);

        /*
         * Agar user ne manually country select kiya hai
         */
        if (savedCode) {

            const savedCountry =
                COUNTRIES.find(function (country) {
                    return country.code === savedCode;
                });

            if (savedCountry) {
                return savedCountry;
            }

        }

        /*
         * Default India
         */
        return COUNTRIES.find(function (country) {
            return country.code === "IN";
        }) || COUNTRIES[0];

    }


    /* =====================================================
       GEO LOCATION
       ===================================================== */

    async function detectVisitorCountry() {

        /*
         * Agar user ne already country manually select
         * ki hui hai to Geo-IP dobara country change
         * nahi karega.
         */

        if (localStorage.getItem(LOCALE_KEY)) {
            return getLocale();
        }


        try {

            const response = await fetch(
                "https://ipapi.co/json/",
                {
                    method: "GET",
                    cache: "no-store"
                }
            );


            if (!response.ok) {
                throw new Error("Geo API failed");
            }


            const data =
                await response.json();


            const detectedCode =
                data &&
                data.country_code
                    ? data.country_code.toUpperCase()
                    : "IN";


            const country =
                COUNTRIES.find(function (item) {
                    return item.code === detectedCode;
                });


            /*
             * Country available in our list
             */
            if (country) {

                localStorage.setItem(
                    LOCALE_KEY,
                    country.code
                );

                return country;

            }


            /*
             * Country supported nahi hai
             * to India fallback
             */
            localStorage.setItem(
                LOCALE_KEY,
                "IN"
            );

            return getLocale();


        } catch (error) {

            console.warn(
                "Geo location detection failed. Using India.",
                error
            );


            /*
             * API fail hone par India
             */
            localStorage.setItem(
                LOCALE_KEY,
                "IN"
            );


            return getLocale();

        }

    }


    /* =====================================================
       SET LOCALE
       ===================================================== */

    function setLocale(code) {

        const country =
            COUNTRIES.find(function (item) {
                return item.code === code;
            });


        if (!country) {

            console.error(
                "Invalid country code:",
                code
            );

            return;

        }


        localStorage.setItem(
            LOCALE_KEY,
            country.code
        );


        applyLocaleToPage();


        /*
         * Dropdown close
         */
        const dropdown =
            document.getElementById(
                "countryPickerList"
            );

        if (dropdown) {

            const activeButton =
                dropdown.closest(".dropdown");

            if (activeButton) {

                const button =
                    activeButton.querySelector(
                        '[data-bs-toggle="dropdown"]'
                    );

                if (
                    button &&
                    typeof bootstrap !== "undefined"
                ) {

                    const instance =
                        bootstrap.Dropdown.getInstance(
                            button
                        );

                    if (instance) {
                        instance.hide();
                    }

                }

            }

        }

    }


    /* =====================================================
       LIVE EXCHANGE RATES
       ===================================================== */

    async function loadExchangeRates() {

        try {

            const saved =
                localStorage.getItem(
                    EXCHANGE_KEY
                );


            if (saved) {

                const parsed =
                    JSON.parse(saved);


                if (
                    parsed &&
                    parsed.date ===
                    new Date().toDateString() &&
                    parsed.data
                ) {

                    return parsed.data;

                }

            }

        } catch (error) {

            console.warn(
                "Saved exchange rates unavailable."
            );

        }


        try {

            const response =
                await fetch(
                    "https://open.er-api.com/v6/latest/INR",
                    {
                        cache: "no-store"
                    }
                );


            if (!response.ok) {
                throw new Error(
                    "Exchange API failed"
                );
            }


            const json =
                await response.json();


            if (
                json.result === "success" &&
                json.rates
            ) {

                localStorage.setItem(
                    EXCHANGE_KEY,
                    JSON.stringify({

                        date:
                            new Date().toDateString(),

                        data:
                            json.rates

                    })
                );


                return json.rates;

            }


        } catch (error) {

            console.error(
                "Exchange rate error:",
                error
            );

        }


        return {};

    }


    /* =====================================================
       FORMAT PRICE
       ===================================================== */

    function formatPrice(inrAmount) {

        const loc =
            getLocale();


        const amount =
            parseFloat(inrAmount);


        if (isNaN(amount)) {
            return "";
        }


        let rate = 1;


        if (loc.currency !== "INR") {

            if (
                window.LIVE_RATES &&
                window.LIVE_RATES[loc.currency]
            ) {

                rate =
                    parseFloat(
                        window.LIVE_RATES[
                            loc.currency
                        ]
                    );

            }

        }


        if (
            !rate ||
            isNaN(rate)
        ) {

            rate = 1;

        }


        const converted =
            amount * rate;


        const decimals =
            loc.currency === "INR"
                ? 0
                : 2;
        
//         let converted = amount * rate;

// /* India price +20% */
// if (loc.currency === "INR") {
//     converted = converted * 1.20;
// }

// const decimals = 
//     loc.currency === "INR" 
//         ? 0 
//         : 2;


        const formatted =
            converted.toLocaleString(
                undefined,
                {
                    minimumFractionDigits:
                        decimals,

                    maximumFractionDigits:
                        decimals
                }
            );


        return (
            loc.symbol +
            formatted
        );

    }


    /* =====================================================
       UPDATE PRICES
       ===================================================== */

    function updatePriceEls(scope) {

        const root =
            scope || document;


        root
            .querySelectorAll(
                "[data-price-inr]"
            )
            .forEach(function (el) {

                const inr =
                    parseFloat(
                        el.getAttribute(
                            "data-price-inr"
                        )
                    );


                if (isNaN(inr)) {
                    return;
                }


                el.textContent =
                    formatPrice(inr);

            });

    }


    /* =====================================================
       APPLY LOCALE TO PAGE
       ===================================================== */

    function applyLocaleToPage() {

        const loc =
            getLocale();


        /*
         * Country name
         */

        document
            .querySelectorAll(
                ".js-locale-name"
            )
            .forEach(function (el) {

                el.textContent =
                    loc.name;

            });


        /*
         * Currency
         */

        document
            .querySelectorAll(
                ".js-locale-currency"
            )
            .forEach(function (el) {

                el.textContent =
                    loc.currency;

            });


        /*
         * Mode
         */

        document
            .querySelectorAll(
                ".js-mode-badge"
            )
            .forEach(function (el) {

                el.textContent =
                    loc.mode === "B2B"
                        ? "B2B Pricing"
                        : "Retail (B2C)";

            });


        /*
         * B2B
         */

        document
            .querySelectorAll(
                ".b2b-only"
            )
            .forEach(function (el) {

                el.classList.toggle(
                    "d-none",
                    loc.mode !== "B2B"
                );

            });


        /*
         * B2C
         */

        document
            .querySelectorAll(
                ".b2c-only"
            )
            .forEach(function (el) {

                el.classList.toggle(
                    "d-none",
                    loc.mode !== "B2C"
                );

            });


        /*
         * Active country in dropdown
         */

        document
            .querySelectorAll(
                ".js-country-option"
            )
            .forEach(function (el) {

                el.classList.toggle(
                    "active",
                    el.dataset.code === loc.code
                );

            });


        /*
         * Prices
         */

        updatePriceEls();


        /*
         * Event
         */

        document.dispatchEvent(
            new CustomEvent(
                "neh:localechange",
                {
                    detail: loc
                }
            )
        );

    }


    /* =====================================================
       COUNTRY PICKER
       ===================================================== */

    function buildCountryPicker(container) {

        if (!container) {
            return;
        }


        container.innerHTML =
            COUNTRIES
                .map(function (country) {

                    return `
                        <button
                            type="button"
                            class="dropdown-item js-country-option d-flex justify-content-between align-items-center"
                            data-code="${country.code}"
                        >

                            <span>
                                ${country.name}
                            </span>

                            <span class="text-muted-soft small">
                                ${country.currency}
                            </span>

                        </button>
                    `;

                })
                .join("");


        container
            .querySelectorAll(
                ".js-country-option"
            )
            .forEach(function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        setLocale(
                            this.dataset.code
                        );

                    }
                );

            });


        /*
         * Current country active
         */

        applyLocaleToPage();

    }


    /* =====================================================
       INITIAL LOCALE
       ===================================================== */

    async function applyInitialLocale() {

        /*
         * IMPORTANT:
         *
         * Agar localStorage me country nahi hai,
         * tabhi Geo-IP detect karo.
         *
         * Agar user ne manually select kiya hai,
         * Geo-IP usko overwrite nahi karega.
         */

        if (
            !localStorage.getItem(
                LOCALE_KEY
            )
        ) {

            await detectVisitorCountry();

        }


        /*
         * Live exchange rates
         */

        window.LIVE_RATES =
            await loadExchangeRates();


        /*
         * Apply country
         */

        applyLocaleToPage();

    }


    /* =====================================================
       CART
       ===================================================== */

    function readCart() {

        try {

            return (
                JSON.parse(
                    localStorage.getItem(
                        CART_KEY
                    )
                ) || []
            );

        } catch (error) {

            return [];

        }

    }


    function writeCart(items) {

        localStorage.setItem(
            CART_KEY,
            JSON.stringify(items)
        );

        renderCartCount();

    }


    function addToCart(id, qty) {

        qty = qty || 1;

        const items =
            readCart();


        const existing =
            items.find(function (item) {
                return item.id === id;
            });


        if (existing) {

            existing.qty += qty;

        } else {

            items.push({
                id: id,
                qty: qty
            });

        }


        writeCart(items);

    }


    function setQty(id, qty) {

        let items =
            readCart();


        if (qty <= 0) {

            items =
                items.filter(function (item) {
                    return item.id !== id;
                });

        } else {

            items.forEach(function (item) {

                if (item.id === id) {
                    item.qty = qty;
                }

            });

        }


        writeCart(items);

    }


    function removeFromCart(id) {

        writeCart(
            readCart().filter(function (item) {
                return item.id !== id;
            })
        );

    }


    function cartCount() {

        return readCart().reduce(
            function (sum, item) {

                return (
                    sum +
                    Number(item.qty || 0)
                );

            },
            0
        );

    }


    function renderCartCount() {

        const count =
            cartCount();


        document
            .querySelectorAll(
                ".js-cart-count, #cartCount"
            )
            .forEach(function (el) {

                el.textContent =
                    count;


                el.style.display =
                    count > 0
                        ? "flex"
                        : "none";

            });

    }


    function clearCart() {

        writeCart([]);

    }


    /* =====================================================
       PRODUCTS
       ===================================================== */

    function cartItemsWithProduct() {

        if (
            typeof PRODUCTS === "undefined" ||
            !Array.isArray(PRODUCTS)
        ) {

            return [];

        }


        return readCart()
            .map(function (item) {

                return {
                    ...item,

                    product:
                        PRODUCTS.find(function (p) {
                            return p.id === item.id;
                        })

                };

            })
            .filter(function (item) {
                return item.product;
            });

    }


    function cartSubtotalINR() {

        return cartItemsWithProduct()
            .reduce(
                function (sum, item) {

                    return (
                        sum +
                        (
                            Number(
                                item.product.price
                            ) *
                            Number(
                                item.qty
                            )
                        )
                    );

                },
                0
            );

    }


    /* =====================================================
       STAR RATING
       ===================================================== */

    function renderStars(rating) {

        const full =
            Math.round(
                Number(rating) || 0
            );


        return (
            "★".repeat(full) +
            "☆".repeat(
                5 - full
            )
        );

    }


    /* =====================================================
       ADD TO CART
       ===================================================== */

    function bindAddToCartButtons(scope) {

        const root =
            scope || document;


        root
            .querySelectorAll(
                ".js-add-to-cart"
            )
            .forEach(function (btn) {

                if (
                    btn.dataset.cartBound === "1"
                ) {

                    return;

                }


                btn.dataset.cartBound =
                    "1";


                btn.addEventListener(
                    "click",
                    function () {

                        addToCart(
                            parseInt(
                                btn.dataset.id,
                                10
                            ),
                            1
                        );


                        const original =
                            btn.innerHTML;


                        btn.innerHTML =
                            `<i class="bi bi-check2"></i> Added`;


                        setTimeout(
                            function () {

                                btn.innerHTML =
                                    original;

                            },
                            900
                        );

                    }
                );

            });

    }


    /* =====================================================
       QUERY
       ===================================================== */

    function qs(name) {

        return new URLSearchParams(
            window.location.search
        ).get(name);

    }


    /* =====================================================
       INIT
       ===================================================== */

    async function init() {

        /*
         * Country dropdown
         */

        buildCountryPicker(
            document.getElementById(
                "countryPickerList"
            )
        );


        /*
         * Geo country
         */

        await applyInitialLocale();


        /*
         * Cart
         */

        renderCartCount();


        /*
         * Cart buttons
         */

        bindAddToCartButtons(
            document
        );


        /*
         * Dynamic content
         */

        document.addEventListener(
            "neh:rendered",
            function () {

                bindAddToCartButtons(
                    document
                );

                updatePriceEls();

            }
        );

    }


    document.addEventListener(
        "DOMContentLoaded",
        init
    );


    /* =====================================================
       PUBLIC API
       ===================================================== */

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

        renderCartCount,

        renderStars,
        bindAddToCartButtons,

        qs

    };


})();
