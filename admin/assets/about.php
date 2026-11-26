
<?php
include "config.php";
error_reporting(E_ALL);
ini_set('display_errors',1);

   $profile_sql = "SELECT * FROM profile";
$profile_result = $conn->query($profile_sql);

if ($profile_result && $profile_result->num_rows > 0) {
    while ($profile_row = $profile_result->fetch_assoc()) {
            
            ?>


<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="utf-8">
 
 <meta name="google-site-verification" content="1GE-eh3i3CFWbRH7t6juE61aZ6S2b0KSW31ovUlngQo">

  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php
 

$records = $conn->query("SELECT * FROM meta_data WHERE LOWER(page_name) = 'about' LIMIT 1");
$meta = $records->fetch_assoc();
?>

<title><?= htmlspecialchars($meta['meta_title'] ?? 'ARJ GROUP – Flats in Noida and Greater Noida') ?></title>
<meta name="description" content="<?= htmlspecialchars($meta['meta_description'] ?? '') ?>">
<meta name="keywords" content="<?= htmlspecialchars($meta['keywords'] ?? '') ?>">

  <!-- Primary meta -->
  <!--<meta name="description" content="Learn more about ARJ GROUP, a trusted real estate brand offering luxury flats and apartments in Noida and Greater Noida. Discover our mission, values, and commitment to delivering premium residential projects." />-->
  <!--<meta name="keywords" content="ARJ GROUP, flats in Noida, flats in Greater Noida, luxury apartments Noida, real estate Noida, premium projects Noida" />-->
  <meta name="author" content="ARJ GROUP" />
  <meta name="publisher" content="ARJ GROUP" />

  <!-- Robots + indexing hints -->
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <!-- If you ever want to hide, change to noindex -->

  <!-- Canonical -->
  <link rel="canonical" href="https://arjgroup.in/about.php" />

  <!-- Hreflang -->
  <link rel="alternate" hreflang="en-IN" href="https://arjgroup.in/about.php" />

  <!-- Sitemap (helps discovery; include this if present) -->
  <link rel="sitemap" type="application/xml" title="Sitemap" href="https://arjgroup.in/sitemap.xml" />

  <!-- Open Graph -->
  <meta property="og:title" content="About ARJ GROUP | Flats in Noida and Greater Noida" />
  <meta property="og:description" content="Learn more about Arj Estates, a trusted real estate brand offering luxury flats and apartments in Noida and Greater Noida. Discover our mission, values, and commitment to delivering premium residential projects." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://arjgroup.in/about.php" />
  <meta property="og:site_name" content="ARJ GROUP" />
  <meta property="og:image" content="https://arjgroup.in/admin/uploads/1780997695_1780916912_ARJ_ESTATES-removebg-preview%20(1).png" />

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="About ARJ GROUP | Flats in Noida and Greater Noida" />
  <meta name="twitter:description" content="Discover ARJ GROUP – delivering premium flats and apartments in Noida and Greater Noida with modern design and trust." />
  <meta name="twitter:image" content="https://arjgroup.in/admin/uploads/1780997695_1780916912_ARJ_ESTATES-removebg-preview%20(1).png" />
  <meta name="theme-color" content="#ffffff">

  <!-- Preload main image to help render speed -->
  <link rel="preload" href="https://arjgroup.in/admin/uploads/1780997695_1780916912_ARJ_ESTATES-removebg-preview%20(1).png" as="image">

  <!-- Structured Data: Organization + WebPage + Breadcrumb -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "https://arjgroup.in/#organization",
        "name": "ARJ GROUP",
        "url": "https://arjgroup.in/",
        "logo": "https://arjgroup.in/admin/uploads/1780997695_1780916912_ARJ_ESTATES-removebg-preview%20(1).png",
        "sameAs": [
          "https://www.facebook.com/arjspaces",
          "https://www.instagram.com/arjspaces",
          "https://www.linkedin.com/company/arjspaces"
        ],
        "contactPoint": [{
          "@type": "ContactPoint",
          "telephone": "+91-9911419406",
          "contactType": "customer service",
          "areaServed": "IN",
          "availableLanguage": "English"
        }]
      },
      {
        "@type": "WebPage",
        "@id": "https://arjgroup.in/about.php#webpage",
        "url": "https://arjgroup.in/about.php",
        "name": "About Us | ARJ GROUP",
        "isPartOf": { "@id": "https://arjgroup.in/#organization" },
        "description": "Learn more about ARJ GROUP, a trusted real estate brand offering luxury flats and apartments in Noida and Greater Noida.",
        "inLanguage": "en-IN",
        "breadcrumb": { "@id": "https://arjgroup.in/about.php#breadcrumb" }
      },
      {
        "@type": "BreadcrumbList",
        "@id": "https://arjgroup.in/about.php#breadcrumb",
        "itemListElement": [
          { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://arjgroup.in/" },
          { "@type": "ListItem", "position": 2, "name": "About Us", "item": "https://arjgroup.in/about.php" }
        ]
      }
    ]
  }
  </script>

                   <?php
 

