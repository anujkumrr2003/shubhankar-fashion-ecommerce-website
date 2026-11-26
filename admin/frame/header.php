<!-- ========== Topbar Start ========== -->
<div class="navbar-custom">
  <div class="topbar container-fluid">
    <div class="d-flex align-items-center gap-1">

      <!-- Topbar Brand Logo -->
      <div class="logo-topbar">
        <!-- Logo light -->
        <a href="index.php" class="logo-light"> <span class="logo-lg"> <img src="ohm.png" alt="logo"> </span> <span class="logo-sm"> <img src="ohm.png" alt="small logo"> </span> </a>

        <!-- Logo Dark -->
        <a href="index.php" class="logo-dark"> <span class="logo-lg"> <img src="ohm.png" alt="dark logo"> </span> <span class="logo-sm"> <img src="ohm.png" alt="small logo"> </span> </a>
      </div>

      <!-- Sidebar Menu Toggle Button -->
      <button class="button-toggle-menu"> <i class="ri-menu-line"></i> </button>

      <!-- Horizontal Menu Toggle Button -->
      <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
        <div class="lines"> <span></span> <span></span> <span></span> </div>
      </button>

      <!-- Topbar Search Form -->

    </div>
    <ul class="topbar-menu d-flex align-items-center gap-3">
      <li class="dropdown">
        <!-- <a class="nav-link dropdown-toggle arrow-none nav-user" data-bs-toggle="dropdown" href="#" role="button"
                                aria-haspopup="false" aria-expanded="false"> <span class="account-user-avatar"> <img src="assets/images/users/avatar-1.jpg" alt="user-image" width="32" class="rounded-circle"> </span> <span class="d-lg-block d-none">
        <h5 class="my-0 fw-normal">Thomson <i
                                            class="ri-arrow-down-s-line d-none d-sm-inline-block align-middle"></i></h5>
        </span> </a> -->
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
          <!-- item-->
          <div class=" dropdown-header noti-title">
            <h6 class="text-overflow m-0">Welcome !</h6>
          </div>

          <!-- item-->
          <a href="#" class="dropdown-item"> <i class="ri-logout-box-line fs-18 align-middle me-1"></i> <span>Logout</span> </a>
        </div>
      </li>
    </ul>
  </div>
</div>
<!-- ========== Topbar End ========== -->

<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu" style=" background: linear-gradient(120deg, #fff7f0, #e9f1ff);">

  <!-- Brand Logo Light -->
  <a href="index.php" class="logo logo-light"> <span class="logo-lg"> <img src="ohm.png" alt="logo" style="width: 80px; height:60px;"> </span> <span class="logo-sm"> <img src="ohm.png" alt="small logo" width="100" height="100"> </span> </a>

  <!-- Brand Logo Dark -->
  <a href="index.php" class="logo logo-dark"> <span class="logo-lg"> <img src="" alt="dark logo" width="100"> </span> <span class="logo-sm"> <img src="ohm.png" alt="small logo" width="100"> </span> </a>

  <!-- Sidebar -left -->
  <div class="h-100" id="leftside-menu-container" data-simplebar>
    <!--- Sidemenu -->
    <ul class="side-nav">
      <!-- <li class="side-nav-item"> <a class="side-nav-link" href="
    website-logo.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Website Logo</span> </a> </li> -->
      <!-- <li class="side-nav-title">Main</li> -->

      <script>
        document.addEventListener("DOMContentLoaded", function() {
          const sideNavLinks = document.querySelectorAll(".side-nav-item > .side-nav-link");

          sideNavLinks.forEach(link => {
            link.addEventListener("click", function(e) {
              const sublist = this.nextElementSibling;

              // Check if the clicked link has a sublist
              if (sublist && sublist.classList.contains("side-nav-sublist")) {
                e.preventDefault(); // Prevent navigation only if there's a submenu
                const isVisible = sublist.style.display === "block";
                sublist.style.display = isVisible ? "none" : "block";
              }
            });
          });
        });
      </script>





     


<style> 
.ri-layout-line{
  color: #ff7a00;
}
  
</style>

<li class="side-nav-item"> <a class="side-nav-link" href="add-home-banner.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Desktop Banner  </span> </a> </li>

<li class="side-nav-item"> <a class="side-nav-link" href="add-home-banner-mobile.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Mobile Banner  </span> </a> </li>

<li class="side-nav-item"> <a class="side-nav-link" href="category.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Cotegory  </span> </a> </li>

     
     
    

       <li class="side-nav-item"> <a class="side-nav-link" href="
    add-size-color.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Size & Color </span> </a> </li>
 

     
     <li class="side-nav-item"> <a class="side-nav-link" href="
     add-products.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Products </span> </a> </li>
     <li class="side-nav-item"> <a class="side-nav-link" href="
     products.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>All Products </span> </a> </li>

 <li class="side-nav-item"> <a class="side-nav-link" href="
     b2b-enquiry.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>B2B Orders </span> </a> </li>

<!--Enquiry-->
      <li class="side-nav-item"> <a class="side-nav-link" href="
     promo_form.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Add Promo code </span> </a> </li>

      <li class="side-nav-item"> <a class="side-nav-link" href="
    customers.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Our Customers </span> </a> </li>
 <li class="side-nav-item"> <a class="side-nav-link" href="
         admin_orders.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Show Orders </span> </a> </li>
     
 <!-- <li class="side-nav-item"> <a class="side-nav-link" href="
     page-form.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Policies </span> </a> </li> -->
      

           
 <!-- <li class="side-nav-item"> <a class="side-nav-link" href="
     logo.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Logo </span> </a> </li> -->
      
 <!-- <li class="side-nav-item"> <a class="side-nav-link" href="
     home-banner.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Home-Banner </span> </a> </li> -->
      
          
 <!-- <li class="side-nav-item"> <a class="side-nav-link" href="
     blog.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Blog </span> </a> </li> -->
      
<!-- <li class="side-nav-item"> <a class="side-nav-link" href="
     homebanner.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Home image </span> </a> </li> -->
      




<!-- <li class="side-nav-item"> <a class="side-nav-link" href="add-social.php
" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span> Add Social Midea </span> </a> </li> -->
      



     

      

      



      <li class="side-nav-item"> <a class="side-nav-link" href="change_password.php" style="text-decoration: none;"> <i class="ri-layout-line"></i> <span>Change Password</span> </a> </li>
      <li class="side-nav-item"> <a style="text-decoration: none;" class="side-nav-link" href="logout.php"> <i class="ri-layout-line"></i> <span> Logout </span> </a> </li>
    </ul>
    <!--- End Sidemenu -->

    <div class="clearfix"></div>
  </div>
</div>
<!-- ========== Left Sidebar End ========== -->