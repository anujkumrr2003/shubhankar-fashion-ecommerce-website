<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Wishlist | Shubhankar</title>

  <meta name="robots" content="noindex,follow">
  <link rel="canonical" href="https://shubhankarfashion.com/wishlist">

  <link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Site styles -->
  <link rel="stylesheet" href="https://shubhankarfashion.com/css/style.css">

  <style>
    .thumb { position: relative; }
    .wishlist-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      z-index: 5;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: none;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 1px 4px rgba(0,0,0,0.15);
      cursor: pointer;
    }
    .wishlist-btn i { font-size: 1.05rem; color: #dc3545; }
  </style>

</head>

<body>

  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index">Home</a> / <span>Wishlist</span></nav>
      <h1 class="h3 mb-0">My Wishlist</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="small text-muted-soft" id="resultCount"></span>
        <button class="btn btn-sm btn-outline-danger d-none" id="clearWishlistBtn">
          <i class="bi bi-trash"></i> Clear Wishlist
        </button>
      </div>

      <div class="row g-3" id="wishlistGrid"></div>

      <div id="emptyState" class="text-center text-muted-soft py-5 d-none">
        <i class="bi bi-heart fs-1 d-block mb-3"></i>
        <p class="mb-3">Your wishlist is empty.</p>
        <a href="shop" class="btn btn-dark btn-sm text-white">Continue Shopping</a>
      </div>

    </div>
  </section>

<style>
    .price{
        margin: auto;
    }
</style>
  <?php include 'footer.php'; ?>

  <script src="js/wishlist.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {

      const grid = document.getElementById("wishlistGrid");
      const emptyState = document.getElementById("emptyState");
      const resultCount = document.getElementById("resultCount");
      const clearBtn = document.getElementById("clearWishlistBtn");

      function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str || "";
        return div.innerHTML;
      }

      function render() {
        const list = getWishlist();
        const items = Object.values(list);

        grid.innerHTML = "";
        resultCount.textContent = items.length + " Item" + (items.length === 1 ? "" : "s");
        emptyState.classList.toggle("d-none", items.length !== 0);
        clearBtn.classList.toggle("d-none", items.length === 0);

        items.forEach(function (item) {
          const price = parseFloat(item.price) || 0;
          const roughPrice = parseFloat(item.roughPrice) || 0;
          let discountHtml = "";

        //   if (roughPrice > price) {
        //     const discount = Math.round(((roughPrice - price) / roughPrice) * 100);
        //     discountHtml =
        //       '<span class="price-strike small text-muted text-decoration-line-through ms-1">₹' + roughPrice + '</span>' +
        //       '<span class="small text-success ms-1">' + discount + '% OFF</span>';
        //   }

          const sizesHtml = item.sizes
            // ? '<div class="small text-muted-soft mb-2">Sizes: ' + escapeHtml(item.sizes.split(",").join(", ")) + '</div>'
            // : "";

          const col = document.createElement("div");
          col.className = "col-6 col-md-4 col-lg-3";
          col.innerHTML =
            '<div class="product-card">' +
              '<a href="' + escapeHtml(item.url) + '">' +
                '<div class="thumb">' +
                  '<button type="button" class="wishlist-btn js-remove-item" data-id="' + escapeHtml(item.id) + '" aria-label="Remove from wishlist">' +
                    '<i class="bi bi-heart-fill"></i>' +
                  '</button>' +
                  '<img src="' + escapeHtml(item.image) + '" class="img-fluid product-img" alt="' + escapeHtml(item.name) + '">' +
                '</div>' +
              '</a>' +
              '<div class="body">' +
                '<a href="' + escapeHtml(item.url) + '" class="text-decoration-none text-center">' +
                  '<div class="name">' + escapeHtml(item.name) + '</div>' +
                '</a>' +
                // sizesHtml +
                '<div class="d-flex align-items-center gap-2">' +
                  '<span class="price">₹' + price + '</span>' +
                  discountHtml +
                '</div>' +
              '</div>' +
            '</div>';

          grid.appendChild(col);
        });

        // Wire up remove buttons
        grid.querySelectorAll(".js-remove-item").forEach(function (btn) {
          btn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            removeFromWishlist(btn.dataset.id);
            render();
          });
        });
      }

      clearBtn.addEventListener("click", function () {
        if (confirm("Remove all items from your wishlist?")) {
          saveWishlist({});
          updateWishlistCount();
          render();
        }
      });

      render();
    });
  </script>

</body>

</html>