// Fetch logo from the database
$all = $conn->query("SELECT logo FROM profile_settings LIMIT 1");

if ($all && $all->num_rows > 0) {
    $row = $all->fetch_assoc();
    $logoPath = htmlspecialchars($row['logo']); // e.g., 'admin/images/logo.png'

    echo '<link rel="icon" href="admin/' . $logoPath . '" />';
} else {
    // Optional fallback favicon
    // echo '<link rel="icon" href="images/a07f9728-9bc5-4796-825d-c8476c2ce8f6-removebg-preview.png" />';
}
?>


  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link rel="stylesheet" href="css/header.css" />
  <link rel="stylesheet" href="css/about.css" />
  <link rel="stylesheet" href="css/footer.css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alan+Sans:wght@300..900&family=Cardo:ital,wght@0,400;0,700;1,400&family=Libre+Bodoni:ital,wght@0,400..700;1,400..700&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
           <?php
   

  // Fetch the latest record
  $result = $conn->query("SELECT head_tag_manager FROM tag_manager ORDER BY id DESC LIMIT 1");

  // Check if record exists
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $head_tag_manager = $row['head_tag_manager'];
  } else {
    $head_tag_manager = '';
  }
  ?>

 
    <?php echo $head_tag_manager; ?>
  <!-- End Google Tag Manager -->
</head>

<body style="font-family: 'Jazz LET'; background-color: <?= htmlspecialchars($profile_row['heading_background_color']) ?>;">
  <!-- Google Tag Manager (noscript) -->
 <?php
   

  // Fetch the latest record
  $result = $conn->query("SELECT body_tag_manager FROM tag_manager ORDER BY id DESC LIMIT 1");

  // Check if record exists
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $head_tag_manager = $row['body_tag_manager'];
  } else {
    $head_tag_manager = '';
  }
  ?>

  <!-- Output inside script tag -->
  
    <?php echo $head_tag_manager; ?>
  <!-- End Google Tag Manager (noscript) -->
  <?php include 'header.php'; ?>
  <br>
    <section class="banner" style="
        background-image: url(images/about-us.webp);
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
      ">
    <h1 style=" color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;">About us</h1>
  </section>
  
  <!--background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; color: <?= htmlspecialchars($profile_row['background_color']) ?>;-->
  
  <section class="overiew same-section" >
    <div class="container" >
      <div class="overview-point" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>;">
        <div class="overview-heading">
          <h2 style="color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;">Who We Are</h2>
        <?php
 

// Function to remove inline styles (optional, cleans up CKEditor styles)
function cleanCKEColor($html) {
    // Remove inline styles like color, font, etc.
    return preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
}

$aboutList = [];

// Fetch records from the database
$res = $conn->query("SELECT about FROM about_content ORDER BY id DESC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        // Optional: clean styles if you want to force color globally
        $aboutList[] = cleanCKEColor($row['about']);
    }
}
?>


    <style>
       
        .about-content-wrapper {
            color: #fff !important;
            padding: 20px;
        }
        .about-content-wrapper p {
            font-size: 14px;
            line-height: 1.6;
        }
    </style>


<div class="about-content-wrapper">
    <?php if (!empty($aboutList)): ?>
        <?php foreach ($aboutList as $about): ?>
            <?= html_entity_decode($about) ?>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- <p>No about content found.</p> -->
    <?php endif; ?>
</div>





