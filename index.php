<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Diagnostic Lab Management System | Home Page</title>
    <!-- Include necessary CSS files -->
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all">
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all">
    <link rel="stylesheet" href="css/chocolat.css" type="text/css" media="screen" charset="utf-8">
    <!-- Include necessary JavaScript files -->
    <script src="js/jquery-1.8.3.min.js"></script>
    <script src="js/modernizr.custom.97074.js"></script>
    <script src="js/move-top.js"></script>
    <script src="js/easing.js"></script>
    <script src="js/jquery.chocolat.js"></script>
    <script src="js/jquery.hoverdir.js"></script>
    <script>
        // Smooth scrolling for anchor links
        jQuery(document).ready(function($) {
            $(".scroll").click(function(event) {
                event.preventDefault();
                $('html,body').animate({scrollTop: $(this.hash).offset().top}, 1200);
            });
        });

        // Initialize Chocolat lightbox for gallery images
        $(function() {
            $('.gallery a').Chocolat();
        });

        // Initialize hover direction effect for gallery images
        $(function() {
            $('#da-thumbs > li').each(function() {
                $(this).hoverdir();
            });
        });

        // Scroll to top functionality
        $(document).ready(function() {
            $().UItoTop({ easingType: 'easeOutQuart' });
        });
    </script>
</head>
<body>
    <!-- Header Section -->
    <div class="header" id="home">
        <div class="header-top">
            <div class="container">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php">ODLMS<span>lab</span></a>
                </div>
                <!-- Navigation Menu -->
                <div class="top-menu">
                    <span class="menu"><img src="images/nav.png" alt="Menu" /></span>
                    <ul>
                        <li><a href="index.php" class="active scroll">Home</a></li>
                        <li><a href="#aboutus" class="scroll">About Us</a></li>
                        <li><a href="#gallery" class="scroll">Gallery</a></li>
                        <li><a href="admin/login.php">Admin</a></li>
                        <li><a href="user/login.php">Users</a></li>
                        <li><a href="employee/login.php">Employee</a></li>
                    </ul>
                </div>
                <!-- Mobile Menu Toggle Script -->
                <script>
                    $("span.menu").click(function() {
                        $(".top-menu ul").slideToggle("slow");
                    });
                </script>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content">
        <!-- About Us Section -->
        <div class="about-section" id="aboutus">
            <div class="container">
                <h3>Welcome to the Laboratory!</h3>
                <div class="about-grids">
                    <div class="col-md-4 about-grid">
                        <img src="images/p1.jpg" class="img-responsive" alt="About Image 1">
                        <h4>Epsum Factorial</h4>
                        <p>Epsum factorial nonp quid pro quo hic escorol. Olypian quarrels et gorcongolium onp quid sic ad nauseum. Souvlaki ignitus.</p>
                    </div>
                    <div class="col-md-4 about-grid">
                        <img src="images/p2.jpg" class="img-responsive" alt="About Image 2">
                        <h4>Olypian Quarrels</h4>
                        <p>Epsum factorial nonp quid pro quo hic escorol. Olypian quarrels et gorcongolium onp quid sic ad nauseum. Souvlaki ignitus.</p>
                    </div>
                    <div class="col-md-4 about-grid">
                        <img src="images/p3.jpg" class="img-responsive" alt="About Image 3">
                        <h4>Epsum Factorial</h4>
                        <p>Epsum factorial nonp quid pro quo hic escorol. Olypian quarrels et gorcongolium onp quid sic ad nauseum. Souvlaki ignitus.</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <!-- Gallery Section -->
        <div class="gallery" id="gallery">
            <div class="container">
                <h3>Gallery</h3>
                <div class="gallery-grids">
                    <section>
                        <ul id="da-thumbs" class="da-thumbs">
                            <!-- Gallery Images -->
                            <?php
                            $galleryImages = [
                                "g1.jpg", "g2.jpg", "g3.jpg", "g4.jpg",
                                "g5.jpg", "g6.jpg", "g7.jpg", "g8.jpg", "g9.jpg"
                            ];
                            foreach ($galleryImages as $image) {
                                echo '
                                <li>
                                    <a href="images/' . $image . '" rel="title" class="b-link-stripe b-animate-go thickbox">
                                        <img src="images/' . $image . '" alt="Gallery Image">
                                        <div>
                                            <h5>Project</h5>
                                            <span>Non suscipit leo fringilla non suscipit leo fringilla molestie</span>
                                        </div>
                                    </a>
                                </li>';
                            }
                            ?>
                            <div class="clearfix"></div>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="container">
            <div class="footer-bottom">
                <p>Online Diagnostic Lab Management System @ 2025</p>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <a href="#" id="toTop" style="display: block;">
        <span id="toTopHover" style="opacity: 1;"></span>
    </a>
</body>
</html>