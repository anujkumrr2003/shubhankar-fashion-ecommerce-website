

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
  <title>Contact Shubhankar | Get in Touch With Us</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Contact Shubhankar | Get in Touch With Us">
<meta name="description" content="Contact Shubhankar for product inquiries, order support, business partnerships or general assistance. Our team is here to help you with all your questions.">

<meta name="keywords" content="Contact Shubhankar, customer support, contact us, fashion store, help, enquiry, men's clothing support, online shopping support">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<meta name="googlebot" content="index,follow">
<meta name="bingbot" content="index,follow">

<link rel="canonical" href="https://shubhankarfashion.com/contact">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="Contact Shubhankar">
<meta property="og:description" content="Need help? Contact the Shubhankar team for product information, orders and customer support.">
<meta property="og:url" content="https://shubhankarfashion.com/contact">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Contact Shubhankar">
<meta name="twitter:description" content="Get in touch with the Shubhankar team for customer support and product enquiries.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar">
<meta name="apple-mobile-web-app-title" content="Shubhankar">
<meta name="format-detection" content="telephone=no">
<meta name="language" content="English">
<meta name="distribution" content="global">
<meta name="rating" content="general">
<meta name="revisit-after" content="7 days">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"ContactPage",
  "name":"Contact Shubhankar",
  "url":"https://shubhankarfashion.com/contact",
  "description":"Contact Shubhankar for customer support, order assistance and business enquiries.",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar",
    "url":"https://shubhankarfashion.com/"
  },
  "publisher":{
    "@type":"Organization",
    "name":"Shubhankar",
    "logo":{
      "@type":"ImageObject",
      "url":"https://shubhankarfashion.com/img/logo-removebg-preview.png"
    }
  }
}
</script>
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"BreadcrumbList",
  "itemListElement":[
    {
      "@type":"ListItem",
      "position":1,
      "name":"Home",
      "item":"https://shubhankarfashion.com/"
    },
    {
      "@type":"ListItem",
      "position":2,
      "name":"Contact",
      "item":"https://shubhankarfashion.com/contact"
    }
  ]
}
</script>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Site styles -->
  <link rel="stylesheet" href="css/style.css">

</head>

<body>

  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index.php">Home</a> / <span>Contact</span></nav>
      <h1 class="h3 mb-0">Get in Touch</h1>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card-panel">
            <h2 class="h5 mb-3">Send us a message</h2>
            <form id="contactForm" action="contact-process.php" method="POST">

              <div class="row g-3">

                <div class="col-md-6">
                  <label>Full Name</label>
                  <input type="text" class="form-control" name="full_name" required>
                </div>

                <div class="col-md-6">
                  <label>Email</label>
                  <input type="email" class="form-control" name="email" required>
                </div>

<div class="col-md-6">
    <label>Contact No <span class="text-danger">*</span></label>

    <input
        type="tel"
        class="form-control"
        id="contact"
        name="contact"
        placeholder="+1 9876543210"
        required
        minlength="8"
        maxlength="20"
        pattern="^\+[0-9]{1,4}\s?[0-9]{6,15}$"
        title="Please enter a valid contact number with country code. Example: +1 9876543210">
</div>

                <div class="col-md-6">
                  <label>Message</label>
                  <textarea class="form-control" name="message" rows="1"  ></textarea>
                </div>

                <div class="col-12">
                  <button type="submit" class="btn btn-brand">Send Message</button>
                </div>

              </div>

            </form>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card-panel mb-3">
            <h2 class="h6 mb-3">Contact details</h2>
            <p class="small mb-2"><i class="bi bi-geo-alt"></i> X-355 Lane No 8, Ram Nagar Gandhi Nagar, New Delhi, India</p>
            <p class="small mb-2"><i class="bi bi-telephone"></i> +91 98765 43210</p>
            <p class="small mb-0"><i class="bi bi-envelope"></i> shubhankarfashion.com</p>
          </div>
          <div class="card-panel">
            <h2 class="h6 mb-3">Business Hours</h2>
            <p class="small mb-1">Mon - Sat: 9:00 AM - 7:00 PM IST</p>
            <p class="small mb-0">Sunday: Closed</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>

 

  <script>
    document.addEventListener("DOMContentLoaded", function() {

      function updateContactCode() {

        if (typeof NEH === "undefined") return;

        const loc = NEH.getLocale();

        const contact = document.getElementById("contact");

        if (!contact) return;

        const country = COUNTRIES.find(c => c.code === loc.code);

        if (country) {
          contact.value = country.dial + " ";
        } else {
          contact.value = "";
        }
      }

      updateContactCode();

      document.addEventListener("neh:localechange", function() {
        updateContactCode();
      });

    });
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {

      if (typeof NEH !== "undefined") {
        NEH.updatePriceEls();
      }

    });
  </script>


</body>

</html>