<!-- ✅ DO NOT wrap again in <p> -->
  <!-- <p style="font-size: 12px !important; color:red !important;">

  </p> -->

        <!-- //  style="color: <?= htmlspecialchars($profile_row['background_color']) ?> -->
        </div>
      </div>
    </div>
  </section>
  <section class="location-points same-section">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <div class="location">
            <div class="location-image">
              <a href="https://arjgroup.in/projects.php" ><img src="images/project.webp" alt=""
                  class="img-fluid" /></a>
            </div>
            <div class="location-botton" >
              <a href="https://arjgroup.in/projects.php" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;">Our Projects</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="location">
            <div class="location-image">
              <a href="https://arjgroup.in/projects.php" ><img src="images/location.webp" alt=""
                  class="img-fluid" /></a>
            </div>
            <div class="location-botton">
              <a href="https://arjgroup.in/projects.php" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;" >Our Developers</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="section-mission-vision same-section">
    <div class="container-fluid">
      <div class="row g-0">
        <div class="col-md-6">
          <div class="mission">
            <div class="mission-icon">
              <img src="images/mission.png" alt="" class="img-fluid" />
            </div>
            <div class="mission-heading">
              <h2 style="color: <?= htmlspecialchars($profile_row['background_color']) ?>;font-family: 'Cardo', serif;">Our Mission</h2>
              <?php 


// Fetch vision & mission content
$res = $conn->query("SELECT vision, mission FROM about_content ORDER BY id DESC LIMIT 1");
?>

<?php if ($res && $res->num_rows > 0): ?>
    <?php $row = $res->fetch_assoc(); ?>
              <p style="color: <?= htmlspecialchars($profile_row['background_color']) ?>;">
                 <?= htmlspecialchars($row['vision']) ?>
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6" > 
          <div class="vision" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; ">
            <div class="vision-icon">
              <img src="images/vision.png" alt="" class="img-fluid" />
            </div>
            <div class="vision-heading">
              <h2 style="color: <?= htmlspecialchars($profile_row['background_color']) ?>;font-family: 'Cardo', serif;">Our Vision</h2>
              <p style="color: <?= htmlspecialchars($profile_row['background_color']) ?>;">
                 <?= htmlspecialchars($row['mission']) ?>
              </p>
            </div>
          </div>
        </div>
        <?php else: ?>
  
<?php endif; ?>
      </div>
    </div>
  </section>
  <style>
       @media only screen and (max-width: 991px) {
    .button_container span, .overlay {
        background: rgb(105 105 105) !important;
    }
  }
  </style>
  <section class="Location-section same-section">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <div class="location">
            <div class="location-image">
              <a href="https://arjgroup.in/blog.php" ><img src="images/blog.webp" alt=""
                  class="img-fluid" /></a>
            </div>
            <div class="location-botton">
              <a href="https://arjgroup.in/blog.php" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;" >Read Our Blogs</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="location">
            <div class="location-image">
              <a href="https://arjgroup.in/blog.php" ><img src="images/video.webp" alt=""
                  class="img-fluid" /></a>
            </div>
            <div class="location-botton">
              <a href="https://arjgroup.in/blog.php" style="background-color:<?= htmlspecialchars($profile_row['heading_text_color']) ?>; color: <?= htmlspecialchars($profile_row['background_color']) ?>; font-family: 'Cardo', serif;" >
                Our Videos</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <br>
 
  <?php include 'footer.php'; ?>
  <style>
    .rera-item {
        background-color: #696969;
        color: #fff;
        text-align: center;
        margin-bottom: 15px;
        padding: 15px;
        font-size: 14px;
    }
    .rera-strip {
        padding: 0;
        background-color: transparent;
    } 
    @media only screen and (max-width: 1320px) {
        .rera-item {
            font-size: 15px;
        }
    }
    @media only screen and (max-width: 1199px) {
        .rera-item {
            font-size: 14px;
        }
    }
    @media only screen and (max-width: 1118px) {
        .rera-item {
            font-size: 13px;
        }
    }
    @media only screen and (max-width: 1049px) {
        .rera-item {
            padding: 8px;
        }
    }
    @media only screen and (max-width: 1007px) {
        .rera-item {
            padding: 8px;
            font-size: 12px;
        }
    }
    @media only screen and (max-width: 991px) {
        .rera-item {
            padding: 9px;
            font-size: 15px;
        }
    }
</style>

  <script src="js/jquery-3.6.0.js"></script>  
  <script src="https://www.google.com/recaptcha/api.js"></script>
  <script src="js/index.js"></script>
  <script src="js/project-page-extranal.js"></script>
</body>
</html>



 <?php
    }
}

$conn->close();
?>


