<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 - Page Not Found | Shubhankar</title>
<meta name="robots" content="noindex,follow">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root{
    --brand:#7a5c3e;
    --brand-dark:#5f462d;
    --ink:#28221c;
    --muted:#8a8177;
    --paper:#faf7f2;
  }

  *{ box-sizing:border-box; }

  body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    background:var(--paper);
    color:var(--ink);
  }

  .error-wrap{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 20px;
    position:relative;
    overflow:hidden;
  }

  /* soft background texture, not a stock image */
  .error-wrap::before,
  .error-wrap::after{
    content:"";
    position:absolute;
    border-radius:50%;
    filter:blur(2px);
    opacity:.5;
  }
  .error-wrap::before{
    width:340px; height:340px;
    background:radial-gradient(circle at 30% 30%, #eadfce, transparent 70%);
    top:-120px; left:-100px;
  }
  .error-wrap::after{
    width:260px; height:260px;
    background:radial-gradient(circle at 70% 70%, #f0e6d6, transparent 70%);
    bottom:-90px; right:-80px;
  }

  .error-card{
    position:relative;
    z-index:1;
    max-width:560px;
    width:100%;
    text-align:center;
  }

  /* hand-drawn-ish knot illustration made of two rings, feels custom not stocky */
  .mark{
    width:96px;
    height:96px;
    margin:0 auto 28px;
    position:relative;
  }
  .mark span{
    position:absolute;
    inset:0;
    border-radius:50%;
    border:3px solid var(--brand);
  }
  .mark span:first-child{
    transform:rotate(0deg) scale(1);
    opacity:.9;
  }
  .mark span:last-child{
    transform:rotate(35deg) scale(.72);
    border-color:#cbb896;
  }
  .mark i{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:45px;
    color:var(--brand);
  }

  .eyebrow{
    text-transform:uppercase;
    letter-spacing:.14em;
    font-size:12.5px;
    font-weight:700;
    color:var(--brand);
    margin-bottom:6px;
  }
  
  
  .mark{
  
    background-color: #fff3cd00;
}

  .error-code{
    font-size:96px;
    font-weight:800;
    line-height:1;
    color:var(--ink);
    letter-spacing:-2px;
  }

  .error-title{
    font-size:24px;
    font-weight:700;
    margin:14px 0 10px;
    color:var(--ink);
  }

  .error-text{
    color:var(--muted);
    font-size:16px;
    line-height:1.6;
    max-width:420px;
    margin:0 auto 30px;
  }

  .error-actions{
    display:flex;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:34px;
  }

  .btn-brand,
  .btn-outline-brand{
    font-size:15px;
    font-weight:600;
    padding:12px 26px;
    border-radius:8px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.25s ease;
  }

  .btn-brand{
    background:var(--brand);
    color:#fff;
    border:1px solid var(--brand);
  }
  .btn-brand:hover{
    background:var(--brand-dark);
    border-color:var(--brand-dark);
    transform:translateY(-1px);
  }

  .btn-outline-brand{
    border:1.5px solid #d8cdbb;
    color:var(--ink);
    background:#fff;
  }
  .btn-outline-brand:hover{
    border-color:var(--brand);
    color:var(--brand);
    transform:translateY(-1px);
  }

  .quick-links{
    border-top:1px solid #e7ded0;
    padding-top:22px;
  }

  .quick-links p{
    font-size:13px;
    color:var(--muted);
    margin-bottom:10px;
  }

  .quick-links a{
    font-size:13.5px;
    color:var(--ink);
    text-decoration:none;
    margin:0 10px;
    position:relative;
  }
  .quick-links a:hover{
    color:var(--brand);
  }
  .quick-links a:not(:last-child)::after{
    content:"";
    position:absolute;
    right:-11px;
    top:2px;
    height:12px;
    width:1px;
    background:#d8cdbb;
  }

  @media(max-width:576px){
    .error-code{ font-size:64px; }
    .error-title{ font-size:20px; }
    .error-text{ font-size:14.5px; }
    .btn-brand, .btn-outline-brand{
      width:100%;
      justify-content:center;
    }
    .error-actions{
      flex-direction:column;
    }
    .quick-links a:not(:last-child)::after{
      display:none;
    }
    .quick-links a{
      display:block;
      margin:6px 0;
    }
  }
</style>
</head>
<body>

<div class="error-wrap">
  <div class="error-card">

    <div class="mark">
      <span></span>
      <span></span>
      <i class="bi bi-emoji-frown"></i>
    </div>

    <div class="eyebrow">Error 404</div>
    <div class="error-code">Lost?</div>

    <h1 class="error-title">This page took a wrong turn</h1>
    <p class="error-text">
      The link you followed might be broken, or the page may have been moved.
      Let's get you back to somewhere useful.
    </p>

    <div class="error-actions">
      <a href="https://shubhankarfashion.com/" class="btn-brand">
        <i class="bi bi-house-door"></i> Back to Home
      </a>
      <a href="shop" class="btn-outline-brand">
        <i class="bi bi-bag"></i> Browse Shop
      </a>
    </div>

    <div class="quick-links">
      <p>You might be looking for</p>
      <a href="categories">Categories</a>
      <a href="shop">All Products</a>
      <a href="contact">Contact Us</a>
    </div>

  </div>
</div>

</body>
</html>