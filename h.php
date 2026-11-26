<?php
include 'config.php';

$categories = $conn->query("
    SELECT id, category_id, subcategory_name, image
    FROM subcategories
    WHERE status='Active'
    ORDER BY subcategory_name ASC
    LIMIT 4
");
?>
<section class="section-sm">
  <div class="container">

    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="eyebrow">Shop by need</span>
        <h2 class="h3 mt-1 mb-0">Categories</h2>
      </div>

      <a href="categories.php" class="small">
        View all <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="row g-4">

      <?php while ($cat = $categories->fetch_assoc()) { ?>

        <?php
        // Total Products
        $subId = $cat['id'];

        $countResult = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM products
                    WHERE subcategory_id='$subId'
                ");

        $count = $countResult->fetch_assoc()['total'];

        $image = (!empty($cat['image']) && file_exists("admin/uploads/" . $cat['image']))
          ? "admin/uploads/" . $cat['image']
          : "admin/uploads/no-image.png";
        ?>

        <div class="col-6 col-md-4 col-lg-3" style="border-radius:0px;">

          <div class="card h-100 shadow-sm border-0 category-card" style="border-radius:0px;">

            <!-- <a href="shop.php?subcategory=<?= $cat['id']; ?>"
                class="text-decoration-none text-dark"> -->
            <a href="shop.php?category=<?= urlencode($cat['subcategory_name']); ?>">

              <img src="<?= $image; ?>"
                class="card-img-top"
                alt="<?= htmlspecialchars($cat['subcategory_name']); ?>" style="border-radius:0px;">

              <div class="card-body text-center">

                <h6 class="fw-bold mb-1">
                  <?= htmlspecialchars($cat['subcategory_name']); ?>
                </h6>

                <!-- <span class="text-muted small">
                                    <?= $count; ?> Products
                                </span> -->

              </div>

            </a>

          </div>

        </div>

      <?php } ?>

    </div>

  </div>
</section>
<style>
  .category-card {
    overflow: hidden;
    transition: .3s ease;
    background: #fff;
    border-radius: 0;
  }

  .category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, .12) !important;
  }

  .category-card img {
    width: 100%;
    /*height: 320px;*/
    /* Large Image */
    /*object-fit: cover;*/
    border-radius: 0;
  }

  .category-card .card-body {
    padding: 18px;
  }

  .category-card h6 {
    font-size: 18px;
    font-weight: 600;
    color: #222;
    margin: 0;
  }

  /* Tablet */
  @media (max-width:991px) {
    .category-card img {
      height: 260px;
    }
  }

  /* Mobile */
  @media (max-width:576px) {
    .category-card img {
      height: 180px;
    }

    .category-card h6 {
      font-size: 16px;
    }
  }
</style>